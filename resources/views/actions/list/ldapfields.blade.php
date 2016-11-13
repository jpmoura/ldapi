@extends('layout')

@section('title')
    List of AD fields and their alias
@endsection

@section("fieldsActive")
    class="active"
@endsection

@section('extrasHeadImports')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.6/jqc-1.12.3/dt-1.10.12/r-2.1.0/datatables.min.css"/>
@endsection

@section('body')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">List of AD fields and their alias</h3>
        </div>
        <div class="panel-body">
            <div class="text-center">
                <table class="table table-responsive table-bordered table-hover table-stripped" id="fields">
                    <thead>
                    <tr>
                        <th>Field</th>
                        <th>Alias</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($fields as $field)
                        <tr>
                            <td>{!! $field->name !!}</td>
                            <td>{!! $field->alias !!}</td>
                            <td><a href="{{ url("/edit/fields/" . base64_encode($field->name)) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>AD Field</th>
                        <th>Alias</th>
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-center">
                <a href="{{ url('/add/fields') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add field</a>
            </div>
        </div>
    </div>
@endsection


@section('extrasBottomBodyImports')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.6/jqc-1.12.3/dt-1.10.12/r-2.1.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#fields').DataTable();
        } );
    </script>
@endsection