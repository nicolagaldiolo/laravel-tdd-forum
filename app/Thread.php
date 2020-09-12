<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Thread extends Model
{

    use RecordsActivity;

    protected $guarded = [];

    protected $with = ['creator', 'channel'];

    protected static function boot()
    {

        parent::boot();

        static::addGlobalScope('replyCount', function($builder){
            $builder->withCount('replies');
        });

        // Ascolto la cancellazione del modello per cancellare tutte le "dipendenze"
        // posso farlo direttamente a db o gestirlo da programma

        // Definisco un observer trait closure (in alternativa potrei creare una classe observer dedicata)
        static::deleting(function ($thread) {

            // Cancellazione tramite modello VS cancellazione tramite query sql

            // facendo una cancellazione massiva (quindi non utilizzando l'istanza del modello ma bensì attraverso l'uso di query) non si scatenano gli eventi di cancellazione sul modello App\Reply
            //$thread->replies()->delete();

            // facendo una cancellazione puntale (quindi utilizzo l'istanza del modello e quindi ciclo tutti i risultati) e cancello il modello, allora si scatenano gli eventi di
            // cancellazione sul modello App\Reply
            //$thread->replies->each(function ($model){
            //  $model->delete();
            //});
            $thread->replies->each->delete();
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function addReply($reply)
    {
        return $this->replies()->create($reply);
    }

    public function scopeFilter($query, $filters)
    {
        // lancio il metodo apply della classe ThreadFilters passandogli il query builder
        return $filters->apply($query);
    }
}
