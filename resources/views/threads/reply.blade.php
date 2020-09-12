{{-- v-cloak - This directive will remain on the element until the associated Vue instance finishes compilation. Combined with CSS rules such as [v-cloak] { display: none }, this directive can be used to hide un-compiled mustache bindings until the Vue instance is ready. --}}
<reply :attributes="{{ $reply }}" inline-template v-cloak>
    <div id="reply-{{ $reply->id }} "class="card">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <h5>
                    <a href="{{ route('profile', $reply->owner) }}">
                        {{ $reply->owner->name }}
                    </a> said {{ $reply->created_at->diffForHumans() }}...
                </h5>
                @auth
                    <favorite :reply="{{ $reply }}"></favorite>
                @endauth

                {{--
                <form  action="/replies/{{ $reply->id }}/favorites" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary {{ $reply->isFavorited() ? 'disabled' : '' }}">
                        {{ $reply->favorites_count }} {{ \Illuminate\Support\Str::plural('Favorite', $reply->favorites_count) }}
                    </button>
                </form>
                --}}
            </div>
        </div>

        <div class="card-body">
            <div v-if="editing">
                <textarea class="form-control" v-model="body"></textarea>

                <button class="btn btn-xs btn-primary" @click="update">Update</button>
                <button class="btn btn-xs btn-link" @click="editing = false">Cancel</button>

            </div>
            <div v-else v-text="body">
                {{ $reply->body }}
            </div>
        </div>

        @can('update', $reply)
            <div class="card-footer d-flex">

                <button class="btn btn-xs btn-secondary" @click="editing = true">Edit</button>
                <button class="btn btn-danger btn-xs" @click="destroy">Delete</button>

            </div>
        @endcan
    </div>
</reply>