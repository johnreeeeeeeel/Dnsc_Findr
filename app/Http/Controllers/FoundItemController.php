<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class FoundItemController extends Controller
{
    // CREATE POST
    public function createPost(Request $request)
    {
        $validated = $request->validate(
            [
                'item_name' => 'required|string|max:100',
                'item_category' => 'required|string|max:50',
                'description' => 'required|string',
                'photo' => 'required|image|mimes:jpg,jpeg,png,gif|max:5048',
                'location_found' => 'required|string',

                // Prevent future date
                'date_found' => 'required|date|before_or_equal:today',

                // Prevent future time
                'time_found' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->date_found == date('Y-m-d') && $value > date('H:i')) {
                            $fail('The time found cannot be in the future.');
                        }
                    },
                ],
            ],
            [
                'date_found.before_or_equal' => 'The date found cannot be in the future.',
            ],
        );

        // Generate unique ID
        do {
            $randomId = strtoupper(Str::random(8));
        } while (FoundItem::where('item_id', $randomId)->exists());

        // Photo upload
        $photo = $request->file('photo');
        $filename = time() . '_' . $photo->getClientOriginalName();
        $path = $photo->storeAs('found-items', $filename, 'public');

        // Create new found item
        $approvalStatus = Auth::user()->usertype === 'Admin' ? 'approved' : 'pending';

        $item = FoundItem::create([
            'item_id' => $randomId,
            'item_name' => $validated['item_name'],
            'item_category' => $validated['item_category'],
            'description' => $validated['description'] ?? '',
            'photo_url' => URL::to('/storage/' . $path),
            'location_found' => $validated['location_found'],
            'date_found' => $validated['date_found'],
            'time_found' => $validated['time_found'],
            'posted_by' => Auth::user()->user_id,
            'status' => 'Not Yet Claimed',
            'post_status' => $approvalStatus,
        ]);

        return response()->json(
            [
                'success' => true,
                'message' => 'Found item posted successfully!',
                'item_id' => $item->item_id,
            ],
            201,
        );
    }

    // EDIT POST
    public function editPost(Request $request, $item_id)
    {
        $item = FoundItem::where('item_id', $item_id)->firstOrFail();

        $validated = $request->validate(
            [
                'item_name' => 'required|string|max:100',
                'item_category' => 'required|string|max:50',
                'description' => 'required|string',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5048',
                'location_found' => 'required|string',

                // Prevent future date
                'date_found' => 'required|date|before_or_equal:today',

                // Prevent future time
                'time_found' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->date_found == date('Y-m-d') && $value > date('H:i')) {
                            $fail('The time found cannot be in the future.');
                        }
                    },
                ],

                'status' => 'sometimes|in:Not Yet Claimed,Claimed',
            ],
            [
                // Custom messages
                'date_found.before_or_equal' => 'The date found cannot be in the future.',
            ],
        );

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($item->photo_url) {
                $oldPath = str_replace(URL::to('/storage/') . '/', '', $item->photo_url);
                $oldPath = ltrim($oldPath, '/');
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Upload new photo
            $photo = $request->file('photo');
            $filename = time() . '_' . $photo->getClientOriginalName();
            $path = $photo->storeAs('found-items', $filename, 'public');

            $validated['photo_url'] = URL::to('/storage/' . $path);
        }

        $item->update($validated);

        return response()->json(
            [
                'success' => true,
                'message' => 'Item updated successfully!',
                'item_id' => $item->item_id,
                'item' => $item->fresh('postedBy'),
            ],
            200,
        );
    }

    public function displayAllPostStatus()
    {
        $items = FoundItem::with('postedBy:user_id,username,firstname,middlename,lastname,usertype')->orderByDesc('created_at')->get();

        return response()->json(
            $items->map(
                fn($item) => [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_category' => $item->item_category,
                    'description' => $item->description,
                    'photo_url' => $item->photo_url,
                    'location_found' => $item->location_found,
                    'date_found' => $item->date_found,
                    'time_found' => $item->time_found,
                    'status' => $item->status,
                    'post_status' => $item->post_status,
                    'created_at' => $item->created_at->toDateTimeString(),
                    'postedBy' => $item->postedBy ? $item->postedBy->only(['username', 'firstname', 'middlename', 'lastname', 'usertype']) : null,
                ],
            ),
        );
    }

    // APPROVE POST
    public function approvePost($item_id)
    {
        $item = FoundItem::where('item_id', $item_id)->firstOrFail();
        $item->update(['post_status' => 'approved']);
        return response()->json(['success' => true]);
    }

    public function rejectPost($item_id)
    {
        $item = FoundItem::where('item_id', $item_id)->firstOrFail();
        $item->update(['post_status' => 'rejected']);
        return response()->json(['success' => true]);
    }

    // DISPLAY USER POST
    public function displayUserPosts()
    {
        $userId = Auth::id();

        $items = FoundItem::with([
            'postedBy' => fn($q) => $q->select('user_id', 'username', 'firstname', 'middlename', 'lastname', 'usertype'),
        ])
            ->where('posted_by', $userId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(
            $items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_category' => $item->item_category,
                    'description' => $item->description ?? '',
                    'photo_url' => $item->photo_url,
                    'location_found' => $item->location_found,
                    'date_found' => $item->date_found,
                    'time_found' => $item->time_found,
                    'status' => $item->status, // Claim status
                    'post_status' => $item->post_status, // This is what matters!
                    'created_at' => $item->created_at->toDateTimeString(),
                    'updated_at' => $item->updated_at?->toDateTimeString(),
                    'postedBy' => $item->postedBy
                        ? [
                            'usertype' => $item->postedBy->usertype,
                            'username' => $item->postedBy->username,
                            'firstname' => $item->postedBy->firstname,
                            'middlename' => $item->postedBy->middlename,
                            'lastname' => $item->postedBy->lastname,
                        ]
                        : null,
                ];
            }),
        );
    }

    // DISPLAY ALL POST
    public function displayApprovedPosts()
    {
        $items = FoundItem::with([
            'postedBy' => fn($q) => $q->select('user_id', 'username', 'firstname', 'middlename', 'lastname', 'usertype'),
        ])
            ->where('post_status', 'approved')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(
            $items->map(
                fn($i) => [
                    'item_id' => $i->item_id,
                    'item_name' => $i->item_name,
                    'item_category' => $i->item_category,
                    'description' => $i->description,
                    'photo_url' => $i->photo_url,
                    'location_found' => $i->location_found,
                    'date_found' => $i->date_found,
                    'time_found' => $i->time_found,
                    'status' => $i->status,
                    'created_at' => $i->created_at,
                    'postedBy' => $i->postedBy
                        ? [
                            'user_id' => $i->postedBy->user_id, // ADD THIS LINE
                            'usertype' => $i->postedBy->usertype,
                            'username' => $i->postedBy->username,
                            'firstname' => $i->postedBy->firstname,
                            'middlename' => $i->postedBy->middlename,
                            'lastname' => $i->postedBy->lastname,
                        ]
                        : null,
                ],
            ),
        );
    }

    // ARCHIVE POST
    public function archivePost($item_id)
    {
        $item = FoundItem::where('item_id', $item_id)->firstOrFail();

        $item->update(['post_status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Post archived successfully',
        ]);
    }

    // UNARCHIVE POST
    public function unarchivePost($item_id)
    {
        $item = FoundItem::where('item_id', $item_id)->firstOrFail();
        $item->update(['post_status' => 'pending']);

        return response()->json(['success' => true, 'message' => 'Post unarchived']);
    }

    // PERMANENTLY DELETE POST
    public function deletePost($item_id)
    {
        $item = FoundItem::where('item_id', $item_id)->firstOrFail();

        // Delete photo from storage
        if ($item->photo_url) {
            $path = str_replace(URL::to('/storage/'), '', $item->photo_url);
            $path = ltrim($path, '/');
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Delete record
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted permanently']);
    }
}
