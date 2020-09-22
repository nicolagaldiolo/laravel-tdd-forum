<?php

namespace Tests\Feature;

use App\Inspections\Spam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class Spamtest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testChecksForInvalidKeywords()
    {
        $spam = new Spam();

        $this->assertFalse($spam->detect('Innocent reply'));

        $this->expectException(\Exception::class);

        $spam->detect('yahoo customer support');
    }

    public function testChecksForAnyKeyBeingHeldDown()
    {
        $spam = new Spam();

        $this->expectException(\Exception::class);

        $spam->detect('Hello world aaaaaaaaaaa');
    }
}
