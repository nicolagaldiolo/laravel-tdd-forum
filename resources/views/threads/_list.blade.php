@forelse($threads as $thread)
    <div class="card mb-3">
        <div class="card-header">
            <h4 class="d-flex">
                <div class="d-flex flex-column">
                    <a href="{{ $thread->path() }}">
                        @if(Auth::check() && $thread->hasUpdatedFor(auth()->user()))
                            <strong style="color: red;">{{ $thread->title }}</strong>
                        @else
                            {{ $thread->title }}
                        @endif
                    </a>
                    <small> Posted By:<a href="{{ route('profile', $thread->creator) }}">{{ $thread->creator->name }}</a></small>
                </div>

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

        <div class="card-footer">
            {{-- $thread->visits()->count() --}}
            {{ $thread->visits }} visits
        </div>
    </div>
@empty
    <p>There are no relevant results at this time</p>
@endforelse