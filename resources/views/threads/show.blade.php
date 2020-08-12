@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <a href="#">{{ $thread->creator->name }}</a> posted:
                        {{ $thread->title }}
                    </div>

                    <div class="card-body">
                        {{ $thread->body }}
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="row justify-content-center">
            <div class="col-md-8">
                @foreach($thread->replies as $reply)
                    @include('threads.reply')
                @endforeach
            </div>
        </div>

        <hr>

        <div class="row justify-content-center">
            <div class="col-md-8">
                @if(auth()->check())
                    <form method="POST" action="{{ $thread->path() . '/replies'}}">
                        @csrf
                        <div class="form-group">
                            <textarea name="body" class="form-control" rows="5" placeholder="Have something to say?"></textarea>
                        </div>

                        <button type="submit" class="btn btn-default">Post</button>
                    </form>
                @else
                    <p class="text-center">Please <a href="{{ route('login') }}">sign in</a> to partecipate in this discussion</p>
                @endif
            </div>
        </div>
    </div>
@endsection
