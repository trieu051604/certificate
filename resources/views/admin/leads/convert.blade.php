@extends('adminlte::page')

@section('title', 'Lịch sử Chuyển đổi')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Lịch sử Chuyển đổi
            </h3>
            {{-- <div class="card-tools">
                <a href="{{route('lead.create')}}" type="button" class="btn btn-success"
                   style="max-width: 200px">Import</a>
            </div> --}}
        </div>
        <div class="card-body">
            @if(Session::has('message'))
                <div class="alert alert-{{Session::get('level')}}">
                    {{Session::get('message')}}
                </div>
            @endif
            @if (count($converts) === 0)
                <p>Chưa có chuyển đổi nào</p>
            @else
                <form action="{{ route('admin.lead.convert') }}" method="GET">
                    <div class="form-group">
                        <label for="">Sale nhận lead</label>
                        <select name="sale_id" id="" class="form-control">
                            <option value="">-- Chọn --</option>
                            @foreach ($sales as $sale)
                                <option value="{{ $sale->id }}" @if (request()->sale_id == $sale->id) selected @endif>{{ $sale->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Lọc</button>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            {{-- <th class="text-center">No</th> --}}
                            <th>Điểm chuyển đổi</th>
                            <th>Điểm được chuyển đổi</th>
                            <th>Điểm còn dư</th>
                            <th>Lead được chuyển</th>
                            <th>Chi tiết</th>
                            <th>Sale nhận lead</th>
                            <th>Ngày tạo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($converts as $index => $convert)
                            <tr class="@if ($index % 2 == 0) odd @else even @endif">
                                {{-- <td class="text-center">{{$loop->iteration}}</td> --}}
                                <td>{{ $convert->point_convert }}</td>
                                <td>{{ $convert->point_convert * $convert->leads }}</td>
                                <td>{{ $convert->point_residual }}</td>
                                <td>{{ $convert->leads }}</td>
                                <td><a href="{{ route('admin.history.convert-lead', $convert->id) }}" target="_blank">XEM</a></td>
                                <td>{{ $convert->user->email }}</td>
                                <td>{{ $convert->created_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $converts->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
@stop
