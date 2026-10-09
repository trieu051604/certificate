<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationForm;
use App\Models\ProvinceCertificationTest;
use App\Services\RegistrationFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationFormController extends Controller
{
    public function index()
    {
        $forms = RegistrationForm::withCount('courses')->orderBy('name')->paginate(30);
        return view('admin.registration-forms.index', compact('forms'));
    }

    public function create()
    {
        return $this->editor(new RegistrationForm(['fields' => RegistrationFields::defaults()]));
    }

    public function edit(RegistrationForm $registrationForm)
    {
        return $this->editor($registrationForm);
    }

    private function editor(RegistrationForm $form)
    {
        $recovery = request()->session()->get('registration_form_recovery');
        $formId = $form->exists ? (string) $form->id : 'new';
        if (request()->boolean('recover') && is_array($recovery) && ($recovery['form_id'] ?? null) === $formId) {
            request()->session()->forget('registration_form_recovery');
            request()->session()->flashInput($recovery['input']);
            request()->session()->now('form_warning', 'Phiên đăng nhập hoặc mã xác nhận đã hết hiệu lực. Thông tin bạn nhập đã được khôi phục; vui lòng kiểm tra và bấm Lưu form lại.');
        }
        $provinces = ProvinceCertificationTest::select('id', 'province')->get();
        return view('admin.registration-forms.edit', compact('form', 'provinces'));
    }

    private function data(Request $request): array
    {
        if (is_string($request->input('name'))) $request->merge(['name' => trim($request->input('name'))]);
        $data = $request->validate(['name' => 'required|string|max:255', 'fields_json' => 'required|string|max:60000']);
        return ['name' => $data['name'], 'fields' => app(RegistrationFields::class)->schema($data['fields_json'])];
    }

    public function store(Request $request)
    {
        RegistrationForm::create($this->data($request));
        return redirect()->route('registration-forms.index')->with('success', 'Đã tạo form đăng ký. Bạn có thể chọn form này trong khóa học.');
    }

    public function update(Request $request, RegistrationForm $registrationForm)
    {
        $registrationForm->update($this->data($request));
        return redirect()->route('registration-forms.edit', $registrationForm)->with('success', 'Đã cập nhật form đăng ký.');
    }

    public function destroy(RegistrationForm $registrationForm)
    {
        return DB::transaction(function () use ($registrationForm) {
            $registrationForm->lockForUpdate()->findOrFail($registrationForm->id);
            if ($registrationForm->courses()->exists()) return back()->with('error', 'Form đang gắn với khóa học. Hãy đổi form của khóa học trước khi xóa.');
            $registrationForm->delete();
            return back()->with('success', 'Đã xóa form đăng ký.');
        });
    }
}
