<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            'app' => [
                'name' => config('app.name'),
            ],
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email', 'role'),
            ],
        ];
    }
}
