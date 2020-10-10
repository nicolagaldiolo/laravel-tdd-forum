<?php

namespace Tests\Feature;

use App\Thread;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UpdateThreadsTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling()->signIn();

    }

    function test_a_thread_required_a_body_and_title_to_be_updated()
    {

        $thread = create(Thread::class, ['user_id' => Auth::id()]);

        $this->patch($thread->path(), [
            'title' => 'Changed',
        ])->assertSessionHasErrors('body');

        $this->patch($thread->path(), [
            'body' => 'Changed body.',
        ])->assertSessionHasErrors('title');
    }

    function test_unauthorized_user_may_not_updated_threads()
    {

        $thread = create(Thread::class, ['user_id' => create(User::class)]);

        $this->patch($thread->path(), [
            'title' => 'Changed',
            'body' => 'Changed body.',
        ])->assertStatus(403);

    }

    function test_a_thread_can_be_updated_by_its_creator()
    {

        $thread = create(Thread::class, ['user_id' => Auth::id()]);

        $this->patch($thread->path(), [
            'title' => 'Changed',
            'body' => 'Changed body.',
        ]);

        tap($thread->fresh(), function ($thread){
            $this->assertEquals('Changed', $thread->title);
            $this->assertEquals('Changed body.', $thread->body);
        });


    }
}
