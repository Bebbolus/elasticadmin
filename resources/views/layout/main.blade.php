<!DOCTYPE html>
<html lang="en">
<head>
<title>{{config('app.name','ELSADMIN')}}</title>
@include('layout.includes.metatags_icons')
@include('layout.includes.stylesheets')
</head>
<body>
    @include('layout.includes.navbar')


	<div id="wrapper">

    @include('layout.includes.sidebar')

		<!-- Page Content -->
		<div id="page-content-wrapper">

			<div class="container-fluid">
                @include('layout.includes.errors')
                @yield('content')
                <div class="row">
                    <div class="col-md-12 col-md-offset-0">
                        <p>
                        </p>
                    </div>
                </div>
			</div>
		</div>
		<!-- /#page-content-wrapper -->

	</div>
	<!-- /#wrapper -->
@include('layout.includes.javascripts')


</body>
</html>