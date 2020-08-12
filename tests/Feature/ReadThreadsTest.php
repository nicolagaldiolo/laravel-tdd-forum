<?php

namespace Tests\Feature;

use App\Reply;
use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
