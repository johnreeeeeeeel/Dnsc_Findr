<?php

namespace App\Http\Controllers;

use App\Models\OsdsAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OsdsAppointmentController extends Controller
{
    public function index()
    {
        $appointments = OsdsAppointment::with('user')->where('user_id', Auth::id())->latest('sent_at')->get();

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_date' => 'required|date|after_or_equal:today',
            'schedule_time' => 'required|date_format:H:i',
            'purpose' => 'required|in:Report Item,Claim Item',
        ]);

        $appointment = OsdsAppointment::create([
            'user_id' => Auth::id(),
            'schedule_date' => $validated['schedule_date'],
            'schedule_time' => $validated['schedule_time'],
            'purpose' => $validated['purpose'],
        ]);

        return response()->json(
            [
                'message' => 'Appointment scheduled successfully!',
                'appointment' => $appointment,
            ],
            201,
        );
    }

    public function adminIndex()
    {
        $appointments = OsdsAppointment::with('user')->latest('sent_at')->get();

        return response()->json($appointments);
    }

    public function approve(Request $request, $ref)
    {
        // Allow optional custom message, fallback to default
        $request->validate([
            'response_message' => 'nullable|string|max:500',
        ]);

        $appt = OsdsAppointment::where('appointment_reference_number', $ref)->firstOrFail();

        $message = $request->response_message && trim($request->response_message) !== '' ? $request->response_message : 'Your appointment has been approved by OSDS.';

        $appt->update([
            'status' => 'Approved',
            'response_message' => $message,
        ]);

        return response()->json(['message' => 'Appointment approved']);
    }

    public function reject(Request $request, $ref)
    {
        $request->validate(['response_message' => 'required|string|max:500']);

        $appt = OsdsAppointment::where('appointment_reference_number', $ref)->firstOrFail();
        $appt->update([
            'status' => 'Rejected',
            'response_message' => $request->response_message,
        ]);

        return response()->json(['message' => 'Appointment rejected']);
    }

    public function reschedule(Request $request, $ref)
    {
        $request->validate([
            'reschedule_date' => 'required|date|after_or_equal:today',
            'reschedule_time' => 'required',
            'response_message' => 'required|string|max:500',
        ]);

        $appt = OsdsAppointment::where('appointment_reference_number', $ref)->firstOrFail();
        $appt->update([
            'status' => 'Rescheduled',
            'reschedule_date' => $request->reschedule_date,
            'reschedule_time' => $request->reschedule_time,
            'response_message' => $request->response_message,
        ]);

        return response()->json(['message' => 'Appointment rescheduled']);
    }

    public function destroy($ref)
    {
        $appt = OsdsAppointment::where('appointment_reference_number', $ref)->firstOrFail();

        // Optional: Only allow delete if not Pending (extra safety)
        if ($appt->status === 'Pending') {
            return response()->json(['message' => 'Cannot delete pending appointment'], 403);
        }

        $appt->delete();

        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}
