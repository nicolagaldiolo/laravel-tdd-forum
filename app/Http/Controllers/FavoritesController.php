<?php

namespace App\Http\Controllers;

use App\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoritesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Reply $reply)
    {

        $reply->favorite();

        if(!request()->expectsJson()){
            return back();
        }
    }

    public function destroy(Reply $reply)
    {

        $reply->unfavorite();

        if(!request()->expectsJson()){
            return back();
        }
    }
}
