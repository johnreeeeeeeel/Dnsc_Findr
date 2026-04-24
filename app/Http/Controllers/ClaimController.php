<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Claim;
use App\Models\ClaimDetails;
use App\Models\Notifications;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function submitClaim(Request $request, $item_id)
    {
        $request->validate(
            [
                'description' => 'required|string',
                'location_lost' => 'required|string',

                // Prevent future date
                'date_lost' => 'required|date|before_or_equal:today',

                // Prevent future time
                'time_lost' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->date_lost == date('Y-m-d') && $value > date('H:i')) {
                            $fail('The time lost cannot be in the future.');
                        }
                    },
                ],
            ],
            [
                // Validation Custom Message
                'date_lost.before_or_equal' => 'The date lost cannot be in the future.'
            ],
        );

        // Find the item first
        $item = \App\Models\FoundItem::findOrFail($item_id); // Adjust model name if needed

        // Prevent user from claiming their own posted item
        if ($item->user_id == Auth::id()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You cannot claim an item you posted yourself.',
                ],
                403,
            );
        }

        // Prevent duplicate claims (user already claimed this item)
        $existingClaim = \App\Models\Claim::where('item_id', $item_id)->where('user_id', Auth::id())->exists();

        if ($existingClaim) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You have already submitted a claim for this item.',
                ],
                403,
            );
        }

        // Only allow claiming if status is "Not Yet Claimed"
        if ($item->status !== 'Not Yet Claimed') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'This item is no longer available for claiming.',
                ],
                403,
            );
        }

        // Generate Reference
        $claimDetailsRef = strtoupper(Str::uuid());
        $claimRef = strtoupper(Str::uuid());

        DB::transaction(function () use ($request, $item_id, $claimDetailsRef, $claimRef) {
            ClaimDetails::create([
                'claim_details_reference_number' => $claimDetailsRef,
                'description' => $request->description,
                'location_lost' => $request->location_lost,
                'date_lost' => $request->date_lost,
                'time_lost' => $request->time_lost,
            ]);

            $claim = Claim::create([
                'claim_reference_number' => $claimRef,
                'item_id' => $item_id,
                'user_id' => Auth::id(),
                'claim_details_reference_number' => $claimDetailsRef,
                'claim_status' => 'pending',
                'request_date' => now(),
            ]);

            $claim->load(['user', 'item']);

            $this->claimSubmitted($claim, $claimRef, $claimDetailsRef, $request);
            $this->newClaimReceived($claim, $request, $claimRef, $claimDetailsRef);
        });

        return response()->json(
            [
                'success' => true,
                'message' => 'Claim submitted successfully!',
                'claim_reference_number' => $claimRef,
            ],
            201,
        );
    }

    // USER NOTIFICATION
    private function claimSubmitted($claim, $claimRef, $claimDetailsRef, $request)
    {
        $message = "Dear Mr./Ms. {$claim->user->lastname},\n\n" . "Thank you for submitting your claim!\n\n" . "Claim Reference: {$claimRef}\n" . "Item: {$claim->item->item_name} (#{$claim->item->item_id})\n" . "Claim Status: {$claim->claim_status}\n" . "Request Date: {$claim->request_date}\n\n" . "Description: {$request->description}\n" . "Location Lost: {$request->location_lost}\n" . "Date Lost: {$request->date_lost}\n" . "Time Lost: {$request->time_lost}\n\n" . "Your claim is now under review by OSDS.\n" . "You will be notified once a decision is made.\n\n" . "Thank you for using DNSC Findr.\n";

        $this->notifyUsers($claim->user, $message, "Claim Submitted – {$claim->item->item_name}");
    }

    // ADMIN NOTIFICATION
    private function newClaimReceived($claim, $request, $claimRef, $claimDetailsRef)
    {
        $claimerFullName = trim("{$claim->user->firstname} {$claim->user->middlename} {$claim->user->lastname}");

        $message = "New claim received!\n\n" . "Claim Reference: {$claimRef}\n" . "Item: {$claim->item->item_name} (#{$claim->item->item_id})\n" . "Claimed by: {$claimerFullName} (#{$claim->user->user_id})\n" . "Claim Status: {$claim->claim_status}\n" . "Request Date: {$claim->request_date}\n\n" . "Claim Details Reference: {$claimDetailsRef}\n" . "Description: {$request->description}\n" . "Location Lost: {$request->location_lost}\n" . "Date Lost: {$request->date_lost}\n" . "Time Lost: {$request->time_lost}\n\n" . 'Please review this claim in the admin panel.';

        $this->notifyAdmins($message, "New Claim Received – {$claim->item->item_name}");
    }

    public function getClaimsForItem($item_id)
    {
        return Claim::with(['user', 'item', 'details'])
            ->where('item_id', $item_id)
            ->orderBy('request_date', 'desc')
            ->get();
    }

    public function getCurrentUserClaimedItemIds()
    {
        $claimedItemIds = Claim::where('user_id', Auth::id())->pluck('item_id')->toArray();

        return response()->json($claimedItemIds);
    }

    public function acceptClaim($claimId)
    {
        $claim = Claim::with(['user', 'item'])->findOrFail($claimId);

        DB::transaction(function () use ($claim) {
            $claim->claim_status = 'accepted';
            $claim->save();

            $claim->item->status = 'Claimed';
            $claim->item->save();

            $this->notifyUsers($claim->user, "Congratulations, {$claim->user->lastname}!\n\n" . "Your claim for \"{$claim->item->item_name}\" has been APPROVED.\n\n" . "Please visit the OSDS office to retrieve your item.\n" . "Bring your valid school ID.\n\n" . '— DNSC Findr Team', "Claim Approved – {$claim->item->item_name}");
        });

        return response()->json(['message' => 'Claim accepted']);
    }

    public function rejectClaim($claimId)
    {
        $claim = Claim::with(['user', 'item'])->findOrFail($claimId);

        DB::transaction(function () use ($claim) {
            $claim->claim_status = 'rejected';
            $claim->save();

            $this->notifyUsers($claim->user, "Dear {$claim->user->lastname},\n\n" . "Your claim for \"{$claim->item->item_name}\" has been rejected.\n\n" . "The details did not match the found item.\n\n" . "Visit OSDS office if you have proof.\n\n" . '— DNSC Findr Team', "Claim Rejected – {$claim->item->item_name}");
        });

        return response()->json(['message' => 'Claim rejected']);
    }

    // NOTIFICATION HELPERS

    private function notifyUsers($user, $message, $title)
    {
        Notifications::create([
            'user_id' => $user->user_id,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    private function notifyAdmins($message, $title)
    {
        foreach (User::where('usertype', 'admin')->get() as $admin) {
            Notifications::create([
                'user_id' => $admin->user_id,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }
}
