<?php

namespace App\Filters;

use App\User;
use Illuminate\Http\Request;

// questa è una classe speicifica di filtri in modo da avere una classe filters generica (Filters.php) e poi posso creare filtri specifici come il ThreadFilters

class ThreadFilters extends Filters
{
    protected $filters = ['by', 'popular']; // definisco un array di filtri da applicare

    /** // Filter the query by a given username
     * @param $username
     * @return mixed
     */

    // ogni metodo di filtro è indipendente e ritorna l'instanza del builder filtrata
    protected function by($username)
    {
        $user = User::where('name', $username)->firstOrFail();

        return $this->builder->whereUserId($user->id);
    }

    protected function popular()
    {
        //$this->builder->getQuery()->orders = []; // Rimuovere ordinamento su vecchie versioni di Laravel
        return $this->builder->reorder('replies_count', 'desc'); // Chiamo il metodo reorder() anzichè orderBy() perchè devo rimuovere il precedente ordinamento
    }
}