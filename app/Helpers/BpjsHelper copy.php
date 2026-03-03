<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use LZString\LZString;


class BpjsHelper
{
    private $Xconsid;
    private $Xconssecret;
    private $Userkey;
    private $Xkodeppk;
    private $norujukan  = "";
    private $response;
    private $idrequest;
    private $jenisaplikasi;
    private $jenisconsid;
    private $url;
    private $Xtimestamp;
    private $ContentType;

    public static function post($endpoint, $data)
    {
        $baseUrl = 'https://bpjs-url.com'; // Sesuaikan URL dasar API BPJS
        $response = Http::post($baseUrl . $endpoint, $data);

        return $response->json();
    }
    public static function generateSignature()
    {
        $consId = env('ANTROL_CONS_ID');
        $secretKey = ENV('ANTROL_SECRET_KEY');
        $timestamp = strval(now()->timestamp - Carbon::create('1970-01-01 00:00:00')->timestamp);
        $data = $consId . "&" . $timestamp;

        $signature = hash_hmac('sha256', $data, $secretKey, true);
        return base64_encode($signature);
    }

    public static function getTimestamp()
    {
        return strval(now()->timestamp - Carbon::create('1970-01-01 00:00:00')->timestamp);
    }

    public static function getRequest($urlQL, $endpoint, array $params = [])
    {
        // $url = env('ANTROL_BASE_URL') . $endpoint;
        $baseUrl = $urlQL === 'QLJ' ? env('ANTROL_BASE_URL') : env('ANTROL_BASE_URL_QLKP');
        $url = $baseUrl . $endpoint;
        Log::info('=== [getRequest - ' . $urlQL . '] getRequest url = ' . $url);
        // Log::info('=== [getRequest - ' . $urlQL . '] getRequest params = ' . $params);
        // $data = [];
        // foreach ($params as $param) {
        //     if ($param !== null) {
        //         $data = array_merge($data, $param);
        //     }
        // }

        $response = Http::withHeaders([
            'X-Cons-Id' => env('ANTROL_CONS_ID'),
            'X-Timestamp' => self::getTimestamp(),
            'X-Signature' => self::generateSignature(),
            'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
        ])->get($url, $params);

        if (!$response->successful()) {
            Log::error('Failed request to ' . $url, [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        }
        return $response->body();
    }

    public static function postRequest($urlQL, $endpoint, array $data = [])
    {
        // $url = env('ANTROL_BASE_URL') . $endpoint;
        //$url = 'http://172.100.10.11/sirstql/api/WSAntrianOnline/pengiriman_taskID';
        $baseUrl = $urlQL === 'QLJ' ? env('ANTROL_BASE_URL') : env('ANTROL_BASE_URL_QLKP');
        $url = $baseUrl . $endpoint;
        $response = Http::withHeaders([
            'X-Cons-Id' => env('ANTROL_CONS_ID'),
            'X-Timestamp' => self::getTimestamp(),
            'X-Signature' => self::generateSignature(),
            'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
        ])->post($url, $data);

        return $response->body();
    }

    public static function postRequestSelf($urlQL, $endpoint, array $data = [])
    {
        $request = array(
            "kodebooking" => $data['kodebooking'],
            "taskid" => $data['taskid'],
            "waktu" => $data['waktu']
        );

        $url        = '/antrean/updatewaktu';
        $uploadData = json_encode($request);
        $method     = 'POST';
        $response   = self::requestBpjsAntrol($url, $uploadData, $method);

        self::response([
            "url" => $response['url'],
            "metadata" => $response['metaData']
        ], REST_Controller::HTTP_OK);
    }

    function vclaimDecrypt($key, $string)
    {
        $encrypt_method = 'AES-256-CBC';
        // hash
        $key_hash = hex2bin(hash('sha256', $key));
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
        return $output;
    }

    function vclaimDecompress($string)
    {
        return LZString::decompressFromEncodedURIComponent($string);
    }

    function requestBpjsAntrol($url,$uploadData,$method,$contenType='')
    {
        // $this->jenisaplikasi= JENISAPLIKASI_BPJS_ANTROL;
        // $this->jenisconsid  = JENISCONSID_PRODUCTION; //JENISCONSID_DEVELOPMENT;
        $this->ContentType  = ((empty($contenType)) ? "application/json; charset=utf-8" : $contenType );
        $this->response     = $this->createSignature($url,$uploadData,$method);

        $result['url']      = $url;
        //jika gagal --> permintaan telah diterima untuk diproses, tetapi pemrosesan belum selesai
        if(empty($this->response))
        {
            $result['metaData']['code'] = '202';
            $result['metaData']['message'] = 'Bridging Sedang Bermasalah, Silakan Coba Lagi.!';
            return $result;
        }

        //jika berhasil -->permintaan telah diterima untuk diproses, dan pemrosesan selesai
        $result['metaData'] = ((isset($this->response->metadata)) ? $this->response->metadata : $this->response );
        if( ($this->response->metadata->code == 200 OR $this->response->metadata->code == 1) && empty(!$this->response->metadata))
        {
            if(isset($this->response->response))
            {
                 //decrypt hasil response
                $decrypt    = $this->vclaimDecrypt($this->Xconsid.$this->Xconssecret.$this->Xtimestamp,$this->response->response);
                //decompress hasil response
                $decompress = $this->vclaimDecompress($decrypt);
                $result['response'] = json_decode($decompress);
            }

        }
        return $result;
    }

    private function upass()
    {
        $this->Xconsid      = 22634;
        $this->Xconssecret  = '0fV130B0EF';
        $this->Userkey      = 'b9a5273459fca3ebd728ffe6f0c14f6f';
        $this->Xkodeppk     = "0179R014";
    }

    function createSignature($requestParameter, $uploadedJSON = '', $method = 'POST')
    {
        $debug = "";
        $this->upass();
        $this->url = 'https://apijkn.bpjs-kesehatan.go.id/antreanrs';

        //menghitung timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        $this->Xtimestamp = $tStamp;

        // else if($this->jenisaplikasi == JENISAPLIKASI_BPJS_ANTROL)
        // {
            //menghitung tanda tangan dengan melakukan hash terhadap salt dengan kunci rahasia sebagai kunci
        $signature          = base64_encode(hash_hmac('sha256', $this->Xconsid."&".$tStamp, $this->Xconssecret, true));
        $headers = array(   "Accept: application/json",
                            "X-cons-id: ".$this->Xconsid,
                            "X-timestamp: ".$this->Xtimestamp,
                            "X-signature: ".$signature,
                            "user_key: ".$this->Userkey,
                            "Format:Json",
                            "Content-Type: application/json"
                        );
        // }


        $debug .= json_encode($headers);

        $ch = curl_init($this->url.$requestParameter);
        $debug .= $this->url.$requestParameter;
        $debug .= $uploadedJSON;

        // else if($this->jenisaplikasi == JENISAPLIKASI_VCLAIM_V2 OR $this->jenisaplikasi == JENISAPLIKASI_BPJS_ANTROL OR $this->jenisaplikasi == JENISAPLIKASI_ICARE)
        // {
           if ($uploadedJSON != '')
            {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadedJSON);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // }



        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);

        if (empty($data) or $data == "null")
        {
            $data = curl_error($ch);
        }
        curl_close($ch);
        //debug mode
        // if($this->jenisaplikasi == JENISAPLIKASI_BPJS_ANTROL)
        // {

        //     var_dump("</br>");
        //     var_dump("Nilai Debug = ".$debug);
        //     var_dump("</br>");
        //     var_dump("<<01>>");
        //     // var_dump(json_encode($data));
        //     $dedata = json_decode($data);
        //     var_dump($dedata);
        //     var_dump("<<11>>");
        //     die;
        // }
        return json_decode($data);

    }

    public static function postTaskID($urlQL, $endpoint, array $data = [])
    {
        // $kodebooking = $data['kodebooking'];
        // $taskid = sql_clean(trim($this->get_post('taskid')));
        // $waktu = sql_clean(trim($this->get_post('waktu')));
        $request = array(
            "kodebooking" => $data['kodebooking'],
            "taskid" => $data['taskid'],
            "waktu" => $data['waktu']
        );
        $url        = '/antrean/updatewaktu';
        $uploadData = json_encode($request);
        $method     = 'POST';
        $response   = $this->bpjsbap->requestBpjsAntrol($url, $uploadData, $method);

        $this->response([
            "url" => $response['url'],
            "metadata" => $response['metaData']
        ], REST_Controller::HTTP_OK);

        // // $url = env('ANTROL_BASE_URL') . $endpoint;
        // //$url = 'http://172.100.10.11/sirstql/api/WSAntrianOnline/pengiriman_taskID';
        // $baseUrl = $urlQL === 'QLJ' ? env('ANTROL_BASE_URL') : env('ANTROL_BASE_URL_QLKP');
        // $url = $baseUrl . $endpoint;
        // $response = Http::withHeaders([
        //     'X-Cons-Id' => env('ANTROL_CONS_ID'),
        //     'X-Timestamp' => self::getTimestamp(),
        //     'X-Signature' => self::generateSignature(),
        //     'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
        // ])->post($url, $data);

        // return $response->body();
    }

    // public static function postRequest($endpoint, ...$params)
    // {
    //     $url = env('ANTROL_BASE_URL') . $endpoint;
    //     //'http://172.100.10.11/sirstql/api/WSAntrianOnline/pengiriman_taskID';
    //     $data = [];
    //     foreach ($params as $param) {
    //         if ($param !== null) {
    //             $data = array_merge($data, $param);
    //         }
    //     }

    //     $response = Http::withHeaders([
    //         'X-Cons-Id' => env('ANTROL_CONS_ID'),
    //         'X-Timestamp' => self::getTimestamp(),
    //         'X-Signature' => self::generateSignature(),
    //         'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
    //     ])->post($url, $data);

    //     return $response->body();
    // }



    // public static function decryptResponse($response)
    // {
    //     $key = env('BPJS_SECRET_KEY');
    //     $key = hex2bin(hash('sha256', $key));

    //     $ciphertext = base64_decode($response);
    //     $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    //     $iv = substr($ciphertext, 0, $ivLength);
    //     $encryptedData = substr($ciphertext, $ivLength);

    //     $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);

    //     return $decrypted;
    // }
}
