<?php

namespace Tests\Feature;

use App\Reply;
use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BestReplyTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function testAThreadCreatorMyMarkAnyReplyAsTheBestReply()
    {
        $this->signIn();

        $thread = create(Thread::class, ['user_id' => Auth::id()]);

        $replies = create(Reply::class, ['thread_id' => $thread->id], 2);

        $this->assertFalse($replies[1]->isBest());

        $this->postJson(route('best-replies.store', $replies[1]->id));

        $this->assertTrue($replies[1]->fresh()->isBest());
    }

    public function test_only_the_thread_creator_may_mark_a_reply_as_best()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create(Thread::class, ['user_id' => Auth::id()]);

        $replies = create(Reply::class, ['thread_id' => $thread->id], 2);

        $this->signIn();

        $this->postJson(route('best-replies.store', $replies[1]->id))->assertStatus(403);

        $this->assertFalse($replies[1]->fresh()->isBest());
    }

    public function test_if_a_best_reply_is_deleted_then_the_thread_is_properly_updated_to()
    {

        // Abilito l'utilizzo delle foreign_key su sql-lite | qui è già
        // abilitato ma se non lo fosse occorre abilitarlo a mano con il seguente comando
        // DB::statement('PRAGMA foreign_keys=on');

        $this->signIn();

        $reply = create(Reply::class, ['user_id' => Auth::id()]);

        $reply->thread->markBestReply($reply);

        $this->deleteJson(route('replies.destroy', $reply->id));

        $this->assertNull($reply->thread->fresh()->best_reply_id);
    }
}
