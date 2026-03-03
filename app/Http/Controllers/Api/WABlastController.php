<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WABlastController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'phone' => 'required',
            'appointment_date' => 'required',
        ]);

        $message = $request->message;
        $phone = $request->phone;

        $phone = str_replace('0', '62', $phone);
        $phone = str_replace(' ', '', $phone);

        $curl = curl_init();
        curl_setopt_array($curl, [
        CURLOPT_URL => 'http://localhost:3000/send-message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'phone' => $phone,
            'message' => $message,
            'appointment_date' => $request->appointment_date,
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return response()->json(json_decode($response, true));
    }
}
