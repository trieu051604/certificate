{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="container-full" id="report">
        <h3>Tự động</h3>
        <div class="row">

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h5>
                            Import: <span class="font-weight-bold text-dark">{{ $countPendingShareAutoFromImport }}</span><br>
                            Online: <span class="font-weight-bold text-dark">{{ $countPendingShareAutoFromAPI }}</span>
                        </h5>
                        <p>Tổng Lead</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=DEFAULT' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ $countPendingShareAuto }}</h3>
                        <p>Lead chưa chia</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=DEFAULT&status=PENDING' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $countPendingImportAuto }}</h3>
                        <p>Lead Chưa import vào CRM</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=DEFAULT&status_insert=PENDING' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>


            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ $countPendingConvertAuto }}</h3>
                        <p>Lead chưa chuyển đổi</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=DEFAULT&status_convert=PENDING' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>


        </div>

        <h3>Hiệu xuất</h3>
        <div class="row">

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h5>
                            Import: <span class="font-weight-bold text-dark">{{ $countPendingSharePerformanceFromImport }}</span><br>
                            Online: <span class="font-weight-bold text-dark">{{ $countPendingSharePerformanceFromAPI }}</span>
                        </h5>
                        <p>Tổng Lead</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=PERFORMANCE' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ $countPendingSharePerformance }}</h3>
                        <p>Lead chưa chia</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=PERFORMANCE&status=PENDING' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $countPendingImportPerformance }}</h3>
                        <p>Lead Chưa import vào CRM</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=PERFORMANCE&status_insert=PENDING' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>


            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ $countPendingConvertPerformance }}</h3>
                        <p>Lead chưa chuyển đổi</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{ route('lead.index') . '?type=PERFORMANCE&status_convert=PENDING' }}" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>


        </div>

        {{-- <h3>Từ Online</h3>
        <div class="row">

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $leadApi->count() }}</h3>
                        <p>Tổng Lead</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ $countPendingShareApi }}</h3>
                        <p>Lead chưa chia</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">

                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $countPendingImportApi }}</h3>
                        <p>Lead Chưa import vào CRM</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>


            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ $countPendingConvertApi }}</h3>
                        <p>Lead chưa chuyển đổi</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Xem thêm <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>


        </div> --}}

        {{-- <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Báo cáo </h3>
                    </div>
                    <div class="box-content">
                        <canvas id="line-chart" width="800" height="450"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Báo cáo </h3>
                    </div>
                    <div class="box-content">
                        <canvas id="line-chart-2" width="800" height="450"></canvas>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@stop

@section('js')

@stop
