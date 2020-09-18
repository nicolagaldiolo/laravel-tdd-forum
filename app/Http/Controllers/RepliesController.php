<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Reply;
use App\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepliesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except('index');
    }

    public function index(Channel $channel, Thread $thread)
    {
        return $thread->replies()->paginate(5);
    }

    public function store(Channel $channel, Thread $thread)
    {
        $this->validate(request(), [
           'body' => 'required'
        ]);

        $reply = $thread->addReply([
            'body' => request('body'),
            'user_id' => Auth::id()
        ]);

        if(request()->expectsJson()){
            return $reply->load('owner');
        }

        return back()->with('flash', 'Your reply has been left.');
    }

    public function update(Reply $reply)
    {

        $this->authorize('update', $reply);

        $this->validate(\request(), [
            'body' => 'required'
        ]);

        $reply->update(request()->only('body'));

    }

    public function destroy(Reply $reply)
    {

        $this->authorize('update', $reply);

        $reply->delete();

        if(request()->expectsJson()){
            return response(['status' => 'Reply deleted']);
        }

        return redirect()->back();

    }
}
