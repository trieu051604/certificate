<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Registration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // return jsonResponse("ok", "03", $request->route()->getName());
        if (!$request->APICode) return jsonResponse("ok", "03", "API code không tìm thấy");
        if (!$request->CheckSum) return jsonResponse("ok", "02", "CheckSum không tìm thấy");

        $APICode = env('API_CODE');
        $accessToken = env('ACCESS_TOKEN');
        $hashKey = env('HASH_KEY');

        // $content = $request->route()->getName() == 'UpdateDocumentAndPayment' 
        //             ? $APICode . $accessToken
        //             : $APICode . $accessToken . $request->Phone . $request->Email . $request->Gender;
        // getAllDataOfStudentByPhoneOrEmail
        $specialContent = ['UpdateDocumentAndPayment'];
        $sendOTP = ['verifyOTP', 'sendOTP'];

        $content = $APICode . $accessToken . $request->Phone . $request->Email . $request->Gender;
        if (in_array($request->route()->getName(), $specialContent)) $content = $APICode . $accessToken;
        if (in_array($request->route()->getName(), $sendOTP)) $content = $APICode . $accessToken . $request->Phone;

        // return jsonResponse("ok", "02", [
        //     $content, getCheckSum($content, $hashKey)
        // ]);
        
        if (getCheckSum($content, $hashKey) != $request->CheckSum) return jsonResponse("ok", "02", "CheckSum không chính xác");

        return $next($request);
    }
}
