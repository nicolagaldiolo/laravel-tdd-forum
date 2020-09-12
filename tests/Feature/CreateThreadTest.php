<?php

namespace Tests\Feature;

use App\Activity;
use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateThreadTest extends TestCase
{
    use RefreshDatabase;

    function testGuestsMayNotCreateThreads()
    {

        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect('/login');

        $this->post('/threads', [])
            ->assertRedirect('/login');
    }

    function testAnAuthenticatedUserCanCreateNewForumThreads()
    {
        $this->signIn(); //

        $thread = make(Thread::class);

        $response = $this->post('/threads', $thread->toArray());

        $this->get($response->headers->get('Location')) // contiene il path definito come redirect nel metodo store del controller
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    function testAThreadRequiresATitle()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    function testAThreadRequiresABody()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

    function testAThreadRequiresAValidChannel()
    {

        factory(Channel::class, 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');
    }


    function testUnauthorizedUsersMayNotDeleteThreads()
    {
        $this->withExceptionHandling();

        $thread = create(Thread::class);

        // tento di cancellare il thread ma non essendo autenticato vengo rediretto
        $this->delete($thread->path())->assertRedirect('/login');

        // mi autentico
        $this->signIn();

        // tento di cancellare il thread ma non avendolo creato io ottengo un errore 403
        $response = $this->delete($thread->path());
            //dd($response->getStatusCode());
            $response->assertStatus(403);

    }

    function testAuthorizedUsersCanDeleteThreads()
    {

        $this->signIn();
        $this->withExceptionHandling();

        $thread = create(Thread::class, ['user_id' => Auth::id()]);
        $reply = create(Reply::class, ['thread_id' => $thread->id]);

        // se uso la funzione json ottengo un errore 401 in quanto mi aspetto di ritorno del json
        $response = $this->json('DELETE', $thread->path())
            ->assertStatus(204);

        // se uso il metodo delete mi aspetto di essere reindinirzzato quindi ottengo un 302
        //$response = $this->delete($thread->path())
        //    ->assertRedirect('/threads');

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, Activity::count());
    }


    public function publishThread($overrides = [])
    {
        $this->signIn();

        $this->withExceptionHandling();

        $thread = make(Thread::class, $overrides);

        return $this->post('/threads', $thread->toArray());
    }
}
