<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class WABlastControllerapi extends Controller
{
    /**
     * Menampilkan halaman utama dengan data dari endpoint 'http://localhost:3101/'.
     */
    public function index()
    {
        try {
            // Membuat instance Guzzle HTTP Client
            $client = new Client();

            // Melakukan permintaan GET ke endpoint 'http://localhost:3101/'
            $response = $client->get('http://localhost:3101/');

            // Memastikan status code adalah 200 (OK)
            if ($response->getStatusCode() === 200) {
                // Mengambil body response (HTML) dan mengembalikannya ke browser
                $htmlContent = $response->getBody()->getContents();
                return response($htmlContent)->header('Content-Type', 'text/html');
            } else {
                // Jika status code bukan 200, kembalikan pesan error
                return response('Failed to load the page from the server.', 500);
            }
        } catch (\Exception $e) {
            // Menangani kesalahan jika terjadi error saat melakukan permintaan
            return response('An error occurred while fetching the page: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Mengirim pesan melalui endpoint 'http://localhost:3101/send-message'.
     */
    public function send(Request $request)
    {
        // Validasi input dari request
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $client = new Client();
            // alamat WA Blast Antrian Online
            $response = $client->post('http://172.100.10.36:3002/send-message', [
            //$response = $client->post('http://localhost:3101/send-message', [
                'json' => [
                    'phone' => $validated['phone'],
                    'message' => $validated['message'],
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            if ($responseData['success'] ?? false) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim',
                    'data' => $responseData['data'] ?? null,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $responseData['error'] ?? 'Gagal mengirim pesan',
                    'details' => $responseData['details'] ?? null,
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while sending the message',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function send_all()
    {
        // Ambil tanggal hari ini
        $today = now()->toDateString(); // Format: 'YYYY-MM-DD'
        // Query untuk mendapatkan data dari database
        // Query untuk mendapatkan data dari database
        $detailData = DB::table('data_kodebooking')
            ->select('norm', 'message') // Ambil kolom yang relevan
            ->whereDate('tanggalperiksa', $today) // Filter hanya data hari ini
            ->get();

        // Inisialisasi array untuk pengelompokan data
        $groupedData = [
            'sudah_terbit_sep' => [],
            'masa_berlaku_habis' => [],
            'bridging_bermasalah' => [],
            'nomor_referensi_belum_sesuai' => [],
            'rujukan_tidak_valid' => [],
            'dokter_tidak_ditemukan' => [],
            'data_nik_belum_sesuai' => [],
        ];

        // Proses data dan kelompokkan berdasarkan isi kolom message
        foreach ($detailData as $data) {
            if (strpos($data->message, 'sudah terbit SEP') !== false) {
                $groupedData['sudah_terbit_sep'][] = $data->norm;
            } elseif (strpos($data->message, 'tidak valid / masa berlaku habis') !== false) {
                $groupedData['masa_berlaku_habis'][] = $data->norm;
            } elseif (strpos($data->message, 'Bridging Sedang Bermasalah') !== false) {
                $groupedData['bridging_bermasalah'][] = $data->norm;
            } elseif (strpos($data->message, 'data nomorreferensi  belum sesuai.') !== false) {
                $groupedData['nomor_referensi_belum_sesuai'][] = $data->norm;
            } elseif (strpos($data->message, 'Rujukan tidak valid') !== false) {
                $groupedData['rujukan_tidak_valid'][] = $data->norm;
            } elseif (strpos($data->message, 'Data dokter tidak ditemukan') !== false) {
                $groupedData['dokter_tidak_ditemukan'][] = $data->norm;
            } elseif (strpos($data->message, 'data nik  belum sesuai.') !== false) {
                $groupedData['data_nik_belum_sesuai'][] = $data->norm;
            }

        }

        $phoneNumbers = [
            '085643639163', // andri
            '087727652807', // nur
            '085743365711', // putri
            '081333100232', // pak didik - manj.humas
            '085643595091', // pak bakhtiar - manj.BPTI
            '08994603812', // pak izul - manj.Pelayanan Penunjang
        ];

        // Pesan yang akan dikirim
        // Bangun pesan berdasarkan data yang dikelompokkan
        $message = "📅 *ANTROL - Tanggal " . now()->format('d-M-Y') . "*\n";
        $message .= "_Halo Antrol Mania, berikut rekap kondisi kodebooking antrol hari ini:_\n";
        $message .= "--------------------------------------------------\n";
        $message .= "### 📋 Kategori Masalah Kodebooking\n\n";

        // Fungsi helper untuk membangun pesan per kategori
        function buildCategoryMessage($categoryData, $notes) {
            $count = count($categoryData); // Hitung jumlah data
            $normList = empty($categoryData) ? '-' : implode(', ', $categoryData);
            return "   Jumlah: $count\n" .
                "   Nomor RM: $normList\n" .
                "   _Catatan:_ $notes\n\n";
        }

        // Sudah Terbit SEP
        $message .= "1. ✅ Sudah Terbit SEP\n";
        $message .= buildCategoryMessage(
            $groupedData['sudah_terbit_sep'],
            "SEP sudah terbit terlebih dahulu sebelum Antrol terkirim. Ada kemungkinan juga pasien salah memasukkan no.kontrol. Mohon cek kembali."
        );

        // Masa Berlaku Habis
        $message .= "2. ⚠️ Masa Berlaku Habis\n";
        $message .= buildCategoryMessage(
            $groupedData['masa_berlaku_habis'],
            "Silakan cek kembali tanggal dan kategori pada no.rujukan."
        );

        // Bridging Bermasalah
        $message .= "3. 🔧 Bridging Sedang Bermasalah\n";
        $message .= buildCategoryMessage(
            $groupedData['bridging_bermasalah'],
            "Silahkan coba kembali."
        );

        // Nomor Referensi Tidak Sesuai
        $message .= "4. 🔍 Nomor Referensi Belum Sesuai\n";
        $message .= buildCategoryMessage(
            $groupedData['nomor_referensi_belum_sesuai'],
            "Pastikan nomor referensi yang dimasukkan sesuai dengan data rujukan."
        );

        // Rujukan Tidak Valid
        $message .= "5. ❌ Rujukan Tidak Valid\n";
        $message .= buildCategoryMessage(
            $groupedData['rujukan_tidak_valid'],
            "Mohon periksa kembali data rujukan pasien."
        );

        // Data Dokter Tidak Ditemukan
        $message .= "6. 👩‍⚕️ Data Dokter Tidak Ditemukan\n";
        $message .= buildCategoryMessage(
            $groupedData['dokter_tidak_ditemukan'],
            "Pastikan kode dokter sesuai dengan data BPJS/HFIS."
        );

        // Data NIK Belum Sesuai
        $message .= "7. 🆔 NIK Belum Sesuai\n";
        $message .= buildCategoryMessage(
            $groupedData['data_nik_belum_sesuai'],
            "Pastikan data NIK Pasien sudah sesuai."
        );

        $message .= "--------------------------------------------------\n\n";
        $message .= "🙏 Terima kasih atas kerja samanya!\n";
        $message .= "Jika ada pertanyaan atau butuh bantuan lebih lanjut, jangan ragu untuk menghubungi kami. 😊\n";
        $message .= "Tim IT QL Hospital\n\n";
        $message .= "_*This is an automated message, please do not reply.*_";

        // Array untuk menyimpan hasil pengiriman
        $results = [];

        // Loop untuk mengirim pesan ke setiap nomor
        foreach ($phoneNumbers as $phone) {
            try {
                // Panggil fungsi send() untuk mengirim pesan
                $request = new Request(['phone' => $phone, 'message' => $message]);
                $response = $this->send($request);

                // Simpan hasil pengiriman
                $results[] = [
                    'phone' => $phone,
                    'status' => 'success',
                    'message' => 'Pesan berhasil dikirim',
                    'response' => $response,
                ];
            } catch (\Exception $e) {
                // Tangani error jika pengiriman gagal
                $results[] = [
                    'phone' => $phone,
                    'status' => 'failed',
                    'message' => 'Gagal mengirim pesan',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Kembalikan hasil pengiriman
        return response()->json([
            'success' => true,
            'results' => $results,
        ], 200);
    }
}
