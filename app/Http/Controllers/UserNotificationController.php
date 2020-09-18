<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return Auth::user()->unreadNotifications;
    }

    public function destroy(User $user, $notificationId)
    {
        Auth::user()->notifications()->find($notificationId)->markAsRead();
    }
}
