@extends('layout')

@section('title')
    Edit AD Field {!! $field->name !!}
@endsection

@section('fieldsActive')
    class="active"
@endsection

@section('body')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Edit AD Field {!! $field->name !!}</h3>
        </div>
        <div class="panel-body">
            <form class="form" accept-charset="utf-8" action="{{ url('/edit/fields') }}" method="post">
                <input type="hidden" name="id" value="{!! $field->name !!}" />
                <input name="name" class="form-control" type="text" placeholder="AD field name" value="{!! $field->name !!}" required />
                <input name="alias" class="form-control" type="text" placeholder="Alias" value="{!! $field->alias !!}" required />

                <br>

                <div class="text-center">
                    <button class="btn btn-default" type="button" onclick="history.back()"><i class="fa fa-times"></i> Cancel</button>
                    <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i> Delete</button>
                    <button class="btn btn-warning" type="reset"><i class="fa fa-eraser"></i> Reset</button>
                    <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal modal-danger fade" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-center">Delete Field {!! $field->name !!}</h4>
                </div>
                <div class="modal-body text-center">
                    Are you sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                    <form action="{{ url('delete/fields') }}" method="post">
                        <input type="hidden" name="id" value="{!! $field->name !!}" />
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection