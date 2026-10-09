@extends('adminlte::page')

@section('title', 'Cập nhật khóa')
@section('css')
<link href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.css" rel="stylesheet">
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Cập nhật khóa
            </h3>
        </div>
        <div class="card-body">
            <p>Đường dẫn làm bài của khóa #{{ $certification->id }}:
                <a href="{{ route('certification.exam', $certification->slug) }}" target="_blank" rel="noopener">{{ route('certification.exam', $certification->slug) }}</a>
            </p>
            @if (!$certification->questions()->exists())
                <div class="alert alert-warning">
                    Khóa học chưa có câu hỏi nên trang làm bài chưa mở.
                    <a href="{{ route('admin.certification.import', $certification->id) }}">Import câu hỏi cho khóa này</a>.
                </div>
            @endif
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
                  action="{{route('certification.update', $certification->id)}}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="">Tên khóa</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="name" name="course" value="{{ old('course', $certification->course) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Điểm đậu</label>
                    <div class="input-group mb-3">
                        <input type="number" min="1" max="100" class="form-control" id="email" name="passing_score" value="{{ old('passing_score', $certification->passing_score) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Trạng thái</label>
                    <select name="status" id="" class="form-control">
                        <option value="ACTIVE" @selected(old('status', $certification->status) === 'ACTIVE')>ACTIVE</option>
                        <option value="UNACTIVE" @selected(old('status', $certification->status) === 'UNACTIVE')>UNACTIVE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Hạn làm bài hết ngày</label>
                    <input type="date" class="form-control" id="date_expire" name="date_expire"
                           value="{{ old('date_expire', $certification->date_expire ? date('Y-m-d', strtotime($certification->date_expire)) : '') }}">
                </div>


                @include('admin.certifications.certificate-select')
                @include('admin.certifications.registration-form-select')
                @include('admin.certifications.email-settings')
                <a href="{{URL::previous()}}" class="btn btn-warning">QUAY LẠI</a>
                <button type="submit" class="btn btn-success" style="width: 100px;margin-left: 10px">CẬP NHẬT</button>
            </form>
        </div>
    </div>

@stop


@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.js"></script>
<script>
    $('#editor').summernote({
        height: 400
    });
</script>
@include('admin.certifications.email-settings-script')

@stop
