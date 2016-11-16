@extends('layout')

@section('title')
    Edit AD Server Settings
@endsection

@section('settingsActive')
    class="active"
@endsection

@section('body')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Edit AD Server Settings</h3>
        </div>
        <div class="panel-body">
            <form class="form" accept-charset="utf-8" action="{{ url('/edit/settings') }}" method="post">
                <input type="hidden" name="id" value="{!! $settings->server !!}" />

                <div class="input-group">
                    <span class="input-group-addon">Server</span>
                    <input name="server" class="form-control" type="text" value="{!! $settings->server !!}" placeholder="Server Address" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">AD User</span>
                    <input name="user" class="form-control" type="text" value="{!! $settings->user !!}" placeholder="AD User" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">AD Reader User Password</span>
                    <input name="password" class="form-control" type="password" value="{!! $settings->pwd !!}" placeholder="AD Reader User Password." required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">Base Domain</span>
                    <input name="domain" class="form-control" type="text" value="{!! $settings->domain !!}" placeholder="AD Base Domain" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">User ID Attribute</span>
                    <input name="userid" class="form-control" type="text" value="{!! $settings->user_id !!}" placeholder="User ID attribute in AD Server for authentication" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">User Base Domain</span>
                    <input name="structdomain" class="form-control" type="text" value="{!! $settings->struct_domain !!}" placeholder="Base domain for User Authentication" required />
                </div>

                <br>

                <div class="text-center">
                    <button class="btn btn-default" type="button" onclick="history.back()"><i class="fa fa-times"></i> Cancel</button>
                    <button class="btn btn-warning" type="reset"><i class="fa fa-eraser"></i> Reset</button>
                    <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection
