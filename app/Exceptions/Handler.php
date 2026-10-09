<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (HttpException $exception, Request $request) {
            if (!$exception->getPrevious() instanceof TokenMismatchException
                || $request->expectsJson()
                || !$request->routeIs('registration-forms.store', 'registration-forms.update')) {
                return null;
            }

            $formId = $request->routeIs('registration-forms.update') ? (string) $request->route('registration_form') : 'new';
            if ($formId !== 'new' && !ctype_digit($formId)) return null;
            $input = [];
            foreach (['name' => 255, 'fields_json' => 60000] as $key => $limit) {
                $value = $request->input($key);
                if (is_string($value) && mb_strlen($value) <= $limit) $input[$key] = $value;
            }
            // Keep the draft through the login redirect; never submit it automatically.
            $request->session()->put('registration_form_recovery', ['form_id' => $formId, 'input' => $input]);

            return $formId === 'new'
                ? redirect()->route('registration-forms.create', ['recover' => 1])
                : redirect()->route('registration-forms.edit', ['registration_form' => $formId, 'recover' => 1]);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
