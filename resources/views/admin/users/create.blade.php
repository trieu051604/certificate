@extends('adminlte::page')

@section('title', 'Thêm nhân viên')
@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Thêm nhân viên
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
                  action="{{route('user.store')}}">
                @csrf
                <div class="form-group">
                    <label for="">Họ và tên</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Email</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Mật khẩu (Mật khẩu ít nhất 8 kí tự, phái có ít nhất 1 kí tự đặc biệt)</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" style="width: auto;">
                            <label for="">Quyền</label>
                            <select name="type" id="" class="form-control" required>
                                <option value="MANAGER">Nhân viên</option>
                                <option value="ADMIN">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" style="width: auto;">
                            <label for="">Loại (Admin không cần chọn)</label>
                            <select name="campus_id" id="" class="form-control">
                                <option value="">-- Chon --</option>
                                @foreach($campus as $item)
                                    <option value="{{ $item->id }}">{{ $item->campus }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

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
