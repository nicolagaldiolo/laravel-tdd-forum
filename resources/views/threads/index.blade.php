@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @include('threads._list')

                {{ $threads->render() }}
            </div>
            @if(count($trending))
                <div class="col-md-4">

                    <div class="card">
                        <div class="card-header">
                            Search
                        </div>
                        <div class="card-body">
                            <form action="/threads/search">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="q" placeholder="Search for something...">
                                </div>
                                <button class="btn btn-primary form-control" type="submit">Search</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            Thrending threads
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($trending as $thread)
                                    <li class="list-group-item">
                                        <a href="{{ $thread->path }}">
                                            {{ $thread->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
