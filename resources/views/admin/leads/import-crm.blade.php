@extends('adminlte::page')

@section('title', 'Lịch sử Import CRM')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Lịch sử Import CRM
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
            @if (count($imports) === 0)
                <p>Chưa import nào</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Trạng thái</th>
                            <th>Messange</th>
                            <th>Lead Import</th>
                            <th>Lead Import Thành công</th>
                            <th>Lead Import Thất bại</th>
                            <th>Chi tiết</th>
                            <th>Ngày tạo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($imports as $index => $import)
                            <tr class="@if ($index % 2 == 0) odd @else even @endif">
                                <td class="text-center">{{$loop->iteration}}</td>
                                <td>
                                    <span class="badge badge-{{ changeColorByStatus($import->status) }}">{{ $import->status }}</span>
                                </td>
                                <td class="text-wrap">{{ $import->messange }}</td>
                                <td>{{ $import->leads->count() }}</td>
                                <td>{{ $import->leads->where('status_insert', 'SUCCESS')->count() }}</td>
                                <td>{{ $import->leads->where('status_insert', 'ERROR')->count() }}</td>
                                <td><a href="{{ route('admin.history.import-lead-crm', $import->id) }}" target="_blank">XEM</a></td>
                                <td>{{ $import->created_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $imports->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
@stop
