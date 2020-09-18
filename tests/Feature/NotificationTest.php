<?php

namespace Tests\Feature;

use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp() :void
    {
        parent::setUp();

        // Loggo l'utente
        $this->signIn();

    }
    
    /** @test */
    public function testANotificationIsPreparedWhenASubscribedThreadReceinvesANewReplyThatIsNotByTheCurrentUser()
    {

        // Mi assicuro che le notifiche per l'utente siano a zero
        $this->assertCount(0, Auth::user()->notifications);

        // Creo il test e iscrivo l'utente corrente al quel thread
        $thread = create(Thread::class)->subscribe();

        // Aggiungo io stesso una risposta
        $thread->addReply([
            'user_id' => Auth::id(),
            'body' => 'Some reply here'
        ]);

        // Mi assicuro di NON RICEVERE la nofifica perchè io SONO l'autore della rispsota
        $this->assertCount(0, Auth::user()->fresh()->notifications);


        // Aggiungo una risposta effettuata da un altro utente
        $thread->addReply([
            'user_id' => create(User::class)->id,
            'body' => 'Some reply here'
        ]);

        // Mi assicuro di RICEVERE la nofifica perchè io NON SONO l'autore della rispsota
        $this->assertCount(1, Auth::user()->fresh()->notifications);
        
    }

    public function testAUserCanFetchTheirUnreadNotification()
    {
        $thread = create(Thread::class)->subscribe();

        // Aggiungo io stesso una risposta
        $thread->addReply([
            'user_id' => create(User::class)->id,
            'body' => 'Some reply here'
        ]);

        $user = Auth::user();

        $response = $this->getJson("/profiles/{$user->name}/notification")->json();

        $this->assertCount(1, $response);
    }

    public function testAUserCanMarkANotificationAsRead()
    {
        $thread = create(Thread::class)->subscribe();

        // Aggiungo io stesso una risposta
        $thread->addReply([
            'user_id' => create(User::class)->id,
            'body' => 'Some reply here'
        ]);

        $user = Auth::user();

        $this->assertCount(1, $user->unreadNotifications);

        $notificationId = $user->unreadNotifications()->first()->id;

        $this->delete("/profiles/{$user->name}/notification/{$notificationId}");

        $this->assertCount(0, Auth::user()->fresh()->unreadNotifications);

    }
}
