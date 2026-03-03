<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WABlastController extends Controller
{
    public function index()
    {
        return view('wablast.index');
    }

    public function send(Request $request)
    {
        $phone = $request->phone;
        $message = $request->message;
        
        $client = new \GuzzleHttp\Client();
        $response = $client->post('http://localhost:3000/send-message', [
            'json' => [
                'phone' => $phone,
                'message' => $message
            ]
        ]);

        return redirect()->route('wablast.index')->with('success', 'Message sent successfully');
    }
}
