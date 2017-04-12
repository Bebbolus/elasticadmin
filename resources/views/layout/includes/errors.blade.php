@if(Session::has('error_message'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>
        {{ Session::get('error_message') }}
    </div>
@endif

@if(Session::has('validation_errors'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>

        <ul>
            @foreach(Session::get('validation_errors')->all() as $error)
                <li>{{ $error }};</li>
            @endforeach
        </ul>
    </div>
@endif

@if(Session::has('warning_message'))
    <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>
        {{ Session::get('warning_message') }}
    </div>
@endif

@if(Session::has('success_message'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>
        {{ Session::get('success_message') }}
    </div>
@endif

@if(Session::has('info_message'))
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi"><span aria-hidden="true">&times;</span></button>
        {!! Session::get('info_message') !!}
    </div>
@endif

