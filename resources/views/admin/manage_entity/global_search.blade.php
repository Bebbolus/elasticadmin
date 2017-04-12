@extends('layout.main') @section('content')
    <script>
        var indices = {!! json_encode($indices) !!};
    </script>
        <div class="row row-title">
            <div class="col-md-12">
                <h1><i class="fa fa-ticket"></i> Entity Management </h1>
            </div>
        </div>


        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"> Manage</h3>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4 col-xs-12">
                                <div class="form-control-wrapper">
                                    <div class="form-label">
                                        Select Index
                                    </div>
                                    <div class="input-group">
                                        <select class="form-control input" name="filters[index]"
                                                id="select_index_input">
                                            <option selected></option>
                                            @foreach($indices as $k => $v)
                                                <option
                                                        @if(isset($filters['index']))
                                                        @if($filters['index'] == $k)
                                                        selected
                                                        @endif
                                                        @endif
                                                        value="{{$k}}">{{strtoupper($k)}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{--PANNELLO RICERCA NEI TIPI--}}
                            <div class="col-md-4 col-xs-12" id="types_list">
                                <div class="form-control-wrapper">
                                    <div class="form-label">
                                        Select Type
                                    </div>
                                    <div class="input-group">
                                        <select class="form-control input" name="filters[type]" id="select_type_input">

                                        </select>
                                    </div>
                                </div>
                            </div>
                            {{--/PANNELLO RICERCA NEI TIPI--}}

                            <div class="col-md-4 col-xs-12">
                                <div class="form-control-wrapper">
                                    <div class="form-label">
                                        Search ID
                                    </div>
                                    <div class="input-group">
                                        <input id="id" name="id" type="text" class="form-control input" value="">
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-4 col-xs-12">
                                <div class="form-control-wrapper">

                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="operation" id="operation1" value="view" checked>
                                            Show
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="operation" id="operation2" value="edit">
                                            Update
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="operation" id="operation3" value="create">
                                            Create
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="operation" id="operation4" value="delete">
                                            Delete
                                        </label>
                                    </div>

                                    {{--<input name="edit" type="checkbox"> Modifica--}}
                                    {{--<input name="create" type="checkbox"> Crea--}}
                                </div>
                            </div>
                        </div>

                        {{--PANNELLO RICERCA NEI METADATI--}}
                        <div class="row" id="search_meta">

                        </div>
                        {{--./PANNELLO RICERCA NEI METADATI--}}

                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Start Search
                        </button>&nbsp;
                    </div>
                </form>
            </div>
        </div>

    <div class="row">
        @if($hits > 0)
            <div class="panel panel-default">
                <div class="panel-heading">
                    Results {{ucwords ($type)}}
                </div>
                <!-- START -->
                <div style="overflow: auto">
                    <div class="table-responsive" style="min-height:50px;">
                        <table class="table table-striped table-condensed table-bordered" >
                            <thead>
                            <tr>
                                @foreach($tableHead as $head)
                                    {{--@if($head != 'id')--}}
                                        <th>
                                            {{strtoupper($head)}}
                                        </th>
                                    {{--@endif--}}
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($results as $result)
                                <tr>
                                    @foreach($tableHead as $head)
                                        {{--@if($head != 'id')--}}
                                            @if(isset($result[$head]))
                                                @if($head == 'id')
                                                    <td class="">
                                                        <a href="/entity/show/{{$filters['index']}}/{{$type}}/{{$result['id']}}"> @if($result[$head] != '') {{$result[$head]}} @else {{$result['id']}} @endif </a>
                                                    </td>
                                                @else
                                                    <td class=""> {{$result[$head]}}  </td>
                                                @endif
                                            @else
                                                @if(strpos($head,'id')!== FALSE)
                                                    <td class="">
                                                        <a href="/entity/show/{{$filters['index']}}/{{$type}}/{{$result['id']}}"> {{$result['id']}} </a>
                                                    </td>
                                                @else
                                                    <td class=""> </td>
                                                @endif
                                            @endif
                                        {{--@endif--}}
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- END -->
                </div>

            </div>
        @endif
    </div>


@endsection