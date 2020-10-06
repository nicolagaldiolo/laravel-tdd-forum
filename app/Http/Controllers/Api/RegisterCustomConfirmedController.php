<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class RegisterCustomConfirmedController extends Controller
{
    public function index()
    {
        if(!$user = User::where('confirmation_token', request()->only('token'))->first()){
            return redirect('/threads')
                ->with('flash', 'Unknown token.');
        }

        $user->confirm();

        return redirect('/threads')
            ->with('flash', 'Your account is now confirmed! You may post to the forum.');
    }
}
