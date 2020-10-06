<?php

namespace Tests\Feature;

use App\Mail\CustomPleaseConfirmYourEmail;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function testAConfirmationEmailIsSentUponRegistration()
    {

        // Istruisci la classe mail di usare la classe fake MailFake
        // così da essere molto più veloce in quanto la mail non viene tecnicamente inviata
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Nicola',
            'email' => 'nicola@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Testo se la mail è stata messa in coda
        Mail::assertQueued(CustomPleaseConfirmYourEmail::class);
    }

    /** @test */
    public function testAUserCanFullyConfirmTheirEmailAddresses()
    {

        // Istruisci la classe mail di usare la classe fake MailFake
        // così da essere molto più veloce in quanto la mail non viene tecnicamente inviata
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Nicola',
            'email' => 'nicola@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::whereName('Nicola')->first();

        $this->assertFalse($user->confirmed);
        $this->assertNotNull($user->confirmation_token);

        // Let the user to confirm their account
        $this->get(route('register.confirm', ['token' => $user->confirmation_token]))
            ->assertRedirect(route('threads'));

        tap($user->fresh(), function ($user){
            $this->assertTrue($user->confirmed);
            $this->assertNull($user->confirmation_token);
        });
    }

    public function testConfirmingAnInvalidToken()
    {
        $this->get(route('register.confirm', ['token' => 'invalid_token']))
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash', 'Unknown token.');
    }
}
