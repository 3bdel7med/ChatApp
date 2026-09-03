<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query', '');

        $users = User::where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function listForGroup()
    {
        $users = User::where('id', '!=', auth()->id())
            ->limit(50)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
