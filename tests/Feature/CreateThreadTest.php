<?php

namespace Tests\Feature;

use App\Activity;
use App\Channel;
use App\Reply;
use App\Rules\Recaptcha;
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

    protected function setUp(): void
    {
        parent::setUp();

        // Dichiaro che nei test quando viene richiesta la classe Recaptcha in realtà viene tornata il risultato della clousure
        // Ogni volta che viene chiamata la classe Recaptcha in realtà mi faccio mokko la classe, mi assicuro che venga
        // invocato il metodo passes e forzo di ritornare true

        app()->singleton(Recaptcha::class, function(){
            return \Mockery::mock(Recaptcha::class, function ($m){
                $m->shouldReceive('passes')->andReturn(true);
            });
        });
    }

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

        $response = $this->publishThread([
            'title' => 'myTitle',
            'body' => 'myBody'
        ]);

        $this->get($response->headers->get('Location')) // contiene il path definito come redirect nel metodo store del controller
            ->assertSee('myTitle')
            ->assertSee('myBody');
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

    function test_a_thread_required_recaptcha_verification()
    {

        // Nel metodo setup ho dichiarato che qualsiasi volta che viene chiamata la classe Recaptcha in realtà mi faccio
        // tornare una classe che mokka la classe originale Recaptcha in quanto se usassi la classe originale il test fallirebbe.
        // In questo però voglio testare la classe originale e rimuovo l'associazione solo per questo test
        unset(app()[Recaptcha::class]);

        $this->publishThread()
            ->assertSessionHasErrors('g-recaptcha-response');
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

        $thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals("foo-bar-{$thread['id']}", $thread['slug']);

    }

    function test_a_thread_with_a_title_that_ends_in_a_number_should_generate_the_proper_slug()
    {
        $this->signIn();

        $thread = create(Thread::class, ['title' => 'foo bar 24']);

        $thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

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

        return $this->post(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token']);
    }
}
