<?php

namespace Tests\Unit;

use App\Notifications\ThreadWasUpdated;
use App\User;
use Carbon\Carbon;
use Hamcrest\Thingy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
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

    public function testAThreadHasAPath()
    {
        $thread = create(Thread::class);

        $this->assertEquals(
            "/threads/{$thread->channel->slug}/{$thread->slug}",
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

    public function testATestNotifiesAllRegisteredSubscribersWhenAReplyIsAdded()
    {
        // Si può usare i feature test per testate il meccanismo implementato nell'applicazione,
        // quindi mi baso sui dati che trovo a db per l'invio delle notifiche
        // Oppure si può utilizzare il metodo fake per simulare l'invio delle notifiche

        Notification::fake();

        // Creo 10 utenti e li iscrivo al thread
        $users = create(User::class, [],10)->each(function($user){
            $this->thread->subscribe($user->id);
        });

        $author = $users->random(1)->first();

        // Aggiungo una reply
        $this->thread->addReply([
            'body' => 'foobar',
            'user_id' => $author->id
        ]);

        // Verifico se tutti gli utenti vengono avvisati tranne l'autore
        Notification::assertSentTo($users->except($author->id), ThreadWasUpdated::class);

        // Verifico che all'autore non arrivi nessuna notifica
        Notification::assertNotSentTo($author, ThreadWasUpdated::class);


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

    public function testAThreadCanCheckIfTheAuthenticatedUserHasReadAllReplies()
    {
        $this->signIn();

        $thread = create(Thread::class);

        tap(Auth::user(), function ($user) use($thread){
            $this->assertTrue($thread->hasUpdatedFor($user));

            $user->read($thread);

            $this->assertFalse($thread->hasUpdatedFor($user));
        });
    }

    /*
     * Creare una classe dedicata e utilizzare Redis per gestire il conteggio delle visite ha senso solo
     * se abbiamo tantissime visite e dobbiamo risparmiare un interrogazione a db. Per questo motivo ho
     * rimosso l'ultizzo della classe visits, ma semplicemente ho aggiunto un campo a db per gestire le visite
     *

    public function testAThreadRecordsEachVisit()
    {
        $thread = make(Thread::class, [
            'id' => 1
        ]);

        $thread->visits()->reset();

        $this->assertSame(0, $thread->visits()->count());
        // assertSame perchè voglio essere sicuro che il valore tornato sia 0 e non null
        // altrimenti con assertEquals 0 è uguale a null

        $thread->visits()->record();

        $this->assertEquals(1, $thread->visits()->count());

        $thread->visits()->record();

        $this->assertEquals(2, $thread->visits()->count());
    }
    */
}