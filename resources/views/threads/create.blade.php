@extends('layouts.app')

@section('header')
    <script src="https://www.google.com/recaptcha/api.js"></script>

    <script>
        function onSubmit(token) {
            document.getElementById("thread-post").submit();
        }
    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Create a new Thread') }}</div>

                    <div class="card-body">
                        <form id="thread-post" method="POST" action="/threads">
                            @csrf
                            <div class="form-group">
                                <label>Channel</label>
                                <select class="form-control" name="channel_id" required>
                                    <option value="">Select one...</option>
                                    @foreach($channels as $channel)
                                        <option value="{{ $channel->id }}" @if(old('channel_id') == $channel->id)selected @endif>{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required></input>
                            </div>
                            <div class="form-group">
                                <label>Body</label>
                                <textarea name="body" class="form-control" rows="8" required>{{ old('body') }}</textarea>
                            </div>
                            <div class="form-group">

                                <button class="btn btn-primary g-recaptcha"
                                        data-sitekey="{{ config('services.recaptcha.key') }}"
                                        data-callback='onSubmit'
                                        data-action='submit'>Publish</button>
                            </div>

                            @if(count($errors))
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
