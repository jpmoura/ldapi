@extends('layout')

@section('title')
    List of users
@endsection

@section('usersActive')
    class="active"
@endsection

@section('extrasHeadImports')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.6/jqc-1.12.3/dt-1.10.12/r-2.1.0/datatables.min.css"/>
@endsection

@section('body')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">List of users</h3>
        </div>
        <div class="panel-body">
            <div class="text-center">
                <table class="table table-responsive table-bordered table-hover table-stripped" id="users">
                    <thead>
                    <tr>
                        <th>Username</th>
                        <th>Description</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{!! $user->username !!}</td>
                            <td>{!! $user->description !!}</td>
                            <td>{!! $user->role !!}</td>
                            <td><a href="{{ url("/edit/user/" . base64_encode($user->username)) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Username</th>
                        <th>Description</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-center">
                <button class="btn btn-default" type="button" onclick="history.back()"><i class="fa fa-arrow-left"></i> Back</button>
                <a href="{{ url('/add/user') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add user</a>
            </div>
        </div>
    </div>
@endsection


@section('extrasBottomBodyImports')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.6/jqc-1.12.3/dt-1.10.12/r-2.1.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#users').DataTable();
        } );
    </script>
@endsection