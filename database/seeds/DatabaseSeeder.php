<?php

use Illuminate\Database\Seeder;
use App\Thread;
use App\Reply;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);

        factory(User::class, 1)->create([
            'name' => 'NicolaGaldiolo',
            'email' => 'galdiolo.nicola@gmail.com'
        ])->each(function ($user){
            factory(Thread::class, 10)->create([
                'user_id' => $user->id
            ])->each(function ($thread){
                factory(Reply::class, 10)->create([
                    'thread_id' => $thread->id
                ]);
            });
        });

        factory(Thread::class, 40)->create()->each(function ($thread){
            factory(Reply::class, 10)->create([
                'thread_id' => $thread->id
            ]);
        });

    }
}
