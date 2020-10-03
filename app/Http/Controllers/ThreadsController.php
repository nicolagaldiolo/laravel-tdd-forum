<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Filters\ThreadFilters;
use App\Inspections\Spam;
use App\Rules\SpamFree;
use App\Thread;
use App\Trending;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ThreadsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel, ThreadFilters $filters, Trending $trending)
        // ThreadFilters è un instanza della classe ThreadFilters che viene iniettata con la dependency injection
    {

        $threads = $this->getThreads($channel, $filters);

        if(request()->wantsJson()){
            return $threads;
        }

        return view('threads.index', [
            'threads' => $threads,
            'trending' => $trending->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('threads.create');
    }

    /**
     * Store a newly created resource in storage.
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => ['required', new SpamFree],
            'body' => ['required', new SpamFree],
            'channel_id' => 'required|exists:channels,id',
        ]);

        $thread = Thread::create([
            'user_id' => Auth::id(),
            'channel_id' => request('channel_id'),
            'title' => request('title'),
            'body' => request('body'),
        ]);

        return redirect($thread->path())
            ->with('flash', 'Your thread has been published!');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel, Thread $thread, Trending $trending)
    {

        // Ogni volta che visito la pagina registro il timestamp con la seguente chiave
        // formata da id utente e id thread
        if(Auth::check()){
            Auth::user()->read($thread);
        }

        $trending->push($thread);

        /*
        * Creare una classe dedicata e utilizzare Redis per gestire il conteggio delle visite ha senso solo
        * se abbiamo tantissime visite e dobbiamo risparmiare un interrogazione a db. Per questo motivo ho
        * rimosso l'ultizzo della classe visits, ma semplicemente ho aggiunto un campo a db per gestire le visite
        */
        //$thread->visits()->record();
        $thread->increment('visits');

        return view('threads.show', compact('thread'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Thread $thread)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel, Thread $thread)
    {
        // Autorizzo la richiesta
        $this->authorize('update', $thread);

        // Cancello da risorsa
        $thread->delete();

        // Ritorno la risposta
        if(request()->wantsJson()){
            return response([], 204);
        }

        return redirect('/threads');
    }

    /**
     * @param ThreadFilters $filters
     * @param Channel $channel
     * @return mixed
     */
    protected function getThreads(Channel $channel, ThreadFilters $filters)
    {

        $threads = Thread::latest()->filter($filters); // filter è un metodo del modello (queryScope) al quale passo l'instanza della classe ThreadFilters

        if ($channel->exists) {
            $threads->whereChannelId($channel->id);
        }

        return $threads->paginate(20);
    }
}
