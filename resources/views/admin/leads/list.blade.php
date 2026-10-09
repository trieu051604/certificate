@extends('adminlte::page')

@section('title', 'Danh sách lead')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Danh sách lead: (Tổng {{ $countLead }})
            </h3>
            {{-- <div class="card-tools">
                <a href="{{route('lead.create-item')}}" type="button" class="btn btn-success"
                   style="max-width: 200px">Create Lead</a>
                <a href="{{route('lead.create-item')}}" type="button" class="btn btn-success"
                   style="max-width: 200px">Import Lead</a>
            </div> --}}
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
                <form action="{{ route('lead.index') }}" method="GET">
                    <div class="d-flex" style="gap: 10px">
                        <div class="form-group" style="width: 49%">
                            <label for="">Email</label>
                            <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Nhập Email">
                        </div>
                        <div class="form-group" style="width: 49%">
                            <label for="">Phone</label>
                            <input type="text" name="phone" value="{{ request('phone') }}" class="form-control" placeholder="Nhập Phone">
                        </div>
                    </div>
                    <div class="d-flex" style="gap: 10px">
                        <div class="form-group" style="width: 32%">
                            <label for="">Loại</label>
                            <select name="type" id="" class="form-control">
                                <option value="">-- Chọn --</option>
                                <option value="DEFAULT" @if (request()->type === 'DEFAULT') selected @endif>Tự động</option>
                                <option value="PERFORMANCE" @if (request()->type === 'PERFORMANCE') selected @endif>Hiệu xuất</option>
                            </select>
                        </div>
                        <div class="form-group" style="width: 32%">
                            <label for="">File Import</label>
                            <select name="import_file" id="" class="form-control">
                                <option value="">-- Chọn --</option>
                                @foreach ($imports as $import)
                                    <option value="{{ $import->id }}" @if (request()->import_file === $import->id) selected @endif>{{ $import->url }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="width: 32%">
                            <label for="">Giao cho</label>
                            <select name="sale_id" id="" class="form-control">
                                <option value="">-- Chọn --</option>
                                @foreach ($sales as $sale)
                                    <option value="{{ $sale->id }}" @if (request()->sale_id == $sale->id) selected @endif>{{ $sale->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex" style="gap: 10px">
                        <div class="form-group" style="width: 32%">
                            <label for="">Trạng thái chia</label>
                            <select name="status" id="" class="form-control">
                                <option value="">-- Chọn --</option>
                                <option value="PENDING" @if (request()->status === 'PENDING') selected @endif>PENDING</option>
                                <option value="DONE" @if (request()->status === 'DONE') selected @endif>DONE</option>
                                <option value="SHORTAGE" @if (request()->status === 'SHORTAGE') selected @endif>SHORTAGE</option>
                                <option value="SHARE_CONVERT" @if (request()->status === 'SHARE_CONVERT') selected @endif>SHARE_CONVERT</option>
                            </select>
                        </div>
                        <div class="form-group" style="width: 32%">
                            <label for="">Trạng thái Import CRM</label>
                            <select name="status_insert" id="" class="form-control">
                                <option value="">-- Chọn --</option>
                                <option value="SUCCESS" @if (request()->status_insert === 'SUCCESS') selected @endif>SUCCESS</option>
                                <option value="PENDING" @if (request()->status_insert === 'PENDING') selected @endif>PENDING</option>
                                <option value="ERROR" @if (request()->status_insert === 'ERROR') selected @endif>ERROR</option>
                            </select>
                        </div>
                        <div class="form-group" style="width: 32%">
                            <label for="">Trạng thái Chuyển đổi</label>
                            <select name="status_convert" id="" class="form-control">
                                <option value="">-- Chọn --</option>
                                <option value="PENDING" @if (request()->status_convert === 'PENDING') selected @endif>PENDING</option>
                                <option value="CONVERTED" @if (request()->status_convert === 'CONVERTED') selected @endif>CONVERTED</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Lọc</button>
                    <a href="{{ route('lead.index') }}" class="btn btn-warning">Xoá lọc</a>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Họ và Tên</th>
                            <th>Giao cho</th>
                            <th>Loại chia</th>
                            <th>Trạng thái</th>
                            <th>Thông báo Insert</th>
                            <th>Số điện thoại</th>
                            <th>Ngành</th>
                            <th>Nhóm ngành</th>
                            <th>Email</th>
                            <th>Nguồn</th>
                            <th>Note</th>
                            <th>Import trùng</th>
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
                                    Chia trong hệ thống&nbsp;&emsp;&emsp;&nbsp;&nbsp;: <span class="badge badge-{{ changeColorByStatus($lead->status) }}">{{ $lead->status }}</span><br>
                                    Insert vào CRM&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;&nbsp;: <span class="badge badge-{{ changeColorByStatus($lead->status_insert) }}">{{ $lead->status_insert }}</span><br>
                                    Lead đã Convert trên CRM: <span class="badge badge-{{ changeColorByStatus($lead->status_convert) }}">{{ $lead->status_convert }}</span>
                                </td>
                                <td style="max-width: 140px; text-wrap: wrap">{{ $lead->messange_insert }}</td>
                                <td>{{ $lead->phone }}</td>
                                <td>{{ $lead->major ? $lead->major->major : "Không có ngành" }}</td>
                                <td>{{ $lead->major && $lead->major->major_group ? $lead->major->major_group->name : "Không có ngành" }}</td>
                                <td>{{ $lead->email }}</td>
                                <td>{{ $lead->source }}</td>
                                <td>{{ $lead->note }}</td>
                                <td>{{ $lead->count_duplicate }}</td>
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
