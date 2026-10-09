<?php

use App\Http\Controllers\Admin\CertificationTestController;
use App\Http\Controllers\Admin\CertificateTemplateController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MajorGroupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZNSController;
use App\Http\Controllers\Api\HelperController;
use App\Http\Controllers\HomeController;
use App\Imports\GetDatFromFileExcel;
use Maatwebsite\Excel\Facades\Excel;

Auth::routes();

// Route::get('/test', function() {

//     ini_set('memory_limit', '1024M');
//     $filePath = public_path('excel/ds-truong.xlsx');
//     $collection = Excel::toCollection(new GetDatFromFileExcel, $filePath);
//     dd($collection[0][2]);
// });

Route::get('/register', function () {
    return redirect()->route('login'); // Redirect đến trang login
})->middleware('auth');

Route::post('/register', function () {
    return redirect()->route('login'); // Redirect đến trang login
})->middleware('auth');

// Route::get('/test', function() {
//     $crm = new CRM();
//     $data = $crm->getDropdownList(params: ['schools', 'provinces']);
//     $provinces = collect($data->ListsInfo->provinces)->filter(function($province) {
//         return in_array($province->label, ['Đắk Lắk', 'Đắk Nông', 'Lâm Đồng', 'Bình Phước', 'Ninh Thuận']);
//     });

//     foreach ($provinces as $province) {
//         $provinceId = DB::table('province_certification')->insertGetId([
//             'province' => $province->label
//         ]);

//         $schools = collect($data->ListsInfo->schools)->filter(function($school) use ($province) {
//             return $school->in_province == $province->key;
//         });

//         foreach ($schools as $school) {
//             \DB::table('school_certification')->insert([
//                 ['province_id' => $provinceId, 'school' => $school->label],
//             ]);
//         }
//     }

//     // return view('send-mail.image-certification');
//     dd(true);
// });

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});
Route::get('/', [LeadController::class, 'create'])->middleware(['auth', 'superAdmin']);
Route::middleware(['auth', 'superAdmin'])->prefix('admin')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('admin');
    Route::get('lead/import', [LeadController::class, 'import'])->name('admin.lead.import');
    Route::get('lead/share', [LeadController::class, 'share'])->name('admin.lead.share');
    Route::get('lead/convert', [LeadController::class, 'convert'])->name('admin.lead.convert');
    Route::get('lead/import-crm', [LeadController::class, 'importCrm'])->name('admin.lead.import-crm');
    Route::get('lead/create-item', [LeadController::class, 'createItem'])->name('lead.create-item');

    Route::resource('lead', LeadController::class);
    Route::resource('user', UserController::class);
    Route::resource('major-group', MajorGroupController::class);

    Route::get('setting', [SettingController::class, 'index'])->name('admin.setting.index');
    Route::post('setting/store', [SettingController::class, 'store'])->name('admin.setting.store');

    // History
    Route::prefix('history')->group(function () {
        Route::get('share-lead/{id}', [HistoryController::class, 'shareLead'])->name('admin.history.share-lead');
        Route::get('import-lead-crm/{id}', [HistoryController::class, 'importLeadCrm'])->name('admin.history.import-lead-crm');
        Route::get('convert-lead/{id}', [HistoryController::class, 'convertLead'])->name('admin.history.convert-lead');
    });

    // certification
    Route::resource('registration-forms', \App\Http\Controllers\Admin\RegistrationFormController::class)->except('show');
    Route::get('certificate-templates', [CertificateTemplateController::class, 'index'])->name('admin.certificate-templates.index');
    Route::get('certificate-templates/create', [CertificateTemplateController::class, 'create'])->name('admin.certificate-templates.create');
    Route::post('certificate-templates', [CertificateTemplateController::class, 'store'])->name('admin.certificate-templates.store');
    Route::patch('certificate-templates/{certificateTemplate}/layout', [CertificateTemplateController::class, 'updateLayout'])->name('admin.certificate-templates.layout');
    Route::delete('certificate-templates/{certificateTemplate}', [CertificateTemplateController::class, 'destroy'])->name('admin.certificate-templates.destroy');
    Route::resource('certification', CertificationTestController::class);
    Route::get('list-student-game/{id}', [CertificationTestController::class, 'showGame'])->name('certification.showGame');
    Route::get('certification/export-data/{id}', [CertificationTestController::class, 'exportDataPerson'])->name('admin.certification.export');
    Route::get('certification/import/{id}', [CertificationTestController::class, 'import'])->name('admin.certification.import');
    Route::post('certification/import-store', [CertificationTestController::class, 'importStore'])->name('admin.certification.importStore');
    Route::get('certification-school/import', [CertificationTestController::class, 'schoolImport'])->name('admin.certification-school.import');
    Route::post('certification-school/import-store', [CertificationTestController::class, 'schoolImportStore'])->name('admin.certification-school.importStore');

    Route::get('check-in', [HelperController::class, 'showCheckIn'])->name('admin.showCheckIn');
    Route::get('export-check-in', [HelperController::class, 'exportCheckIn'])->name('admin.exportCheckIn');

});
// Route::middleware(['auth', 'checkin'])->prefix('admin')->group(function () {
//     Route::get('check-in', [HelperController::class, 'showCheckIn'])->name('admin.showCheckIn');
//     Route::get('export-check-in', [HelperController::class, 'exportCheckIn'])->name('admin.exportCheckIn');
// });

Route::middleware(['auth', 'znsAdmin'])->prefix('admin')->group(function () {
    Route::get('zns/import', [ZNSController::class, 'import'])->name('zns.import');
    Route::post('zns/import', [ZNSController::class, 'importStore'])->name('zns.import.store');
});

?>
