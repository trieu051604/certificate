<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
    'default_background' => 'css/submit-certification/cer.jpg',

    'email_subject' => 'Chứng chỉ hoàn thành khóa học [course] – [name]',
    'email_content' => <<<'HTML'
<div style="font-family:Arial,sans-serif;color:#222;max-width:680px;margin:0 auto;line-height:1.6;">
<h2 style="color:#f37021;">CHỨNG CHỈ HOÀN THÀNH</h2>
<p>Kính gửi <strong>[name]</strong>,</p>
<p>Chúc mừng bạn đã hoàn thành khóa học <strong>[course]</strong>.</p>
<p>Thông tin chứng chỉ của bạn:</p>
<table style="width:100%;border-collapse:collapse;" cellpadding="10">
<tbody>
<tr><td style="border:1px solid #ddd;">Họ và tên</td><td style="border:1px solid #ddd;">[name]</td></tr>
<tr><td style="border:1px solid #ddd;">Đơn vị</td><td style="border:1px solid #ddd;">[unit]</td></tr>
<tr><td style="border:1px solid #ddd;">Khóa học</td><td style="border:1px solid #ddd;">[course]</td></tr>
<tr><td style="border:1px solid #ddd;">Mẫu chứng chỉ</td><td style="border:1px solid #ddd;">[cer]</td></tr>
<tr><td style="border:1px solid #ddd;">Ngày hoàn thành</td><td style="border:1px solid #ddd;">[completed_date]</td></tr>
</tbody>
</table>
<p>Chứng chỉ PDF được đính kèm trong email này. Vui lòng tải xuống và lưu lại để sử dụng.</p>
<p>Trân trọng,<br>Ban tổ chức</p>
</div>
HTML,

    'layouts' => [
        'school' => 'Họ tên – Trường – Tỉnh – Khóa học',
        'organization' => 'Họ tên – Đơn vị – Khóa học (mẫu TEST-AI)',
    ],

    'templates' => [
        [
            'name' => 'Chứng chỉ hoàn thành - Đại học FPT',
            'background' => 'css/submit-certification/cer.jpg',
        ],
        [
            'name' => 'Chứng nhận hoàn thành - Đại học FPT',
            'background' => 'css/submit-certification/cer1.jpg',
        ],
        [
            'name' => 'Chứng chỉ hoàn thành - Tỉnh đoàn Gia Lai',
            'background' => 'css/submit-certification/certificate-tinh-doan.png',
        ],
    ],
];
