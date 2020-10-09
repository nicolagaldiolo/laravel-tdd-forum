<?php

namespace Tests\Feature;

use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LockThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_administrators_may_not_lock_threads()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create(Thread::class);

        $this->post(route('locked-threads.store', $thread))->assertStatus(403);

        $this->assertFalse($thread->fresh()->locked);

    }

    public function test_administrators_can_lock_threads()
    {
        $user = factory(User::class)->state('administrator')->create();

        $this->withExceptionHandling()->signIn($user);

        $thread = create(Thread::class, ['user_id' => Auth::id()]);
        $this->post(route('locked-threads.store', $thread))->assertStatus(200);

        $this->assertTrue($thread->fresh()->locked, 'Failed asserting that the htread was locked.');
    }

    public function test_administrators_can_unlock_threads()
    {
        $user = factory(User::class)->state('administrator')->create();

        $this->withExceptionHandling()->signIn($user);

        $thread = create(Thread::class, ['user_id' => Auth::id(), 'locked' => true]);
        $this->delete(route('locked-threads.destroy', $thread))->assertStatus(200);

        $this->assertFalse($thread->fresh()->locked, 'Failed asserting that the htread was unlocked.');
    }

    public function test_once_locked_a_thread_may_not_receive_new_replies()
    {
        $this->signIn();

        $thread = create(Thread::class, [
            'locked' => true
        ]);

        $this->post($thread->path() . '/replies', [
            'body' => 'foobar',
            'user_id' => Auth::id()
        ])->assertStatus(422);
    }
}
