@extends('layout.main') @section('content')
        <div class="row row-title">
            <div class="col-md-12">
                <h1><i class="fa fa-ticket"></i> Update Global</h1>
            </div>
        </div>


        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"> Update {{$type}}</h3>
                </div>
                <form method="POST" action="/entity/update/{{$index}}/{{$type}}/{{$id}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="panel-body">
                        {!! $formBody !!}
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Update</button>&nbsp;
                    </div>
                </form>
            </div>
        </div>
@endsection