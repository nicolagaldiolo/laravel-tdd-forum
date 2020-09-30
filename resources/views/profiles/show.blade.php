@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="page-header mb-3">
                    <avatar-form :user="{{ $profileUser }}"></avatar-form>
                </div>

                @forelse($activities as $date => $activity)
                    <div class="card mt-3">
                        <div class="card-header">
                            {{ $date }}
                        </div>
                        <div class="card-body">
                            @foreach($activity as $record)
                                @if(view()->exists("profiles.activities.{$record->type}"))
                                    @include("profiles.activities.{$record->type}", ['activity' => $record])
                                @endif
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p>There is no activity for this user yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

