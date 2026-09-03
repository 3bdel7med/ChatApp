<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchUsers(Request $request)
    {
        $query = $request->get('query', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
