@extends('layout')

@section('title')
    Add User
@endsection

@section('usersActive')
    class="active"
@endsection

@section('body')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Add User</h3>
        </div>
        <div class="panel-body">
            <form class="form" accept-charset="utf-8" action="{{ url('/add/user') }}" method="post">

                <div class="input-group">
                    <span class="input-group-addon">Username</span>
                    <input name="username" class="form-control" type="text" placeholder="Username" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">Password</span>
                    <input name="password" class="form-control" type="password" placeholder="Password" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">Description</span>
                    <input name="description" class="form-control" type="text" placeholder="User description" required />
                </div>

                <div class="input-group">
                    <span class="input-group-addon">Type</span>
                    <select name="role" class="form-control" required>
                        <option value="">Select a option</option>
                        <option value="user">Normal user</option>
                        <option value="admin">Administrator</option>
                    </select>
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
