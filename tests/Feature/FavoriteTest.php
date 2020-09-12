<?php

namespace Tests\Feature;

use App\Reply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */

    public function testGuestUserCannotFavoriteAnyReply()
    {

        $this->withExceptionHandling()
            ->post('replies/1/favorites')
            ->assertRedirect('/login');
    }

    public function testAnAuthenticatedUserCanFavoriteAnyReply()
    {
        $reply = create(Reply::class);

        $this->signIn();

        $this->post('replies/' . $reply->id . '/favorites');

        $this->assertCount(1, $reply->favorites);
    }

    public function testAuthenticatedUserCanUnfavoriteAReply()
    {
        $this->signIn();

        $reply = create(Reply::class);

        $reply->favorite();

        $this->delete("/replies/{$reply->id}/favorites");

        $this->assertCount(0, $reply->favorites);
    }

    public function testAnAuthenticatedUserMayOnlyFavoriteAReplyOnce()
    {

        $reply = create(Reply::class);

        $this->signIn();

        try {

            $this->post('replies/' . $reply->id . '/favorites');
            $this->post('replies/' . $reply->id . '/favorites');

        }catch (\Exception $e){

            $this->fail('Did not expect to insert the same record set twice.');

        }


        $this->assertCount(1, $reply->favorites);

    }
}
