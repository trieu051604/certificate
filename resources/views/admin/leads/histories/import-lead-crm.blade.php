@extends('adminlte::page')

@section('title', 'Danh sách lead')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Danh sách lead
            </h3>
        </div>
        <div class="card-body">
            @if(Session::has('message'))
                <div class="alert alert-{{Session::get('level')}}">
                    {{Session::get('message')}}
                </div>
            @endif
            @if (count($leads) === 0)
                <p>Hiện không có lead nào</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Họ và Tên</th>
                            <th>Giao cho</th>
                            <th>Loại Chia</th>
                            <th>Trạng thái</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Ngành</th>
                            <th>Nhóm ngành</th>
                            <th>Thêm bởi</th>
                            <th>Ngày tạo</th>
                            {{-- <th>Thao tác</th> --}}
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($leads as $index => $lead)
                            <tr class="@if ($index % 2 == 0) odd @else even @endif">
                                <td class="text-center">{{$loop->iteration}}</td>
                                <td>{{ $lead->fullname }}</td>
                                <td>{{ $lead->user ? $lead->user->email : "Chưa giao" }}</td>
                                <td>
                                    <span class="badge badge-{{ changeColorByStatus($lead->type) }}">{{ $lead->type === 'DEFAULT' ? 'TỰ ĐỘNG' : 'HIỆU XUẤT' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ changeColorByStatus($lead->status_insert) }}">{{ $lead->status_insert }}</span>
                                </td>
                                <td>{{ $lead->phone }}</td>
                                <td>{{ $lead->email }}</td>
                                <td>{{ $lead->major ? $lead->major->major : "Không có ngành" }}</td>
                                <td>{{ $lead->major && $lead->major->major_group ? $lead->major->major_group->name : "Không có ngành" }}</td>
                                <td>{{ $lead->import ? $lead->import->user->name : "API Import" }}</td>
                                <td>{{ $lead->created_at }}</td>
                                {{-- <td>
                                    <form action="{{route('user.destroy',$lead->id)}}"
                                        style="display: inline-block;"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Bạn có muốn xóa khách mời?')" class="btn btn-danger delete">Xóa</button>
                                    </form>
                                </td> --}}
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $leads->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
@stop
