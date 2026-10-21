<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class MasterAcademicController extends Controller
{
    // ==================== KELAS / ROMBEL ====================
    public function classRooms(): Response
    {
        $classes = ClassRoom::with(['homeroomTeacher', 'students'])->orderBy('grade_level')->orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/ClassRooms', [
            'classes' => $classes,
            'teachers' => $teachers,
        ]);
    }

    public function storeClassRoom(Request $request)
    {
        $validated = $request->validate([
            'grade_level' => ['required', 'integer', 'min:1', 'max:13'],
            'major_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:50'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        ClassRoom::create($validated);

        return back()->with('success', 'Rombongan belajar berhasil ditambahkan.');
    }

    public function updateClassRoom(Request $request, ClassRoom $classRoom)
    {
        $validated = $request->validate([
            'grade_level' => ['required', 'integer', 'min:1', 'max:13'],
            'major_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:50'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        $classRoom->update($validated);

        return back()->with('success', 'Rombongan belajar berhasil diperbarui.');
    }

    public function destroyClassRoom(ClassRoom $classRoom)
    {
        $classRoom->delete();

        return back()->with('success', 'Rombongan belajar berhasil dihapus.');
    }

    // ==================== MATA PELAJARAN ====================
    public function subjects(): Response
    {
        $subjects = Subject::withCount(['questionBanks', 'examSchedules'])->orderBy('name')->get();

        return Inertia::render('Admin/Subjects', [
            'subjects' => $subjects,
        ]);
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['nullable', 'integer', 'min:1', 'max:13'],
            'kkm_score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Subject::create($validated);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code,'.$subject->id],
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['nullable', 'integer', 'min:1', 'max:13'],
            'kkm_score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $subject->update($validated);

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroySubject(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    // ==================== GURU & PENGAWAS ====================
    public function staff(): Response
    {
        $users = User::whereIn('role', ['teacher', 'proctor', 'admin'])
            ->withCount(['questionBanks', 'proctoredSessions'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Staff', [
            'staff' => $users,
        ]);
    }

    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'in:teacher,proctor,admin'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Akun Guru / Tenaga Pendidik berhasil dibuat.');
    }

    public function updateStaff(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:4'],
            'role' => ['required', 'in:teacher,proctor,admin'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroyStaff(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}
