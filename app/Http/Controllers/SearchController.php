<?php

namespace App\Http\Controllers;

use App\Thread;
use App\Trending;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function show(Trending $trending){

        // Serve per i test
        // In test uso una chiamata get per farmi tornare i dati.
        // In applicazione utilizzo i componenti Vue messi a disposizione da Algolia
        if(request()->expectsJson()){
            return Thread::search(request('q'))->paginate(25);;
        }

        return view('threads.search', [
            'trending' => $trending->get()
        ]);
    }
}
