<?php
use App\Http\Controllers\CertificationClientController;
use App\Mail\SendCertificationMail;
use App\Models\PersonCertificationTest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
// use PDF;
// fptuni-certificatie
Route::get("/certification/{course}", [CertificationClientController::class, 'show'])->name('certification.exam');
Route::get('/certification/download/{studentId}', [CertificationClientController::class, 'downloadCertification'])
    ->middleware('throttle:10,1')->name('certification.download');
Route::get('/chung-chi/tra-cuu', [CertificationClientController::class, 'index']);
Route::post('/chung-chi/tra-cuu', [CertificationClientController::class, 'search']);
Route::domain('certificate-uni.io.vn')->group(function () {
    Route::get("/", function() {
        return abort(404);
    });
    Route::get("/{course}", [CertificationClientController::class, 'show']);

    Route::get('/chung-chi/tra-cuu/', [CertificationClientController::class, 'index'])->name('students.search.index');
    Route::post('/chung-chi/tra-cuu/', [CertificationClientController::class, 'search'])->middleware('throttle:student-search')->name('students.search');




});
// Route::get('/test', function() {
//     $person = PersonCertificationTest::find(18);
//     dd($person->school);
// });
