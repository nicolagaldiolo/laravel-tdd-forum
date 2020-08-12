<?php

namespace Tests\Feature;

use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PartecipateInFormTest extends TestCase
{
    use RefreshDatabase;

    function testUnauthenticatedUsersMayNotAddReplies()
    {
        $this->withExceptionHandling()
            ->post('/threads/some-channel/1/replies', [])
            ->assertRedirect('/login');
    }

    function testAnAuthenticatedUserMayPartecipateInForumThreads()
    {
        // Given we have a authenticated user
        $this->signIn();

        // And an Existing thread
        $thread = create(Thread::class);

        // When the user adds a reply to the thread
        $reply = make(Reply::class);

        // Then their reply should be included on the page
        $this->post($thread->path() . '/replies', $reply->toArray());

        $this->get($thread->path())
            ->assertSee($reply->body);
    }

    function testAReplyRequiresABody()
    {
        // Given we have a authenticated user
        $this->withExceptionHandling()->signIn();

        // And an Existing thread
        $thread = create(Thread::class);

        // When the user adds a reply to the thread
        $reply = make(Reply::class, [
            'body' => null
        ]);

        // Then their reply should be included on the page
        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertSessionHasErrors('body');
    }
}
