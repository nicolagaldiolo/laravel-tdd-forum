<?php

namespace Tests\Feature;

use App\Reply;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function testAUserCanFetchTheirMostRecentReply()
    {
        $user = create(User::class);

        $reply = create(Reply::class, ['user_id' => $user->id]);

        $this->assertEquals($reply->id, $user->lastReply->id);
    }

    function testAUserCanDetermineTheirAvatarPath()
    {
        $user = create(User::class);

        $this->assertEquals(asset('images/avatars/default.jpg'), $user->avatar_path);

        $user->avatar_path = 'avatars/me.jpg';

        $this->assertEquals(asset('avatars/me.jpg'), $user->avatar_path);
    }

    function testItsNotPossibleHaveTwoUsersWithTheSameName()
    {
        $this->expectException('Illuminate\Database\QueryException');
        $this->expectExceptionCode(23000);
        $this->expectDeprecationMessage('Integrity constraint violation: 19 UNIQUE constraint failed');

        create(User::class, ['name' => 'JonDoe'], 2);
    }
}
