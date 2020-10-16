<div class="card" v-if="editing">
    <div class="card-header">
        <div class="form-group">
            <input type="text" class="form-control" v-model="form.title">
        </div>
    </div>

    <div class="card-body">
        <div class="form-group">
            <wysiwyg name="body" v-model="form.body"></wysiwyg>
        </div>
    </div>

    <div class="card-footer">
        <div class="d-flex">
            <button class="btn btn-sm btn-primary mr-1" @click="update">Update</button>
            <button class="btn btn-sm btn-secondary mr-1" @click="resetForm">Cancel</button>

            @can('update', $thread)
                <form action="{{ $thread->path() }}" method="POST" class="ml-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete Thread</button>
                </form>
            @endcan
        </div>

    </div>

</div>

<div class="card" v-else>
    <div class="card-header d-flex">
        <img src="{{ $thread->creator->avatar_path }}" width="25" height="25" class="mr-1" alt="{{ $thread->creator->name }}">

        <a href="{{ route('profile', $thread->creator) }}">{{ $thread->creator->name }}</a> posted: <span v-text="title"></span>
    </div>

    <div class="card-body" v-html="body"></div>

    <div class="card-footer" v-if="authorize('owns', thread)">
        <button class="btn btn-sm btn-secondary" @click="editing = true">Edit</button>
    </div>

</div>