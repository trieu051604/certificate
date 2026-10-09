@extends('adminlte::page')

@section('title', 'Danh sách khóa học')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Danh sách khóa học
            </h3>
            <div class="card-tools">
                <a href="{{route('certification.create')}}" type="button" class="btn btn-success"
                   style="max-width: 200px">Thêm trang</a>
            </div>
        </div>
        <div class="card-body">
            @if(Session::has('message'))
                <div class="alert alert-{{Session::get('level')}}">
                    {{Session::get('message')}}
                </div>
            @endif
            @if (count($certifications) === 0)
                <p>Hiện không có</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover" id="example" style="background: #FFF">
                        <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tên</th>
                            <th>URL</th>
                            <th>Form đăng ký</th>
                            <th>Trạng thái</th>
                            <th>Hạn làm bài hết ngày</th>
                            <th>Số câu hỏi</th>
                            <th>Điểm đậu</th>
                            <th>Số người tham gia</th>
                            <th>Ngày tạo</th>
                            <th>Tác vụ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($certifications as $index => $certification)
                                <tr class="@if ($index % 2 == 0) odd @else even @endif">
                                    <td class="text-center">{{$loop->iteration}}</td>
                                    <td>{{ $certification->course }} <small class="text-muted">#{{ $certification->id }}</small></td>
                                    <td><a target="_blank" rel="noopener" href="{{ route('certification.exam', $certification->slug) }}">{{ route('certification.exam', $certification->slug) }}</a></td>
                                    <td>{{ $certification->registrationForm?->name ?? 'Form mặc định' }}</td>
                                   
                                    <td>
                                          @if($certification->status =='ACTIVE')
                                        <span class="badge badge-success"> {{ $certification->status }}</span>
                                         @else
                                         <span class="badge badge-danger"> {{ $certification->status }}</span>
                                         @endif
                                    </td>
                                        @if($certification->date_expire != '')
                                      <td>{{  \Carbon\Carbon::parse($certification->date_expire)->format('d-m-Y') }} </td>
                                      @else
                                       <td>{{  $certification->date_expire }} </td>
                                      @endif

                                    <td>{{ $certification->questions->count() }}</td>
                                    <td>{{ $certification->passing_score }}</td>
                                    <td>{{ $certification->persons->count() }}</td>
                                    <td>{{ $certification->created_at}}</td>
                                    <td class="d-flex" style="gap: 5px">
                                        <a href="{{ route('certification.edit', $certification->id) }}" class="btn btn-success">Sửa</a>
                                        <a href="{{ route('admin.certification.import', $certification->id) }}" class="btn btn-warning">Import câu hỏi</a>
                                        <a target="_blank" href="{{ route('certification.show', $certification->id) }}" class="btn btn-warning">Danh sách</a>
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
