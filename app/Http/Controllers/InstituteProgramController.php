<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use App\Models\Program;
use Illuminate\Http\Request;

class InstituteProgramController extends Controller
{
    /**
     * Generate ID based on first 2 letters of code + 4 random digits
     */
    private function generateCodeId($prefix)
    {
        $prefix = strtoupper(substr($prefix, 0, 2)); // first 2 letters

        do {
            $random = rand(1000, 9999); // 4 digits
            $id = $prefix . $random;
            $exists = Institute::where('institute_id', $id)->exists() || Program::where('program_id', $id)->exists();
        } while ($exists);

        return $id;
    }

    /**
     * View for managing institutes & programs
     */
    public function index()
    {
        return view('Admin.manage-institute-program');
    }

    public function extractAI(Request $request)
{
    $text = trim($request->input('text'));
    if (empty($text)) {
        return response()->json(['message' => 'Please paste some text'], 422);
    }

    $lines = array_filter(array_map('trim', explode("\n", $text)));
    $result = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '//')) continue;

        // If user wrote (CODE) at the end → use it exactly
        $userCode = null;
        $cleanName = $line;
        if (preg_match('/\(([^)]+)\)$/', $line, $m)) {
            $userCode = strtoupper(preg_replace('/[^A-Z0-9\s]/', '', $m[1])); // BSEd Math → BSEDMATH
            $userCode = preg_replace('/\s+/', ' ', trim($userCode)); // keep spaces if any
            $cleanName = trim(preg_replace('/\s*\([^)]+\)$/', '', $line));
        }

        $generatedCode = $this->acronym($cleanName); // ← SAME FUNCTION

        $code = $userCode ?: $generatedCode;

        // Is this a program? → starts with common degree word
        $isProgram = preg_match('/^(Bachelor|Master|Doctor|PhD|BS|MS|MA|BA|B\.|M\.)/i', $cleanName);

        if ($isProgram && !empty($result)) {
            $result[count($result)-1]['programs'][] = [
                'name' => $line,
                'code' => $code,
            ];
        } else {
            $result[] = [
                'institute' => [
                    'name' => $cleanName,
                    'code' => $code,
                ],
                'programs' => []
            ];
        }
    }

    return response()->json($result);
}

    private function acronym($text)
{
    $words = preg_split('/\s+/', trim($text));
    $code = '';

    foreach ($words as $word) {
        $clean = preg_replace('/[^A-Za-z]/', '', $word); // remove ., -, etc.
        if (strlen($clean) === 0) continue;

        // Skip very small common words
        if (in_array(strtolower($clean), ['of', 'and', 'the', 'in', 'for', 'with', 'a', 'an'])) {
            continue;
        }

        $code .= strtoupper($clean[0]);

        // Max 6 letters
        if (strlen($code) >= 6) break;
    }

    return $code ?: 'XXX';
}

    public function importAI(Request $request)
    {
        $entries = $request->input('data'); // array of {institute, programs[]}

        if (!is_array($entries) || empty($entries)) {
            return response()->json(['message' => 'No data to import'], 400);
        }

        foreach ($entries as $entry) {
            $instData = $entry['institute'];
            $programs = $entry['programs'] ?? [];

            // Create institute if not exists
            $institute = Institute::firstOrCreate(
                ['institute_code' => $instData['code']], // find by code
                [
                    'institute_id' => $this->generateCodeId($instData['code']),
                    'institute_code' => $instData['code'],
                    'institute_description' => $instData['name'],
                ],
            );

            // Create each program
            foreach ($programs as $prog) {
                Program::firstOrCreate(
                    ['program_code' => $prog['code']], // unique check
                    [
                        'program_id' => $this->generateCodeId($prog['code']),
                        'program_code' => $prog['code'],
                        'program_description' => $prog['name'],
                        'institute_id' => $institute->institute_id,
                    ],
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully imported ' . count($entries) . ' institute(s) with programs',
        ]);
    }

    /** --- Institute CRUD --- */

    public function storeInstitute(Request $request)
    {
        $request->validate([
            'institute_code' => 'required|string|max:12|unique:institutes,institute_code',
            'institute_description' => 'required|string|max:255',
        ]);

        $code = strtoupper($request->institute_code);

        Institute::create([
            'institute_id' => $this->generateCodeId($code),
            'institute_code' => $code,
            'institute_description' => $request->institute_description,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateInstitute(Request $request, $id)
    {
        $request->validate([
            'institute_code' => 'required|string|max:12',
            'institute_description' => 'required|string|max:255',
        ]);

        $inst = Institute::findOrFail($id);
        $inst->update([
            'institute_code' => strtoupper($request->institute_code),
            'institute_description' => $request->institute_description,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteInstitute($id)
    {
        $inst = Institute::findOrFail($id);

        if ($inst->programs()->count() > 0) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Cannot delete institute because it has programs.',
                ],
                400,
            );
        }

        $inst->delete();

        return response()->json(['success' => true]);
    }

    /** --- Program CRUD --- */

    public function storeProgram(Request $request)
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,institute_id',
            'program_code' => 'required|string|max:12|unique:programs,program_code',
            'program_description' => 'required|string|max:255',
        ]);

        $code = strtoupper($request->program_code);

        Program::create([
            'program_id' => $this->generateCodeId($code),
            'program_code' => $code,
            'program_description' => $request->program_description,
            'institute_id' => $request->institute_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateProgram(Request $request, $id)
    {
        $request->validate([
            'program_code' => 'required|string|max:12',
            'program_description' => 'required|string|max:255',
        ]);

        $prog = Program::findOrFail($id);
        $prog->update([
            'program_code' => strtoupper($request->program_code),
            'program_description' => $request->program_description,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteProgram($id)
    {
        Program::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /** --- Get Institutes & Programs --- */

    public function getInstitutes()
    {
        return Institute::select('institute_id', 'institute_code', 'institute_description')->orderBy('institute_code')->get();
    }

    public function getPrograms($code)
    {
        return Program::whereHas('institute', function ($q) use ($code) {
            $q->where('institute_code', $code);
        })
            ->select('program_id', 'program_code', 'program_description', 'institute_id') // add institute_id
            ->get();
    }
}
