@extends('adminlte::page')

@section('title', 'Lịch sử Chia Lead')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Lịch sử Chia Lead
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
            @if (count($shares) === 0)
                <p>Chưa chia lead nào</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>UserId Thiếu</th>
                            <th>Trạng thái</th>
                            <th>Messange</th>
                            <th>Số lead được chia</th>
                            <th>Chi tiết</th>
                            <th>Nhóm ngành</th>
                            <th>Ngày tạo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($shares as $index => $share)
                            <tr class="@if ($index % 2 == 0) odd @else even @endif">
                                <td class="text-center">{{$loop->iteration}}</td>
                                <td>{{ $share->list_user_miss ? $share->list_user_miss : "Chia đủ" }}</td>
                                <td>
                                    <span class="badge badge-{{ changeColorByStatus($share->status) }}">{{ $share->status }}</span>
                                </td>
                                <td class="text-wrap">{{ $share->messange }}</td>
                                <td>{{ $share->leads->count() }}</td>
                                <td><a href="{{ route('admin.history.share-lead', $share->id) }}" target="_blank">XEM</a></td>
                                <td>{{ $share->major_group->name }}</td>
                                <td>{{ $share->created_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $shares->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
@stop
