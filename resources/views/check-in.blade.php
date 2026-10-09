<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Check In</title>
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

<h2 class="text-center">Danh sách Check In</h2>

<form action="{{ route('admin.showCheckIn') }}" style="max-width: 50%; margin:0 auto; gap: 10px">
    <div class="form-group">
      <input type="text" name="phone" class="form-control" value="{{ request()->phone }}" placeholder="Phone">
    </div>
    <div class="form-group">
      <label for="#startDate">Ngày bắt đầu</label>
      <input type="date" name="startDate" id="startDate" class="form-control" value="{{ request()->startDate }}">
      <label for="#endDate">Ngày kết thúc</label>
      <input type="date" name="endDate" id="endDate" class="form-control" value="{{ request()->endDate }}">
    </div>
    <input type="submit" class="btn btn-success" value="Tìm">
    <a href="{{ route('admin.showCheckIn') }}" class="btn btn-success">Reset</a>
    <a href="{{ route('admin.exportCheckIn') . '?phone=' . request()->phone . '&startDate=' . request()->startDate . '&endDate=' . request()->endDate }}" class="btn btn-warning">Download</a>
</form>
<table style="margin-top: 20px">
  <caption style="padding-left: 20px">Số bản ghi ({{$count}})</caption>
  <tr>
    <th>Họ và Tên</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Tỉnh</th>
    <th>Trường</th>
    <th>Môn học</th>
    <th>Khoá học</th>
    <th>IP</th>
    <th>Ngày tạo</th>
    <th>Thời gian tạo</th>
  </tr>
  @foreach ($lists as $item)
  <tr>
    <td>{{ $item->fullname }}</td>
    <td>{{ $item->email }}</td>
    <td>{{ $item->phone }}</td>
    <td>{{ $item->province }}</td>
    <td>{{ $item->school }}</td>
    <td>{{ $item->mon_hoc }}</td>
    <td>{{ $item->khoa_hoc }}</td>
    <td>{{ $item->ip }}</td>
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

