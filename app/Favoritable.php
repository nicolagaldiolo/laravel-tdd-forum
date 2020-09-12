<?php


namespace App;

use Illuminate\Support\Facades\Auth;

trait Favoritable
{

    protected static function bootFavoritable()
    {

        static::deleting(function($model){
            $model->favorites()->get()->each->delete();
        });
    }



    /**
     * A reply can be favorited.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    /**
     * Favorite the current reply.
     *
     * @return Model
     */
    public function favorite()
    {
        $attributes = ['user_id' => auth()->id()];

        if (! $this->favorites()->where($attributes)->exists()) {
            return $this->favorites()->create($attributes);
        }
    }

    public function unfavorite()
    {
        $attributes = ['user_id' => auth()->id()];


        // Cancellazione tramite modello VS cancellazione tramite query sql

        // facendo una cancellazione massiva (quindi non utilizzando l'istanza del modello ma bensì attraverso l'uso di query) non si scatenano gli eventi di cancellazione sul modello App\Reply
        //$this->favorites()->where($attributes)->delete();

        // facendo una cancellazione puntale (quindi utilizzo l'istanza del modello e quindi ciclo tutti i risultati) e cancello il modello, allora si scatenano gli eventi di
        // cancellazione sul modello App\Reply
        //$this->favorites()->where($attributes)->get()->each(function ($model){
        //    $model->delete();
        //});

        $this->favorites()->where($attributes)->get()->each->delete();

    }

    /**
     * Determine if the current reply has been favorited.
     *
     * @return boolean
     */
    public function isFavorited()
    {

        // NB FARE ATTENZIONE CHE SE VALORIZZIAMO CON IL QUERY BUILDER IL METODO WHERE VUOLE UN ARRAY KEY=>VALUE
        // MENTRE IL METODO WHERE DELLA COLLECTION VUOLE 2 PARAMETRI

        // Metodo via SQL - N+1 problem () ad ogni commento lancio la query per sapere se ho messo il like
        //return $this->favorites()->where(['user_id' => Auth::id()])->exists();

        // Metodo via Collection - NON HO IL N+1 problem () ad ogni commento NON lancio la query in quanto ho già i favorites precaricati tramite eagerLoad, quindi lavoro sulla collection per sapere se ho messo il like
        return !! $this->favorites->where('user_id', auth()->id())->count(); // !! inverto la negazione facendo il cast a boolean
    }

    public function getIsFavoritedAttribute()
    {
        return $this->isFavorited();
    }


    /**
     * Get the number of favorites for the reply.
     *
     * @return integer
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }
}