<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAvatarController extends Controller
{
    public function store()
    {
        request()->validate([
            'avatar' => 'required|image'
        ]);

        Auth::user()->update([
            'avatar_path' => request()->file('avatar')->store('avatars', 'public')
        ]);

        // 204 The server has successfully fulfilled the request and that
        // there is no additional content to send in the response payload body.

        return response([], 204);

    }
}
