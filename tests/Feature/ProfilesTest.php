<?php

namespace Tests\Feature;

use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProfilesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testAUserHasAProfile()
    {
        $user = create(User::class);

        $this->get("/profiles/{$user->name}")
            ->assertSee($user->name);
    }

    public function testProfiles_display_all_threads_created_by_the_associated_user()
    {
        $this->signIn();

        $thread = create(Thread::class, ['user_id' => Auth::id()]);

        $this->get("/profiles/" . Auth::user()->name)
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }
}

