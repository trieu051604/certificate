@extends('adminlte::page')

@section('title', 'Danh sách nhân viên')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Danh sách nhân viên
            </h3>
            {{-- <div class="card-tools">
                <a href="{{route('user.create')}}" type="button" class="btn btn-success"
                   style="max-width: 200px">Thêm nhân viên</a>
            </div> --}}
        </div>
        <div class="card-body">
            @if(Session::has('message'))
                <div class="alert alert-{{Session::get('level')}}">
                    {{Session::get('message')}}
                </div>
            @endif
            @if (count($users) === 0)
                <p>Hiện không có Danh sách khách mời nào</p>
            @else
                <form action="{{ route('user.index') }}" method="GET">
                    <div class="form-group">
                        <label for="start_date">Thời gian bắt đầu - kết thúc</label>
                        <div class="d-flex" style="gap:10px">
                            <input type="date" name="start_date" id="start_date" value="{{ $stringTimeStart }}" class="form-control">
                            <input type="date" name="start_end" id="start_end" value="{{ $stringTimeEnd }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Sắp xếp theo</label>
                        <div class="d-flex" style="gap:10px">
                            <select name="key" id="" class="form-control">
                                <option value="">-- Chọn trường lọc ---</option>
                                <option value="number_lead" @if (request('key') === 'number_lead') selected @endif>Số Lead được chia</option>
                                <option value="number_lead_convert" @if (request('key') === 'number_lead_convert') selected @endif>Số Lead đã chuyển đổi</option>
                            </select>
                            <select name="type" id="" class="form-control">
                                <option value="">-- Kiểu lọc ---</option>
                                <option value="DESC" @if (request('type') === 'DESC') selected @endif>Từ cao đến thấp</option>
                                <option value="ASC" @if (request('type') === 'ASC') selected @endif>Từ thấp đến cao</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Lọc</button>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tên</th>
                            <th>Điểm hiện có</th>
                            <th>Số lead được chia</th>
                            <th>Số lead đã chuyển đổi</th>
                            <th>Email</th>
                            <th>Nhóm ngành</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $index => $user)
                                <tr class="@if ($index % 2 == 0) odd @else even @endif">
                                    <td class="text-center">{{$loop->iteration}}</td>
                                    <td>{{ $user->name}}</td>
                                    <td>{{ $user->point}}</td>
                                    <td>{{ $user->number_lead }}</td>
                                    <td>{{ $user->number_lead_convert }}</td>
                                    <td>{{ $user->email}}</td>
                                    <td>{{ implode(',', $user->major_group->pluck('name')->toArray()) }}</td>
                                    <td class="d-flex" style="gap: 5px">
                                        <a href="{{ route('user.edit', $user->id) }}" class="btn btn-success">Sửa</a>
                                        <form action="{{route('user.destroy',$user->id)}}"
                                            style="display: inline-block;"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Bạn có muốn xóa khách mời?')" class="btn btn-danger delete">Xóa</button>
                                        </form>
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
@stop
