<?php

namespace Tests\Unit;

use App\Reply;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplyTest extends TestCase
{

    use RefreshDatabase;

    function testHasAnOwner()
    {
        $reply = create(Reply::class);

        $this->assertInstanceOf(User::class, $reply->owner);
    }


    function testItKnowsIfItWasJustPublished()
    {
        $reply = create(Reply::class);

        $this->assertTrue($reply->wasJustPublished());

        $reply->created_at = Carbon::now()->subMonth();

        $this->assertFalse($reply->wasJustPublished());
    }

    function testItCanDetectAllMentionedUsersInTheBody()
    {
        $reply = new Reply([
            'body' => '@JaneDoe wants to talk to @JohnDoe'
        ]);

        $this->assertCount(2, $reply->mentionedUsers());
    }

    function testWrapsMentionedUsernamesInTheBodyWithinAnchorTags()
    {
        $reply = new Reply([
            'body' => 'Hi @NicolaGaldiolo'
        ]);

        $this->assertEquals('Hi <a href="/profiles/NicolaGaldiolo">@NicolaGaldiolo</a>', $reply->body);
    }

    function testItKnowsIfItIsTheBestReply()
    {
        $reply = create(Reply::class);

        $this->assertFalse($reply->isBest());

        $reply->thread()->update([
            'best_reply_id' => $reply->id
        ]);

        $this->assertTrue($reply->fresh()->isBest());
    }

    public function testAReplyBodyIsSanitizedAutomatically()
    {
        $reply = make(Reply::class, ['body' => '<script>alert("bad")</script><p>This is okay.</p>']);

        $this->assertEquals('<p>This is okay.</p>', $reply->body);
    }

}
