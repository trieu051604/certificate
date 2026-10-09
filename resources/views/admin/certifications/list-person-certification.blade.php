<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Người thi khóa {{$certification->course}}</title>
    <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>

<h2 class="text-center">Danh sách {{$certification->course}}</h2>

<form action="{{ route('certification.show', $certification->id) }}" style="max-width: 50%; margin:0 auto; gap: 10px">
    <div class="form-group">
      <input type="text" name="phone" class="form-control" value="{{ request()->phone }}" placeholder="Phone">
    </div>
    <div class="form-group">
        <input type="text" name="email" class="form-control" value="{{ request()->email }}" placeholder="Email">
        <br>
        <label >Bạn là</label>
        <select id="you_is" class="form-control" name="you_is">
          <option value="" selected>Chọn</option>
                                <option value="giao_vien"  >Giáo viên</option>
                                <option value="phu_huynh" >Phụ huynh</option>
                                <option value="hs_12" >Học sinh lớp 12</option>
                                <option value="hs_11" >Học sinh lớp 11</option>
                                <option value="hs_10" >Học sinh lớp 10</option>
                                <option value="khac" >Khác</option>

        </select>


      </div>
    <div class="form-group">
      <label for="#startDate">Ngày bắt đầu</label>
      <input type="date" name="startDate" id="startDate" class="form-control" value="{{ request()->startDate }}">
      <label for="#endDate">Ngày kết thúc</label>
      <input type="date" name="endDate" id="endDate" class="form-control" value="{{ request()->endDate }}">
    </div>
    <input type="submit" class="btn btn-success" value="Tìm">
    <a href="{{ route('certification.show', $certification->id) }}" class="btn btn-success">Reset</a>
    <a href="{{ route('admin.certification.export', $certification->id) . '?phone=' . request()->phone . '&startDate=' . request()->startDate . '&endDate=' . request()->endDate }}" class="btn btn-warning">Download</a>
</form>
<table style="margin-top: 20px">
  <caption style="padding-left: 20px">Số bản ghi ({{$count}})</caption>
  <tr>
    <th>Họ và Tên</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Tỉnh</th>
    <th>Trường(tự nhập)</th>
    <th>Bạn là</th>
    <th>Thông tin đăng ký</th>
    <th>Điểm</th>
    <th>Kết quả</th>
    <!-- <th>Tình trạng gửi mail</th> -->
    <th>Ngày tạo</th>
    <th>Thời gian tạo</th>
  </tr>
  @foreach ($lists as $item)

  @php
    $mang_banla = ['giao_vien' => "Giáo viên", 'phu_huynh' => "Phụ Huynh", 'hs_10',"HS lớp 10", 'hs_11'=>"HS lớp 11", 'hs_12' => "HS lớp 12", 'khac' => "Khác"]
  @endphp
  <tr>
    <td>{{ $item->fullname }}</td>
    <td>{{ $item->email }}</td>
    <td>{{ $item->phone }}</td>
    <td>{{ $item->province?->province ?? '' }}</td>
    <td>{{ $item->name_school }}</td>
     <td>{{ $mang_banla[$item->you_is] ?? $item->you_is }}</td>
    <td>@if($item->registration_data)<details><summary>Xem thông tin</summary>@foreach($item->registration_data as $field)<div><strong>{{ $field['label'] }}:</strong> {{ $field['value'] }}</div>@endforeach</details>@endif</td>
    <td>{{ $item->point }}</td>
    <td>{{ $item->isPass() ? "Đậu" : "Không đậu" }}</td>
    <!-- <td>{{ $item->status_send_mail == 200 ? "Đã gửi" : "Chưa gửi" }}</td> -->
    @php 
        $time = explode(' ', $item->created_at);
    @endphp
    <td>{{ $time[0] }}</td>
    <td>{{ $time[1] }}</td>
  </tr>
  @endforeach
</table>
{!! $lists->withQueryString()->links() !!}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
</body>
</html>

