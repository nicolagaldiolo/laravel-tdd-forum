<?php

namespace Tests\Unit;

use App\User;
use Hamcrest\Thingy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Thread;
use App\Channel;

class ThreadTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->thread = create(Thread::class);

    }

    public function testAThreadCanMakeAStringPath()
    {
        $thread = create(Thread::class);

        $this->assertEquals(
            "/threads/{$thread->channel->slug}/{$thread->id}",
            $thread->path()
        );
    }

    public function testAThreadHasReplies()
    {
        $this->assertInstanceOf(Collection::class, $this->thread->replies);
    }

    public function testAThreadHasACreator()
    {
        $this->assertInstanceOf(User::class, $this->thread->creator);
    }

    public function testAThreadCanAddAReply()
    {
        $this->thread->addReply([
            'body' => 'foobar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    public function testAThreadsBelongsToAChannel()
    {
        $this->assertInstanceOf(Channel::class, $this->thread->channel);
    }

    public function testAThreadCanBeSubscribedTo()
    {
        // Dato un thread (creato con il metodo setUp())

        // e un utente autenticato
        $this->signIn();

        // quando l'utente si iscrive ad un tread
        $this->thread->subscribe($userId = 1);

        // poi l'utente dovrebbe essere in grado di recuperare tutti i tread al qualche si è iscritto
        $this->assertEquals(1, $this->thread->subscriptions()->where('user_id', $userId)->count());
    }

    public function testAThreadCanBeUnSubscribedFrom()
    {
        // Dato un thread (creato con il metodo setUp())

        // e un utente autenticato
        $this->signIn();

        // l'utente si iscrive ad un tread
        $this->thread->subscribe($userId = 1);

        // l'utente si rimuove dalla sottoscrizione ad un tread
        $this->thread->unsubscribe($userId);

        // poi l'utente dovrebbe essere in grado di recuperare tutti i tread al qualche si è iscritto
        $this->assertCount(0, $this->thread->subscriptions);
    }

    public function testItKnowIfTheAuthenticatedUserIsSubscribedTo()
    {
        $this->signIn();

        $this->assertFalse($this->thread->isSubscribedTo);

        $this->thread->subscribe();

        $this->assertTrue($this->thread->isSubscribedTo);
    }

}