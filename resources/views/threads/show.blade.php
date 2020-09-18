@extends('layouts.app')

@section('content')
    <thread-view :initial-replies-count="{{ $thread->replies_count }}" inline-template>
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex">
                        <a href="{{ route('profile', $thread->creator) }}">{{ $thread->creator->name }}</a> posted:
                        {{ $thread->title }}

                        @can('update', $thread)
                            <form action="{{ $thread->path() }}" method="POST" class="ml-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link">Delete Thread</button>
                            </form>
                        @endcan
                    </div>

                    <div class="card-body">
                        {{ $thread->body }}
                    </div>
                </div>

                <hr>

                <replies @added="repliesCount++" @removed="repliesCount--"></replies>


            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p>
                            This threads was published {{ $thread->created_at->diffForHumans() }} by
                            <a href="#">{{ $thread->creator->name }}</a>, and currently
                            has <span v-text="repliesCount"></span> {{ Str::plural('comment', $thread->replies_count) }}.

                            <div class="mt-2">
                                <subscribe-button :active="{{ json_encode($thread->isSubscribedTo) }}"/>
                            </div>

                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </thread-view>
@endsection