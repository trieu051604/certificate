@extends('adminlte::page')

@section('title', 'Cài đặt')
@section('css')

@stop

@section('content')
	<div class="card mt-4">
		<div class="card-header">
			<h3 class="card-title">
				Cài đặt
			</h3>
		</div>
		<div class="card-body">
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
			      action="{{ route('admin.setting.store') }}">
                    @csrf
                    {{-- <h3>API</h3>
                    <div class="form-group">
                        <label for="">URL</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="url" name="url"
                                    value="{{ setting()->getMeta('url') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Username</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="url" name="username"
                                    value="{{ setting()->getMeta('username') }}">
                        </div>
                    </div>
                    <div class="form-group mb-4">
                        <label for="">Password</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="url" name="password"
                                    value="">
                        </div>
                    </div> --}}
                    <h5>Cấu hình chia Lead</h5>
                    <div class="form-group">
                        <label for="">Số phút chia</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="minute_share" id="minute_share" value="{{ setting() ? setting()->getMeta('minute_share') : 5 }}">
                        </div>
                    </div>
					<div class="form-group">
                        <label for="">Số phút chuyển đổi lead</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="minute_convert" id="minute_convert" value="{{ setting() ? setting()->getMeta('minute_convert') : 5 }}">
                        </div>
                    </div>
					<div class="form-group">
                        <label for="">Số điểm để chuyển đổi</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="point" name="point"
                                    value="{{ setting() ? setting()->getMeta('point') : "" }}">
                        </div>
                    </div>
					<div class="form-group">
                        <label for="">Số lead chuyển đổi trên 1 điểm</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="number_lead_by_point" name="number_lead_by_point"
                                    value="{{ setting() ? setting()->getMeta('number_lead_by_point') : "" }}">
                        </div>
                    </div>
				<a href="{{URL::previous()}}" class="btn btn-warning">QUAY LẠI</a>
				<button type="submit" class="btn btn-success" style="width: 100px;margin-left: 10px">CẬP NHẬT</button>
			</form>
		</div>
	</div>
@stop

@section('js')
	<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
	<script>
		// $('#lfm').filemanager('file');
		// $('#lfm-1').filemanager('file');
		// $('#lfm-2').filemanager('file');
		// $('#lfm-3').filemanager('file');
		// $('#lfm-4').filemanager('file');
		// $('#lfm-5').filemanager('file');
		// $('#lfm-6').filemanager('file');
		// $('#lfm-7').filemanager('file');
		// $('#lfm-8').filemanager('file');
		// $('#lfm-9').filemanager('file');
		// $('#lfm-10').filemanager('file');
		// $('#lfm-11').filemanager('file');
	</script>
@stop