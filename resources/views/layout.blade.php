<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LD(AP)I - @yield('title')</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @yield('extrasHeadImports')
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
@yield('extrasTopBodyImports')

<nav class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/admin') }}">LD(AP)I</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbar">
            <ul class="nav navbar-nav">
                <li @yield('homeActive')><a href="{{ url('/admin') }}"><i class="fa fa-home"></i> Home</a></li>
                <li @yield('settingsActive')><a href="{{ url('/list/settings') }}"><i class="fa fa-sliders"></i> AD Settings</a></li>
                <li @yield('fieldsActive')><a href="{{ url('/list/fields') }}"><i class="fa fa-th-list"></i> AD Fields</a></li>
                <li @yield('usersActive')><a href="{{ url('/list/users') }}"><i class="fa fa-users"></i> API Users</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<div class="container">
    @if(isset($_SESSION["type"]))
        <div class="row">
            <div class="alert @if($_SESSION["type"] == "success") alert-success @else alert-danger @endif">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {!! $_SESSION["message"] !!}
            </div>
        </div>

        @php
            session_unset();
            session_destroy();
        @endphp
    @endif

    @yield('body')
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
@yield('extrasBottomBodyImports')
</body>
</html>
