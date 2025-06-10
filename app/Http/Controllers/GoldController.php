<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class GoldController extends Controller
{
    public function index()
    {
        $response = Http::get('https://www.goldtraders.or.th/api/latest-price');

        if ($response->successful()) {
            $data = $response->json();
            $goldPrice = [
                'type' => 'ทองรูปพรรณ',
                'buy' => $data['response']['price']['gold_jewelry']['buy'],
                'sell' => $data['response']['price']['gold_jewelry']['sell']
            ];

            $error = null; // กำหนดค่า $error ให้ชัดเจนกรณีสำเร็จ
        } else {
            $goldPrice = null;
            $error = 'ไม่สามารถโหลดราคาทองคำได้ในขณะนี้'; // ข้อความ error ชัดเจน
        }

        return view('gold.index', compact('goldPrice', 'error'));
    }
}
