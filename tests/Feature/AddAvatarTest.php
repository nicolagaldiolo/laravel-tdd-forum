<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddAvatarTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function testOnlyMembersCanAddAvatars()
    {

        $this->withExceptionHandling();

        $this->json('POST', 'api/users/1/avatar')
            ->assertStatus(401); // 401 perchè non sono autenticato. 403 se non sono autorizzato
    }

    public function testAValidAvatarMustBeProvided()
    {

        $this->withExceptionHandling()->signIn();

        $this->json('POST', 'api/users/' . Auth::id() . '/avatar', [
            'avatar' => 'not-an-image'
        ])->assertStatus(422); // entità non processabile
    }


    public function testAUserMayAddAnAvatarToTheirProfile()
    {
        $this->withExceptionHandling()->signIn();

        Storage::fake('public');

        $this->json('POST', 'api/users/' . Auth::id() . '/avatar', [
            'avatar' => $file = UploadedFile::fake()->image('avatar.jpg') //UploadedFile ha un metodo statico fake() che torna un istanza di FileFactory sul quale chiamiamo image()
        ]);

        $this->assertEquals(asset('avatars/' . $file->hashName()), Auth::user()->avatar_path);

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

}
