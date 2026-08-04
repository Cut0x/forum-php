<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('username', 'like', $query.'%')
            ->orWhere('name', 'like', $query.'%')
            ->limit(8)
            ->get(['name', 'username', 'avatar']);

        return response()->json($users->map(fn (User $user) => [
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : asset('images/default-avatar.jpg'),
        ]));
    }
}
