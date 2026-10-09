@extends('adminlte::page')

@section('title', 'Danh sách nhân viên')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Danh sách nhân viên
            </h3>
            <div class="card-tools">
                <a href="{{route('major-group.create')}}" type="button" class="btn btn-success"
                   style="max-width: 200px">Thêm nhóm ngành</a>
            </div>
        </div>
        <div class="card-body">
            @if(Session::has('message'))
                <div class="alert alert-{{Session::get('level')}}">
                    {{Session::get('message')}}
                </div>
            @endif
            @if (count($majorGroups) === 0)
                <p>Hiện không có Danh sách khách mời nào</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tên</th>
                            <th>Ngành</th>
                            <th class="text-nowrap">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($majorGroups as $majorGroup)
                            <tr>
                                <td class="text-center">{{$loop->iteration}}</td>
                                <td class="text-nowrap">{{ $majorGroup->name}}</td>
                                <td class="text-wrap">{{ implode(',', $majorGroup->majors->pluck('major')->toArray()) }}</td>
                                <td class="d-flex" style="gap: 5px">
                                    <a href="{{ route('major-group.edit', $majorGroup->id) }}" class="btn btn-success">Sửa</a>
                                    {{-- <form action="{{route('major-group.destroy',$majorGroup->id)}}"
                                        style="display: inline-block;"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Bạn có muốn bản ghi?')" class="btn btn-danger delete">Xóa</button>
                                    </form> --}}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $("#example").DataTable();
    });
</script>
@stop
