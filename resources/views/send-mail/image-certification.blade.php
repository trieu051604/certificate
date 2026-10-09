

{{-- @php
$infoSendMail = [
    'name' => 'Việt',
    'school' => 'HTK',
    'province' => 'ok',
    'course' => 'Ứng Dụng AI Trong Học Tập - Khám Phá Tương Lai Cùng Công Nghệ'
]
@endphp --}}
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @isset($certificateCss)
        <style>{!! $certificateCss !!}</style>
    @else
        <link rel="stylesheet" href="{{ ('css/submit-certification/generate-image.css?ver=' . verTime()) }}">
    @endisset
</head>
<body>
    <style>
        .ngaythang {
            position: absolute;
            top: 50px;
            right: 50px;
        }
        .ngaythang p {
            font-size: 25px;
            font-weight: bold;
        }
    </style>
    <div id="previewImg">
    </div>
    
        <div class="convertimg" >
        
        @php
            $urlImage = $certificateBackground ?? ($infoSendMail['certification'] ?? null)?->certificateBackground() ?? config('certificates.default_background', 'css/submit-certification/cer.jpg');
            $mbCourse = strlen($infoSendMail['course']) >= 60 ? "style=bottom:27.4%" : "";
            $currentDate = date('d/m/Y');
        @endphp
        
        <div id="html-content-holder" class="{{ ($certificateLayout ?? 'school') === 'organization' ? 'certificate-organization' : '' }}" style="background: url('{{$urlImage}}');background-size:100% 100%;height: 1448px;width: 2048px;">
    
            
            @if (($certificateLayout ?? 'school') === 'organization')
                @php
                    $courseText = $certificateCourseTitle ?? $infoSendMail['course'];
                    $length = mb_strlen($courseText, 'UTF-8');
                    $courseSize = $length <= 45 ? 44 : ($length <= 85 ? 36 : ($length <= 130 ? 30 : 22));
                    $nameLength = mb_strlen($infoSendMail['name'], 'UTF-8');
                    $nameSize = $nameLength <= 40 ? 64 : ($nameLength <= 80 ? 48 : ($nameLength <= 140 ? 36 : 24));
                    $unitLength = mb_strlen($certificateOrganization ?? '', 'UTF-8');
                    $unitSize = $unitLength <= 50 ? 32 : ($unitLength <= 100 ? 26 : ($unitLength <= 150 ? 22 : 16));
                @endphp
                <div class="certificate-name" style="font-size: {{ $nameSize }}px">{{ mb_strtoupper($infoSendMail['name'], 'UTF-8') }}</div>
                <div class="certificate-unit" style="font-size: {{ $unitSize }}px">{{ $certificateOrganization ?? '' }}</div>
                <div class="certificate-course" style="font-size: {{ $courseSize }}px">{{ $courseText }}</div>
            @else
            <div class="ngaythang">
                <p>{{ $currentDate }}</p>
            </div>
            <div class="ten">
            <p>{{ $infoSendMail['name'] }}</p>
            </div>
            <div class="truong">
                <p>{{ $infoSendMail['school'] }}</p>
            </div>
            <div class="tinh">
                <p>{{ $infoSendMail['province'] }}</p>
            </div>
            <div {{ $mbCourse }} class="khoahoc">
                <p>{{ $certificateCourseTitle ?? ($infoSendMail['title'] ?: $infoSendMail['course']) }}</p>
            </div>
            @endif
    
        </div>
    
    </div>
</body>
</html>

