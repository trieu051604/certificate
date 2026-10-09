<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống cấp và tải chứng chỉ học viên</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background: url('/images/bgai.jpg');
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container-custom {
            max-width: 900px;
            margin: 20px auto;
        }
        
        .search-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .search-card h2 {
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
        }
        
        .search-input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-input-group input {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .btn-search {
/*            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);*/
background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);


            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .student-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 25px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .student-info-card .info-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .student-info-card .info-value {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .courses-section h5 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .table-responsive,.courses-section {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background:white;
            padding: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table thead th {
            padding: 18px 15px;
            font-weight: 600;
            border: none;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e0e0e0;
            height: 60px;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9ff;
        }
        
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .badge-active {
                background: #00ae2a;
                color: white;
        }
        .badge-not-active {
            background: #d50000;
            color: white;
        }
        
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }
        
        .pagination .page-link {
            color: #667eea;
            border-color: #e0e0e0;
        }
        
        .pagination .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination .page-item.active .page-link {
            background: #667eea;
            border-color: #667eea;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9ff;
            border-radius: 12px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 16px;
        }

        .logotop {
    text-align: center;
}

.logotop img {
    width: 25%;
    margin-bottom: 20px;
}
 .btn-loading {
        pointer-events: none;
        opacity: 0.6;
    }
    
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
        margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
  .table thead {
    display: none;
  }

  .table, .table tbody, .table tr, .table td {
    display: block;
    width: 100%;
  }

  .table tr {
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 0.75rem;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  .table td {
    border: none;
    position: relative;
    padding-left: 50%;
    text-align: right;
  }

  .table td::before {
    content: attr(data-label);
    position: absolute;
    left: 0.75rem;
    width: 45%;
    padding-right: 10px;
    font-weight: 600;
    text-align: left;
    color: #555;
  }
}



    </style>
</head>
<body>
    <div class="container-custom">
        <!-- Search Card -->
        <div class="search-card">
            <div class="logotop">
                <img src="../images/dhfpt.png" alt="">
            </div>
            <h2 style="text-transform: uppercase;">Tra cứu chứng chỉ</h2>
            <p style="text-align: center;color: #0066b2;font-size: 19px;">Hệ thống tra cứu kết quả học tập của học viên</p>
            
            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-custom">
                    <strong>Lỗi!</strong> {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-warning alert-custom">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Search Form -->
            <form action="{{ route('students.search') }}" method="POST">
                @csrf
                <div class="search-input-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control form-control-lg" 
                        placeholder="Số Điện Thoại hoặc Email của học viên"
                        value="{{ old('search') }}"
                        required
                    >

                    <input type="text" name="website" style="display:none">
                </div>
                <button class="btn btn-search btn-lg text-white" type="submit">
                    Tra cứu
                </button>
            </form>
        </div>

        <!-- Student Info & Courses -->
        @if(isset($student))
            <!-- Student Information Card -->
      <!--       <div class="student-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Chào Quý Thầy/Cô</div>
                        <div class="info-value">{{ $student->fullname }}</div>
                        
                        <div class="info-label">Email</div>
                        
                    </div>
                   
                </div>
            </div> -->

            <!-- Courses Section -->
            <div class="courses-section">
                <h3 style="    margin-bottom: 30px;
    text-align: center;
    text-transform: uppercase;
    font-weight: bold;
    color: #0066b2;">Chào Quý học viên: {{ $student->fullname }}</h3>
                <h5>📚 Dưới đây là danh sách khóa học</h5>
                
                @if($students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Tên Khóa Học</th>
                                    <th width="20%">Tình trạng</th>
                                    <th width="20%">Ngày Hoàn thành</th>
                                    <th width="30%">Chứng chỉ</th>
                                </tr>
                            </thead>
                            <tbody>
                              <!--   @foreach($students as $stu)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $stu->certification->course ?? 'N/A' }}</strong></td>
                                      
                                        <td>
                                            <span class="badge badge-status {{  $stu->certification ? ($stu->isPass() ? 'badge-active' : 'badge-not-active') : 'nonedat' }} ">
                                               {{ $stu->certification ? ($stu->isPass() ? 'Đạt' : 'Chưa đạt') : 'Chưa có thông tin' }}
                                            </span>
                                        </td>
                                        <td>{{ $stu->created_at->format('d/m/Y') }}</td>
                                        <td> 

                                        

                                             @if($stu->certification && $stu->isPass())
                                                     <button class="btn btn-sm btn-primary btn-download" 
                                                             data-student-id="{{ $stu->id }}"
                                                             onclick="downloadCertification(this, event)">
                                                        📥 <span class="btn-text">Tải chứng chỉ</span>
                                                     </button>
                                                 @else
                                                     <span class="text-muted">Chưa có thông tin</span>
                                                @endif


                                        </td>
                                    </tr>
                                @endforeach -->

                                @foreach($students as $stu)
                                    <tr>
                                        <td data-label="#"> {{ $loop->iteration }} </td>

                                        <td >
                                            <strong>{{ $stu->certification->course ?? 'N/A' }}</strong>
                                        </td>

                                        <td data-label="Tình trạng">
                                            <span class="badge badge-status {{  $stu->certification ? ($stu->isPass() ? 'badge-active' : 'badge-not-active') : 'nonedat' }}">
                                                {{ $stu->certification ? ($stu->isPass() ? 'Đạt' : 'Chưa đạt') : 'Chưa có thông tin' }}
                                            </span>
                                        </td>

                                        <td data-label="Ngày Hoàn thành">
                                            {{ $stu->created_at->format('d/m/Y') }}
                                        </td>

                                        <td data-label="Chứng chỉ">
                                            @if($stu->certification && $stu->isPass())
                                                <button class="btn btn-sm btn-primary btn-download" 
                                                        data-student-id="{{ $stu->id }}"
                                                        onclick="downloadCertification(this, event)">
                                                    📥 <span class="btn-text">Tải chứng chỉ</span>
                                                </button>
                                            @else
                                                <span class="text-muted">Chưa có thông tin</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach


                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                   <!--  @if($students->hasPages())
                        <nav>
                            <ul class="pagination">
                                {{ $students->links() }}
                            </ul>
                        </nav>
                    @endif -->
                @else
                    <div class="empty-state">
                        <p>📭 Học viên này chưa đăng ký khóa học nào</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
function downloadCertification(button, event) {
    event.preventDefault();
    
    const studentId = button.getAttribute('data-student-id');
    const btnText = button.querySelector('.btn-text');
    const originalText = btnText.textContent;
    
    // Thêm loading state
    button.disabled = true;
    button.classList.add('btn-loading');
    btnText.innerHTML = '<span class="spinner-border spinner-border-sm"></span>Đang tải...';
    
    // Fetch PDF
    fetch(`/certification/download/${studentId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.blob();
    })
    .then(blob => {
        // Tạo link download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `chungchi_${new Date().getTime()}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Reset button
        button.disabled = false;
        button.classList.remove('btn-loading');
        btnText.innerHTML = '<span>Tải chứng chỉ</span>';
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.classList.remove('btn-loading');
        btnText.innerHTML = '❌ <span>Lỗi tải</span>';
        
        // Quay lại bình thường sau 2 giây
        setTimeout(() => {
            btnText.innerHTML = '<span>Tải chứng chỉ</span>';
        }, 2000);
    });
}
</script>


</body>
</html>