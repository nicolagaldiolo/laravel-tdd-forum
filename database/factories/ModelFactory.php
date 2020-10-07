<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Thread;
use App\Reply;
use App\Channel;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'confirmed' => true, // conferma account manuale (solo per scopi didattici, altrimenti usare funzioanlità built-in laravel)
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

// Posso creare anche le factory con uno stato.
// Di default la factory user è già confermato ma posso anche creare lo stato non confermato
// che verrà chiamato così factory(User::class)->state('unconfirmed')->create() per sovrascrivere il default
$factory->state(User::class, 'unconfirmed', function (){
    return [
        'confirmed' => false // conferma account manuale (solo per scopi didattici, altrimenti usare funzioanlità built-in laravel)
    ];
});


$factory->define(Thread::class, function ($faker){

    $title = $faker->sentence;

    return [
        'channel_id' => function(){
            return factory(Channel::class)->create()->id;
        },
        'user_id' => function(){
            return factory(User::class)->create()->id;
        },
        'title' => $title,
        'body' => $faker->paragraph,
        'visits' => 0
    ];
});

$factory->define(Channel::class, function ($faker){
    $name = $faker->word;

    return [
        'name' => $name,
        'slug' => $name
    ];
});

$factory->define(Reply::class, function ($faker){
    return [
        'user_id' => function(){
            return factory(User::class)->create()->id;
        },
        'thread_id' => function(){
            return factory(Thread::class)->create()->id;
        },
        'body' => $faker->paragraph
    ];
});

$factory->define(\Illuminate\Notifications\DatabaseNotification::class, function ($faker){
    return [
        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'type' => 'App\Notifications\ThreadWasUpdated',
        'notifiable_id' => function(){
            return auth()->id() ?: factory(User::class)->create()->id;
        },
        'notifiable_type' => User::class,
        'data' => ['foo', 'bar']
    ];
});