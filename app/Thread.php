<?php

namespace App;

use App\Events\ThreadReceivedNewReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Thread extends Model
{

    use RecordsActivity, Searchable;

    protected $guarded = [];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $with = ['creator', 'channel'];

    protected $appends = ['isSubscribedTo'];

    protected $casts = [
        'locked'  =>  'boolean'
    ];

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

        static::created(function ($thread) {
            $thread->update(['slug' => $thread->title]);
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
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

    public function setSlugAttribute($value)
    {
        // NEL CASO IN CUI VOGLIO DEGLI SLUG INCREEMENTALI
        // foo-bar
        // foo-bar-2
        // foo-bar-3
        /*

        $original = $slug;
        $count = 2;
        while(static::whereSlug($slug = Str::slug($value))->exists()){
            $slug = "{$original}-" . $count ++;
        }
        */

        // NEL CASO IN CUI VOGLIO UNO SLUG BASATO SU ID IN CASO SIA GIà OCCUPATO IL NOME
        if (static::whereSlug($slug = Str::slug($value))->exists()) {
            $slug = "{$slug}-{$this->id}";
        }

        $this->attributes['slug'] = $slug;

    }

    public function hasUpdatedFor($user)
    {
        // Verifico se il thread è stato aggiornato dall'ultima volta che l'ho visto
        // Il valore della chiave Key è il timestamp dell'ultima visita
        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }

    /*
     * Creare una classe dedicata e utilizzare Redis per gestire il conteggio delle visite ha senso solo
     * se abbiamo tantissime visite e dobbiamo risparmiare un interrogazione a db. Per questo motivo ho
     * rimosso l'ultizzo della classe visits, ma semplicemente ho aggiunto un campo a db per gestire le visite
     */

    /*public function visits()
    {
        return new Visits($this);
    }
    */

    public function markBestReply(Reply $reply)
    {
        $this->update(['best_reply_id' => $reply->id]);
    }

    // LARAVEL SCOUT
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    // Ciò che viene inviato ad algolia è il risultato di questo metodo (presente nel trait searchable).
    // Dato che ho bisogno di aggiungere anche il path della risorsa sovrascrivo il qui il metodo
    public function toSearchableArray()
    {
        return $this->toArray() + ['path' => $this->path()];
    }

}
