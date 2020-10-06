<?php

namespace App\Http\Controllers;

use App\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BestReplyController extends Controller
{
    public function store(Reply $reply)
    {

        $this->authorize('update', $reply->thread);
        $reply->thread->markBestReply($reply);
    }
}
