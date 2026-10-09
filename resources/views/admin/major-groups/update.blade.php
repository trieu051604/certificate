@extends('adminlte::page')

@section('title', 'Đổi mật khẩu')
@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Đổi mật khẩu
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

            <form id="frmAddPromotion" method="post"
                  action="{{route('major-group.update', $major_group->id)}}">
                @csrf
                @method('PUT')
                <label for="" class="mb-4 text-uppercase">Chọn ngành - nhóm ngành {{ $major_group->name }}</label>
                <div class="form-group">
                    <label for="">Name</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="name" name="name" value="{{ $major_group->name }}" required>
                    </div>
                </div>
                @foreach ($majors as $major)
                    <div class="form-group d-flex align-items-end mb-1" style="gap: 10px">
                        <input type="checkbox" class="form-control d-block" style="width: 20px" id="major{{$major->id}}" value="{{ $major->id }}" name="majorId[]" @if(in_array($major->id, $major_group->majors->pluck('id')->toArray())) checked @endif>
                        <label for="major{{$major->id}}">{{ $major->major }}</label>
                    </div>
                @endforeach
                <a href="{{URL::previous()}}" class="btn btn-warning">QUAY LẠI</a>
                <button type="submit" class="btn btn-success" style="width: 100px;margin-left: 10px">CẬP NHẬT</button>
            </form>
        </div>
    </div>
@stop

@section('js')
    {{-- <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('file');
    </script>

    <script>
        var options = {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
        };
        CKEDITOR.replace('content', options);
    </script> --}}
@stop
