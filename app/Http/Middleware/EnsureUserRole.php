<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Single device active session validation for students during CBT exams
        if ($user->role === 'student' && ! app()->runningUnitTests()) {
            if ($user->active_session_id && $user->active_session_id !== $request->session()->getId()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'username' => 'Sesi login Anda telah berakhir atau digantikan oleh perangkat lain. Hubungi pengawas ruang jika memerlukan reset login.',
                ]);
            }
        }

        if (! in_array($user->role, $roles)) {
            // Redirect based on actual role
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'teacher' => redirect()->route('teacher.dashboard'),
                'proctor' => redirect()->route('proctor.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                default => abort(403, 'Akses tidak diizinkan untuk peran Anda.'),
            };
        }

        return $next($request);
    }
}
