<?php

namespace Tests\Feature;

use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PartecipateInThreadsTest extends TestCase
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
        // Dato un utente autenticato
        $this->signIn();

        // Creo un tread
        $thread = create(Thread::class);

        // Creo un instanza di risposta (è solo in memoria, non è persistente in quanto fatto un make)
        $reply = make(Reply::class);

        // Posto la risposta (a questo punto è persistente)
        $this->post($thread->path() . '/replies', $reply->toArray());

        // Mi aspetto di trovarla a db
        $this->assertDatabaseHas('replies', ['body' => $reply->body]);

        // Chiamo il metodo fresh() in quanto l'istanza non è aggiornata
        $this->assertEquals(1, $thread->fresh()->replies_count);

        // Il contenuto viene caricato via JS quindi non posso basarmi sul fatto che il contenuto è visibile in pagina
        //$this->get($thread->path())->assertSee($reply->body);
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

    function testUnauthorizedUsersCannotDeleteReplies()
    {
        $this->withExceptionHandling();

        $reply = create(Reply::class);

        $this->delete('/replies/' . $reply->id)
            ->assertRedirect('login');

        $reply = create(Reply::class);

        $this->signIn();

        $this->delete('/replies/' . $reply->id)
            ->assertStatus(403);
    }

    function testAuthorizedUsersCanDeleteReplies()
    {
        $this->withExceptionHandling();

        $this->signIn();

        $reply = create(Reply::class, ['user_id' => Auth::id()]);

        $this->delete('/replies/' . $reply->id)
            ->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, $reply->thread->fresh()->replies_count);
    }

    function testUnauthorizedUsersCannotUpdateReplies()
    {
        $this->withExceptionHandling();

        $reply = create(Reply::class);

        $this->patch("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn();

        $this->patch("/replies/{$reply->id}")
            ->assertStatus(403);

    }

    function testAuthorizedUsersCanUpdateReplies()
    {

        $this->signIn();

        $reply = create(Reply::class, ['user_id' => Auth::id()]);

        $updatedReply = 'You been changed, fool.';

        $this->patch("/replies/{$reply->id}", ['body' => $updatedReply]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $updatedReply]);
    }

    function testRepliesThatContainSpamMayNoBeCreated()
    {
        $this->withExceptionHandling();
        $this->signIn();

        // And an Existing thread
        $thread = create(Thread::class);

        // When the user adds a reply to the thread
        $reply = make(Reply::class, [
            'body' => 'Yahoo Customer Support'
        ]);

        // Then their reply should be included on the page
        $this->json('post', $thread->path() . '/replies', $reply->toArray())
            ->assertStatus(422);
    }

    function testUsersMayOnlyReplyAMaximumOfOncePerMinute()
    {

        $this->withExceptionHandling();
        $this->signIn();

        $thread = create(Thread::class);
        $reply = make(Reply::class);

        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertStatus(201);

        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertStatus(429);
    }
}
