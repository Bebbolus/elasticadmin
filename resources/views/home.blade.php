@extends('layout.main')
@section('page_title')
    @lang('pages.home')
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12 col-md-offset-0">
            <div class="panel panel-default">
                @section('content')
                    <h1>Welcome</h1>
                    <p>A web front end for browsing and interacting with Elastic Search for Laravel PHP Framework.</p>
                @endsection
            </div>
        </div>
    </div>
@endsection
