<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\Api\RegistrationService;
use App\Http\Requests\RegistrationRequest;

class RegistrationController extends Controller
{
    private $registrationService;
    private $requireFields;
    private $acceptFields;

    function __construct()
    {
        $this->registrationService = new RegistrationService();
        $fields = config('flelds');
        $this->requireFields = $fields['require_flelds'];
        $this->acceptFields = $fields['accept_flelds'];
    }

    function notAllowMethod()
    {
        return jsonResponse("error", "99", 'Phương thức không hợp lệ');
    }

    function createStudent(RegistrationRequest $request)
    {
        // echo env('ASSIGNED_ID');
        // return;
        // check fields required
        $checkExistRequired = checkFieldRequired(
            collect($request->all())->toArray(),
            $this->requireFields['createStudent']
        );

        if (!$checkExistRequired['status']) return jsonResponse("ok", "99", 'Thiêu thông số ' . $checkExistRequired['field']);

        // check fields required accept
        $checkFieldAccept = checkFieldAccept(
            collect($request->all())->toArray(),
            $this->acceptFields['createStudent']
        );

        if (!$checkFieldAccept['status']) return jsonResponse("ok", "99", 'Thông số không cho phép ' . $checkFieldAccept['field']);

        $contact = $this->registrationService->checkExistStudentByPhone($request->IdNumber);
        if ($contact) return jsonResponse("ok", "01", 'Đã có student', 1, [
            'ID' => $contact['contactid'],
            'Email' => $contact['email'],
            'Phone' => $contact['mobile'],
            'FullName' => $contact['lastname'],
            'StatusCode' => $contact['contacts_status']
        ]);

        $newContact = $this->registrationService->saveAllData($request);
        if (!$newContact) return jsonResponse("ok", "99", 'Tạo student không thành công');

        return jsonResponse("ok", "00", 'Nhập mới student thành công', 1, $newContact);
    }

    function updateStudent(RegistrationRequest $request)
    {
         // check fields required accept
         $checkFieldAccept = checkFieldAccept(
            collect($request->all())->toArray(),
            $this->acceptFields['updateStudent']
        );

        if (!$checkFieldAccept['status']) return jsonResponse("ok", "99", 'Thông số không cho phép ' . $checkFieldAccept['field']);

        if (!$request->ID) return jsonResponse("ok", "03", 'ID không tồn tại');

        $studentExist = $this->registrationService->checkExistStudentById($request->ID);
        if (!$studentExist) return jsonResponse("ok", "99", 'student không tồn tại');

        $updateContact = $this->registrationService->updateStudent($request);
        // return jsonResponse("ok", "00", 'updateStudent student thành công', 1, $updateContact);
        if (!$updateContact) return jsonResponse("ok", "99", 'updateStudent không thành công');
        if (!is_array($updateContact)) return jsonResponse("ok", "99", 'Phone or Email or CCCD trùng với ID ' . $updateContact);
        return jsonResponse("ok", "00", 'updateStudent student thành công', 1, $updateContact);
    }

    function getAllDataOfStudentByPhoneOrEmail(Request $request)
    {
         // check fields required accept
         $checkFieldAccept = checkFieldAccept(
            collect($request->all())->toArray(),
            $this->acceptFields['getAllDataOfStudentByPhoneOrEmail']
        );

        if (!$checkFieldAccept['status']) return jsonResponse("ok", "99", 'Thông số không cho phép ' . $checkFieldAccept['field']);

        $student = $this->registrationService->getAllDataOfStudentByPhoneOrEmail($request);
        if (!$student) return jsonResponse("ok", "99", 'Student không tồn tại');
        return jsonResponse("ok", "00", 'Thành công', 1, $student);
    }

    function updateStatus(Request $request)
    {
         // check fields required accept
         $checkFieldAccept = checkFieldAccept(
            collect($request->all())->toArray(),
            $this->acceptFields['updateStatus']
        );

        if (!$checkFieldAccept['status']) return jsonResponse("ok", "99", 'Thông số không cho phép ' . $checkFieldAccept['field']);

        if (!$request->ID) return jsonResponse("ok", "03", 'ID không tồn tại');
        $updateStatus = $this->registrationService->updateStatus($request);
        if (!$updateStatus) jsonResponse("ok", "99", 'UpdateStudentStatus không thành công');
        return jsonResponse("ok", "00", 'UpdateStudentStatus thành công', 1, $updateStatus);

    }

    function updateDocumentAndPayment(Request $request)
    {
        $checkFieldAccept = checkFieldAccept(
            collect($request->all())->toArray(),
            $this->acceptFields['updateDocumentAndPayment']
        );

        if (!$checkFieldAccept['status']) return jsonResponse("ok", "99", 'Thông số không cho phép ' . $checkFieldAccept['field']);

        $updateDocumentAndPayment = $this->registrationService->updateDocumentAndPayment($request);
        if (!$updateDocumentAndPayment) return jsonResponse("ok", "99", 'Update Không thành công');

        return jsonResponse("ok", "00", 'updateDocumentAndPayment thành công', 3, $updateDocumentAndPayment);
    }

    function sendOTP(Request $request)
    {
        $sendOTP = $this->registrationService->sendOTP($request);
        if (!$sendOTP) return jsonResponse("ok", "99", 'SendOTP Không thành công'); 

        return jsonResponse("ok", "00", 'SendOTP thành công', 1, $sendOTP);
    }

    function verifyOTP(Request $request)
    {
        $verifyOTP = $this->registrationService->verifyOTP($request);
        if (!$verifyOTP) return jsonResponse("ok", "99", 'verifyOTP Không thành công'); 

        return jsonResponse("ok", "00", 'verifyOTP thành công', 1, $verifyOTP);
    }

    // function CheckCCCD($cccd){
    //     // echo "123";
    //     $contact = $this->registrationService->checkExistStudentByPhone($cccd);
    //     if ($contact) return jsonResponse("ok", "01", 'Đã có student', 1, [
    //         'ID' => $contact['contactid'],
    //         'Email' => $contact['email'],
    //         'Phone' => $contact['mobile'],
    //         'FullName' => $contact['lastname'],
    //         'StatusCode' => $contact['contacts_status'],
    //         'Nam' => $contact['registered_year']
    //     ]);

    // }
}
