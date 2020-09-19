@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @forelse($threads as $thread)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="d-flex">
                                <a href="{{ $thread->path() }}">
                                    @if($thread->hasUpdatedFor(auth()->user()))
                                        <strong style="color: red;">{{ $thread->title }}</strong>
                                    @else
                                        {{ $thread->title }}
                                    @endif
                                </a>
                                <a href="{{ $thread->path() }}" class="ml-auto">
                                    <small>{{ $thread->replies_count }} {{ Str::plural('reply', $thread->replies_count) }}</small>
                                </a>
                            </h4>
                        </div>

                        <div class="card-body">
                            <div class="body">
                                {{ $thread->body }}
                            </div>

                        </div>
                    </div>
                @empty
                    <p>There are no relevant results at this time</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
