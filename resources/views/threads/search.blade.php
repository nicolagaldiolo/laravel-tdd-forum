@extends('layouts.app')

@section('content')

    <div class="container">
        <!-- https://www.algolia.com/doc/guides/building-search-ui/getting-started/vue/ -->
        <search
            appid="{{ config('scout.algolia.id') }}"
            appkey="{{ config('scout.algolia.key') }}"
            indexname="threads"
            searchparam="{{ request('q') }}"
        ></search>
    </div>
@endsection