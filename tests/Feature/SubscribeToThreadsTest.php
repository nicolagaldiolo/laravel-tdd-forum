<?php

namespace Tests\Feature;

use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SubscribeToThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testAUserCanSubscribeToThreads()
    {
        // Loggo l'utente
        $this->signIn();

        // Creo il thread
        $thread = create(Thread::class);

        // Mi iscrivo al thread
        $this->post($thread->path() . '/subscriptions');


        $this->assertCount(1, $thread->fresh()->subscriptions);
    }

    public function testAUserCanUnSubscribeFromThreads()
    {
        // Loggo l'utente
        $this->signIn();

        // Creo il thread
        $thread = create(Thread::class);

        $thread->subscribe();

        // Mi iscrivo al thread
        $this->delete($thread->path() . '/subscriptions');

        $this->assertCount(0, $thread->subscriptions);
    }
}
