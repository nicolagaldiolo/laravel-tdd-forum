<?php

namespace Tests\Feature;

use App\Activity;
use App\Reply;
use App\Thread;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function testRecordsActivityWhenAThreadIsCreated()
    {
        $this->signIn();

        $thread = create(Thread::class);

        $this->assertDatabaseHas('activities', [
            'type' => 'created_thread',
            'user_id' => Auth::id(),
            'subject_id' => $thread->id,
            'subject_type' => Thread::class
        ]);

        $activity = Activity::first();

        $this->assertEquals($activity->subject->id, $thread->id);
    }

    public function testRecordsActivityWhenAReplyIsCreated()
    {
        $this->signIn();

        create(Reply::class);

        $this->assertEquals(2, Activity::count());
    }

    public function testFetchesAFeedForAnyUser()
    {
        $this->signIn();

        create(Thread::class, ['user_id' => Auth::id()], 2);

        Auth::user()->activity()->first()->update(['created_at' => Carbon::now()->subWeek()]);

        $feed = Activity::feed(Auth::user(), 50);

        $this->assertContains(Carbon::now()->format('Y-m-d'), $feed->keys());

        $this->assertContains(Carbon::now()->subWeek()->format('Y-m-d'), $feed->keys());

    }
}
