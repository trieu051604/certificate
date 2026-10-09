@extends('adminlte::page')

@section('title', 'Import Trường')
@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Import Trường
            </h3>
        </div>
        <div class="card-body">
            @if(Session::has('message'))
                <div class="alert alert-{{Session::get('level')}}">
                    {{Session::get('message')}}
                </div>
            @endif

            @if(count($errors)>0)
                <ol>
                    @foreach($errors->all() as $err)
                        <li class=" text-warning" style="margin-bottom: 5px">
                            {{$err}}
                        </li>
                    @endforeach
                </ol>
            @endif
            <form id="frmAddPromotion" enctype="multipart/form-data" method="post"
                  action="{{route('admin.certification-school.importStore')}}">
                @csrf
                <div class="form-group">
                    <label for="">Import Trường</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" data-input="thumbnail" id="lfm">Chọn File</span>
                        </div>
                        <input type="file" class="form-control" id="thumbnail" name="file"
                               value="">
                    </div>
                </div>
                <div class="form-group">
                    <select name="province_id" id="" class="form-control">
                        @foreach ($provinces as $province)
                            <option value="{{ $province->id }}">{{ $province->province }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-success">File mẫu: <a href="{{ url('excel/import-school-demo.xlsx') }}" class="text-success text-decoration-underline">Nhấn vào đây để xem</a></p>
                <a href="{{URL::previous()}}" class="btn btn-warning">QUAY LẠI</a>
                <button type="submit" class="btn btn-success" style="width: 100px;margin-left: 10px">IMPORT</button>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('file');
    </script>
@stop
