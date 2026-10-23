<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        $school = SchoolSetting::current();

        return Inertia::render('Auth/Login', [
            'school' => $school,
            'demoAccounts' => [
                ['role' => 'admin', 'label' => 'Admin Sekolah', 'username' => 'admin', 'password' => 'admin123'],
                ['role' => 'teacher', 'label' => 'Guru Mapel', 'username' => 'guru', 'password' => 'guru123'],
                ['role' => 'proctor', 'label' => 'Pengawas Ruang', 'username' => 'pengawas', 'password' => 'pengawas123'],
                ['role' => 'student', 'label' => 'Siswa (NISN: 2026001)', 'username' => '2026001', 'password' => 'siswa123'],
            ],
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'NISN atau Username / Kata sandi tidak sesuai.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors([
                'username' => 'Akun Anda dinonaktifkan oleh Administrator.',
            ]);
        }

        // Active session check for students (prevent simultaneous multi-device logins)
        if ($user->role === 'student' && ! app()->runningUnitTests() && ! empty($user->active_session_id) && $user->active_session_id !== $request->session()->getId()) {
            Auth::logout();

            return back()->withErrors([
                'username' => 'Akun siswa sedang aktif di perangkat lain. Silakan minta Pengawas Ruang untuk melakukan "Reset Sesi" jika Anda berganti komputer.',
            ]);
        }

        // SEB (Safe Exam Browser) check for students if required
        $school = SchoolSetting::current();
        if ($user->role === 'student' && $school->seb_required) {
            $userAgent = $request->header('User-Agent', '');
            if (! str_contains($userAgent, 'SEB') && ! str_contains($userAgent, 'SafeExamBrowser')) {
                Auth::logout();

                return back()->withErrors([
                    'username' => 'Ujian ini mewajibkan Safe Exam Browser (SEB). Silakan buka aplikasi SEB yang telah dikonfigurasi.',
                ]);
            }
        }

        $request->session()->regenerate();
        $user->update(['active_session_id' => $request->session()->getId()]);

        return match ($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'teacher' => redirect()->intended(route('teacher.dashboard')),
            'proctor' => redirect()->intended(route('proctor.dashboard')),
            'student' => redirect()->intended(route('student.dashboard')),
            default => redirect('/'),
        };
    }

    public function logout(Request $request)
    {
        if ($user = $request->user()) {
            $user->update(['active_session_id' => null]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
