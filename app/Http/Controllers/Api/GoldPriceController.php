<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class GoldPriceController extends Controller
{
    public function latest()
    {
        $response = Http::get('https://xn--42cah7d0cxcvbbb9x.com/');
        $html = $response->body();

        // ดึงราคาทองคำแท่ง 96.5%
        preg_match('/ทองคำแท่ง\s*96.5%.*?<td[^>]*>([\d,\.]+)<\/td>\s*<td[^>]*>([\d,\.]+)<\/td>/s', $html, $barMatches);
        // ดึงราคาทองรูปพรรณ 96.5%
        preg_match('/ทองรูปพรรณ\s*96.5%.*?<td[^>]*>([\d,\.]+)<\/td>\s*<td[^>]*>([\d,\.]+)<\/td>/s', $html, $jewelryMatches);
        // ดึงวันที่ล่าสุด
        preg_match('/อัพเดทวันที่\s*([^<]+)\s*เวลา\s*([^<]+)น\./u', $html, $dateMatch);

        // รวมวันที่และเวลา
        $dateText = isset($dateMatch[1], $dateMatch[2]) 
            ? trim($dateMatch[1]) . ' ' . trim($dateMatch[2]) . 'น.' 
            : '-';

        return response()->json([
            'gold_bar_buy'      => $barMatches[1] ?? '-',
            'gold_bar_sell'     => $barMatches[2] ?? '-',
            'gold_jewelry_buy'  => $jewelryMatches[1] ?? '-',
            'gold_jewelry_sell' => $jewelryMatches[2] ?? '-',
            'change_buy'        => '-', // ไม่มีข้อมูลขึ้น/ลงในเว็บนี้
            'change_sell'       => '-',
            'last_update'       => $dateText,
        ]);
    }

}
