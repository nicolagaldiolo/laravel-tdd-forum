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

        // Il contenuto viene caricato via JS quindi non posso basarmi sul fatto che il contenuto è visibile in pagina
        //$this->get($this->thread->path())->assertSee($reply->body);

        $this->assertDatabaseHas('replies', ['body' => $reply->body]);
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

    public function testAUserCanFilterThreadsByPopularity()
    {
        $threadsWithTwoReplies = create(Thread::class);
        create(Reply::class, ['thread_id' => $threadsWithTwoReplies->id], 2);

        $threadsWithThreeReplies = create(Thread::class);
        create(Reply::class, ['thread_id' => $threadsWithThreeReplies->id], 3);

        $threadsWithNoReply = $this->thread;

        $response = $this->getJson('threads?popular=1')->json();

        $this->assertEquals([3,2,0], array_column($response['data'], 'replies_count'));
    }

    public function testAUserCanFilterThreadsByThoseThatAreUnanswered()
    {
        // Un thread viene creato di default (metodo setUp) per ogni test quindi uno è già presente

        // Creo un altro test ma a questo associo una risposta
        $thread = create(Thread::class);
        create(Reply::class, ['thread_id' => $thread->id], 1);

        $response = $this->getJson('threads?unanswered=1')->json();

        $this->assertCount(1, $response['data']);
    }

    public function testAUserCanRequestAllRepliesForAGivenThread()
    {
        $thread = create(Thread::class);

        create(Reply::class, ['thread_id' => $thread->id], 2);

        $response = $this->getJson($thread->path() . '/replies')->json();

        $this->assertCount(2, $response['data']);
        $this->assertEquals(2, $response['total']);
    }

    public function testWeRecordANewVisitEachTimeTheThreadIsRead()
    {
        $thread = create(Thread::class);

        $this->assertSame(0, $thread->visits);

        $this->get($thread->path());

        $this->assertEquals(1, $thread->fresh()->visits);
    }

}
