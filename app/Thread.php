<?php

namespace App;

use App\Events\ThreadReceivedNewReply;
use App\Notifications\ThreadWasUpdated;
use App\Notifications\YouWereMentioned;
use Hamcrest\Thingy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Thread extends Model
{

    use RecordsActivity;

    protected $guarded = [];

    protected $with = ['creator', 'channel'];

    protected $appends = ['isSubscribedTo'];

    protected static function boot()
    {

        parent::boot();

        // NON SERVE PIù ESEGUIRE LA QUERY SULLA RELAZIONE REPLIES IN QUANTO HO CREATO LA COLONNA AD HOC
        //static::addGlobalScope('replyCount', function($builder){
        //    $builder->withCount('replies');
        //});

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
        $reply = $this->replies()->create($reply);

        event(new ThreadReceivedNewReply($reply));

        return $reply;
    }

    public function scopeFilter($query, $filters)
    {
        // lancio il metodo apply della classe ThreadFilters passandogli il query builder
        return $filters->apply($query);
    }

    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: Auth::id()
        ]);

        return $this;
    }

    public function unsubscribe($userId = null)
    {
        $this->subscriptions()->where('user_id', $userId ?: Auth::id())->delete();
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()->where('user_id', Auth::id())->exists();
    }

    public function hasUpdatedFor($user)
    {
        // Verifico se il thread è stato aggiornato dall'ultima volta che l'ho visto
        // Il valore della chiave Key è il timestamp dell'ultima visita
        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }
}
