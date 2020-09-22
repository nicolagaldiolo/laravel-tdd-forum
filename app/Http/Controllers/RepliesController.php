<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Http\Requests\CreatePostRequest;
use App\Reply;
use App\Rules\SpamFree;
use App\Thread;
use Illuminate\Support\Facades\Gate;
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

    public function store(Channel $channel, Thread $thread, CreatePostRequest $form)
    {

        return $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id()
        ])->load('owner');
    }

    public function update(Reply $reply)
    {

        $this->authorize('update', $reply);

        try {
            $this->validate(\request(), [
                'body' => ['required', new SpamFree]
            ]);

            $reply->update(request()->only('body'));

        }catch (\Exception $e){
            return response('Sorry, your reply could not be saved at this time.', 422);
        }



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
