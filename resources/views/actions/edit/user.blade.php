@extends('layout')

@section('title')
    Edit User {!! $user->username !!}
@endsection

@section('usersActive')
    class="active"
@endsection

@section('body')
    <div class="panel panel-primary">
    	  <div class="panel-heading">
    			<h3 class="panel-title">Edit {!! $user->username !!}</h3>
    	  </div>
    	  <div class="panel-body">
              <form class="form" accept-charset="utf-8" action="{{ url('/edit/user') }}" method="post">
                  <input type="hidden" name="id" value="{!! $user->username !!}" />

                  <div class="input-group">
                      <span class="input-group-addon">Username</span>
                      <input name="username" class="form-control" type="text" value="{!! $user->username !!}" placeholder="Username" required/>
                  </div>

                  <div class="input-group">
                      <span class="input-group-addon">Password</span>
                      <input name="password" class="form-control" type="password" placeholder="New Password. Fill in only if you want to change the current password." />
                  </div>

                  <div class="input-group">
                      <span class="input-group-addon">Description</span>
                      <input name="description" class="form-control" type="text" value="{!! $user->description !!}" placeholder="User description" required/>
                  </div>

                  <div class="input-group">
                      <span class="input-group-addon">Type</span>
                      <select name="role" class="form-control" required>
                          <option value="admin" @if($user->role == "admin") selected @endif>Administrator</option>
                          <option value="user" @if($user->role == "user") selected @endif>Normal User</option>
                      </select>
                  </div>

                  <br>

                  <div class="text-center">
                      <button class="btn btn-default" type="button" onclick="history.back()"><i class="fa fa-times"></i> Cancel</button>
                      <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</button>
                      <button class="btn btn-warning" type="reset"><i class="fa fa-eraser"></i> Reset</button>
                      <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Submit</button>
                  </div>

                  <br>
              </form>
    	  </div>
    </div>

    <div class="modal modal-danger fade" id="deleteModal">
    	<div class="modal-dialog">
    		<div class="modal-content">
    			<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    				<h4 class="modal-title text-center">Delete User {!! $user->username !!}</h4>
    			</div>
                <div class="modal-body text-center">
                    Are you sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                    <form action="{{ url('delete/user') }}" method="post">
                        <input type="hidden" name="id" value="{!! $user->username !!}" />
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-trash"></i> Delete</button>
                    </form>
                </div>
    		</div><!-- /.modal-content -->
    	</div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
