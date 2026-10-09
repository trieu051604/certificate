<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\HelperController;
use App\Http\Controllers\Api\CertificationTestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::post('test-zns', ())

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:50,1');
// dành riêng cho datalake

Route::middleware(['throttle:50,1'])->prefix('datalake')->group(function () {
    Route::post('/insert-lead', [LeadController::class, 'saveDatalake']);
    Route::post('/response-create', [LeadController::class, 'responseCreate']);
});



Route::middleware(['auth:sanctum', 'throttle:50,1', 'auth-api'])->group(function () {
    Route::post('/insert-lead', [LeadController::class, 'save']);
    Route::post('/insert-lead-to-crm', [LeadController::class, 'importLeadToCrm']);
    // Route::get('/get-schools/{province}', [LeadController::class, 'getSchools']);
    Route::post('/push-data-to-crm', [LeadController::class, 'pushDataToCrm']);
    Route::post('/push-data-to-new-page-register', [LeadController::class, 'pushDataToNewPageRegister']);
    // Route::post('get-lead-zns')
});

Route::middleware(['throttle:50,1', 'registraion'])->group(function () {
    Route::any('/CreateStudent', [RegistrationController::class, 'notAllowMethod']);
    Route::post('/CreateStudent', [RegistrationController::class, 'createStudent']);

    Route::any('/UpdateStudent', [RegistrationController::class, 'notAllowMethod']);
    Route::post('/UpdateStudent', [RegistrationController::class, 'updateStudent']);

    Route::any('/UpdateDocumentAndPayment', [RegistrationController::class, 'notAllowMethod'])->name('UpdateDocumentAndPayment');
    Route::post('/UpdateDocumentAndPayment', [RegistrationController::class, 'updateDocumentAndPayment'])->name('UpdateDocumentAndPayment');

    Route::any('/GetAllDataOfStudentByPhoneOrEmail', [RegistrationController::class, 'notAllowMethod']);
    Route::post('/GetAllDataOfStudentByPhoneOrEmail', [RegistrationController::class, 'getAllDataOfStudentByPhoneOrEmail'])->name('getAllDataOfStudentByPhoneOrEmail');

    Route::any('/UpdateStatus', [RegistrationController::class, 'notAllowMethod']);
    Route::post('/UpdateStatus', [RegistrationController::class, 'updateStatus']);

    Route::any('/sendOTP', [RegistrationController::class, 'notAllowMethod']);
    Route::post('/sendOTP', [RegistrationController::class, 'sendOTP'])->name('sendOTP');

    Route::any('/verifyOTP', [RegistrationController::class, 'notAllowMethod']);
    Route::post('/verifyOTP', [RegistrationController::class, 'verifyOTP'])->name('verifyOTP');

    

}); 

Route::post('get-wards', [HelperController::class, 'getWards']);
Route::post('get-districts', [HelperController::class, 'getDistricts']);
Route::post('get-schools', [HelperController::class, 'getSchools']);
Route::post('get-provinces', [HelperController::class, 'getProvinces']);
Route::post('get-schools/{province}', [HelperController::class, 'getSchoolsViaPro']);
Route::post('/checkCCCD/{cccd}', [RegistrationController::class, 'checkCCCD'])->name('checkCCCD');

// get school new
Route::middleware(['throttle:100,1'])->group(function () {
    Route::post('get-provinces-new', [HelperController::class, 'getProvincesNew']);
    Route::post('get-schools-new', [HelperController::class, 'getSchoolsNew']);
});

Route::post('checkin-online', [HelperController::class, 'checkIn'])->middleware('throttle:150,1');
Route::post('tra-cuu', [HelperController::class, 'traCuu']);

Route::prefix('certification')->group(function () {
    Route::get('courses', [\App\Http\Controllers\Api\PublicCertificationController::class, 'index']);
    Route::get('courses/{slug}', [\App\Http\Controllers\Api\PublicCertificationController::class, 'show']);
    Route::post('lookup/request-code', [\App\Http\Controllers\Api\PublicCertificationController::class, 'requestCode'])->middleware('throttle:5,1');
    Route::post('lookup/verify-code', [\App\Http\Controllers\Api\PublicCertificationController::class, 'verifyCode'])->middleware('throttle:10,1');
    Route::get('certificates/{studentId}/download', [\App\Http\Controllers\CertificationClientController::class, 'downloadCertification'])
        ->middleware(['signed', 'throttle:10,1'])->name('api.certification.download');
    // Route::get('get-province', [CertificationTestController::class, 'getProvinces']);submit-form-game
    // Route::get('get-school', [CertificationTestController::class, 'getSchools']);
    Route::post('submit-form', [CertificationTestController::class, 'submitFormCertification']);
    Route::post('submit-form-game', [CertificationTestController::class, 'submitFormCertificationGame']);
});

// Route::post('resend-failed-emails', [CertificationTestController::class, 'resendFailedEmails']);
