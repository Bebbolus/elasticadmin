@extends('layout.api') @section('content')
<div class="container">
	<div class="row row-title">
		<div class="col-md-8">
			<h1><i class="fa fa-question-circle"></i> TEST</h1>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">From di inserimento</h3>
		</div>

        <form class="form-horizontal" id="full_text" role="form" method="POST" action="">
            <div class="panel-body">
                {!! $formBody !!}

            </div>


            <div class="row">
                <div class="col-xs-12">
                    <div class="col-md-6 col-xs-12">
                        <div class="form-control-wrapper">
                            <div class="form-label">
                                Checklist
                            </div>
                            <select class="form-control input-sm" name="field_CHECKLIST" id="doc_checklist" onchange="loadChecklistField();">
                                <option></option>
                                @foreach($checklistItems as $item)
                                <option value="{{$item[0]}}">{{$item[1]}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="campi_parametrici">
                <img src="/img/ajax_loader_3.gif" id="loaderField" style="display: none;">
                <div id="container_checklist"></div>
            </div>
        </form>
	</div>
</div>
<script type="text/javascript">
function initDatepicker(id) {
    $("#"+id).datetimepicker({
        format: 'DD/MM/YYYY'
    });
}
</script>
@endsection