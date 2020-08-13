<?php

namespace Tests\Feature;

use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReadThreadsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    use RefreshDatabase;

    protected $thread;

    protected function setUp() :void
    {
        parent::setUp();

        $this->thread = create(Thread::class);

    }

    public function testThatUserCanBrowseThreads()
    {
        $this->get('/threads')
            ->assertStatus(200);
    }

    public function testThatUserCanSeeThreads()
    {

        $this->get('/threads')
            ->assertSee($this->thread->title);
    }

    public function testThatUserCanSeeSingleThread()
    {

        $this->get($this->thread->path())
            ->assertSee($this->thread->title);
    }

    public function testAUserCanReadRepliesThatAreAssociatedWithAThread()
    {

        $reply = create(Reply::class, ['thread_id' => $this->thread->id]);

        $this->get($this->thread->path())
            ->assertSee($reply->body);
    }

    public function testAUserCanFilterThreadsAccordingToAChannel()
    {
        $channel = create(Channel::class);
        $threadInChannel = create(Thread::class, ['channel_id' => $channel->id]);
        $threadNotInCnannel = create(Thread::class);

        $this->get('/threads/' . $channel->slug)
            ->assertSee($threadInChannel->title)
            ->assertDontSee($threadNotInCnannel->title);
    }

    public function testAUserCanFilterThreadsByAnyUsername()
    {
        $username = 'JohnDoe';

        $this->signIn(create(User::class, ['name' => $username]));

        $threadByJohn = create(Thread::class, ['user_id' => Auth::id()]);
        $threadNotByJohn = create(Thread::class);

        $this->get('threads?by=' . $username)
            ->assertSee($threadByJohn->title)
            ->assertDontSee($threadNotByJohn->title);
    }
}
