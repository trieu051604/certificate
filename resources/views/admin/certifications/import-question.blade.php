@extends('adminlte::page')

@section('title', 'Import câu hỏi')
@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Import câu hỏi – {{ $certification->course }} #{{ $certification->id }}
            </h3>
        </div>
        <div class="card-body">
            <p><a href="{{ route('certification.exam', $certification->slug) }}" target="_blank" rel="noopener">Mở trang làm bài của khóa #{{ $certification->id }}</a></p>
            @php($locked = $certification->persons_count + $certification->person_game_count > 0)
            @if ($locked)
                <div class="alert alert-warning">
                    Khóa học đã có {{ $certification->persons_count + $certification->person_game_count }} bài nộp nên không thể thay bộ câu hỏi.
                    @if ($certification->type === 'CERTIFICATION')
                        <a href="{{ route('certification.create', ['copy_from' => $certification->id]) }}" class="btn btn-primary ml-2">Tạo bản sao để import câu hỏi mới</a>
                    @endif
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
            <form id="frmAddPromotion" enctype="multipart/form-data" method="post"
                  action="{{route('admin.certification.importStore')}}">
                @csrf
                <div class="form-group">
                    <input type="hidden" name="certification_id" value="{{ $certification->id }}">
                    <label for="">Import Question</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" data-input="thumbnail" id="lfm">Chọn File</span>
                        </div>
                        <input type="file" class="form-control" id="thumbnail" name="file" accept=".xlsx,.xls,.csv" required @disabled($locked)>
                    </div>
                </div>
                <p class="text-success">File mẫu: <a href="{{ url('excel/import-question-demo.xlsx') }}" class="text-success text-decoration-underline">Nhấn vào đây để xem</a></p>
                <p class="text-danger">Nếu đã import câu hỏi trước đó khi import file tiếp theo thì các câu hỏi cũ sẽ bị xoá và thay bằng các câu hỏi mới trong file vừa import vào</p>
                <p>Giữ dòng tiêu đề và 6 cột: Câu hỏi, A, B, C, D, Đáp án đúng. Cột Đáp án đúng nhập A/B/C/D.</p>
                <a href="{{URL::previous()}}" class="btn btn-warning">QUAY LẠI</a>
                <button type="submit" class="btn btn-success" style="width: 100px;margin-left: 10px" @disabled($locked)>IMPORT</button>
            </form>
            <hr>
            <h4>Câu hỏi đang lưu trong khóa: {{ $certification->questions->count() }} câu</h4>
            @forelse ($certification->questions as $question)
                <div class="border rounded p-3 mb-2">
                    <strong>{{ $loop->iteration }}. {{ $question->question }}</strong>
                    <ol type="A" class="mt-2 mb-0">
                        @foreach ($question->answers as $answer)
                            <li>{{ $answer->answer }} @if ((string) $question->answer_correct_id === (string) $answer->id)<span class="badge badge-success">Đáp án đúng</span>@endif</li>
                        @endforeach
                    </ol>
                </div>
            @empty
                <p class="text-muted">Chưa có câu hỏi. Chọn file Excel và bấm IMPORT.</p>
            @endforelse
        </div>
    </div>
@stop

@section('js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('file');
    </script>
@stop
