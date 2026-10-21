<?php

namespace App\Http\Middleware;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'class_room_id' => $user->class_room_id,
                    'class_room' => $user->classRoom ? [
                        'id' => $user->classRoom->id,
                        'name' => $user->classRoom->name,
                        'grade_level' => $user->classRoom->grade_level,
                    ] : null,
                    'exam_room_name' => $user->exam_room_name,
                ] : null,
            ],
            'school' => SchoolSetting::current(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
