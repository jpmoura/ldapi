@extends('layout')

@section('title')
    Dashboard
@endsection

@section('homeActive')
    class="active"
@endsection

@section('body')
    <div class="text-center">
        <h3>Select a option</h3>
        <a class="btn btn-default" href="{{ url('/list/settings') }}"><i class="fa fa-3x fa-sliders"></i> <br> AD Settings</a>
        <a class="btn btn-default" href="{{ url('/list/fields') }}"><i class="fa fa-3x fa-th-list"></i> <br> AD Fields</a>
        <a class="btn btn-default" href="{{ url('/list/users') }}"><i class="fa fa-3x fa-users"></i> <br>API Users</a>
    </div>
@endsection