<?php

namespace Tests\Feature;

use App\Activity;
use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Carbon\Carbon;
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
            ->assertRedirect(route('login'));

        $this->post(route('threads'), [])
            ->assertRedirect(route('login'));
    }

    function testNewUsersMustFirstConfirmTheirEmailAddressBeforeCreatingThreads()
    {

        $user = factory(User::class)->state('unconfirmed')->create();

        $this->signIn($user);

        $thread = make(Thread::class);

        $this->post(route('threads'), $thread->toArray())
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash', 'You must confirm first your email address');
    }

    function testAUserCanCreateNewForumThreads()
    {
        $this->signIn(); //

        $thread = make(Thread::class);

        $response = $this->post(route('threads'), $thread->toArray());

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

    /** @test */
    function testAThreadRequiresAUniqueSlug()
    {
        $this->signIn();

        $thread = create(Thread::class, ['title' => 'foo bar']);

        $this->assertEquals($thread->slug, 'foo-bar');

        $thread = $this->postJson(route('threads'), $thread->toArray())->json();

        $this->assertEquals("foo-bar-{$thread['id']}", $thread['slug']);

    }

    function test_a_thread_with_a_title_that_ends_in_a_number_should_generate_the_proper_slug()
    {
        $this->signIn();

        $thread = create(Thread::class, ['title' => 'foo bar 24']);

        $thread = $this->postJson(route('threads'), $thread->toArray())->json();

        $this->assertEquals("foo-bar-24-{$thread['id']}", $thread['slug']);

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
        //    ->assertRedirect(route('threads'));

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, Activity::count());
    }


    public function publishThread($overrides = [])
    {
        $this->signIn();

        $this->withExceptionHandling();

        $thread = make(Thread::class, $overrides);

        return $this->post(route('threads'), $thread->toArray());
    }
}
