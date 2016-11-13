@extends('layout')

@section('title')
    AD Server Settings
@endsection

@section('settingsActive')
    class="active"
@endsection

@section('body')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">AD Server Settings</h3>
        </div>
        <div class="panel-body">
            <div class="text-center">
                <table class="table table-responsive table-bordered table-hover table-stripped">
                    <thead>
                    <tr>
                        <th>Server address</th>
                        <th>User</th>
                        <th>Domain</th>
                        <th>Password</th>
                        <th>User ID</th>
                        <th>User Domain</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{!! $settings->server !!}</td>
                            <td>{!! $settings->user !!}</td>
                            <td>{!! $settings->domain !!}</td>
                            <td>Hidden</td>
                            <td>{!! $settings->user_id !!}</td>
                            <td>{!! $settings->struct_domain !!}</td>
                            <td><a href="{{ url("/edit/settings/" . base64_encode($settings->server)) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a></td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Server address</th>
                        <th>User</th>
                        <th>Domain</th>
                        <th>Password</th>
                        <th>User ID</th>
                        <th>User Domain</th>
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>

                <div class="text-center">
                    <button class="btn btn-default" type="button" onclick="history.back()"><i class="fa fa-arrow-left"></i> Back</button>
                </div>
            </div>
        </div>
    </div>
@endsection