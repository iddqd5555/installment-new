<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoldPriceController extends Controller
{
    /**
     * Return latest gold price from daily_gold_prices table.
     * Fallback to the latest available if today's price not available.
     */
    public function latest()
    {
        $today = Carbon::today()->toDateString();

        // ลองหาข้อมูลราคาทองวันนี้ก่อน
        $gold = DB::table('daily_gold_prices')
            ->where('date', $today)
            ->first();

        // ถ้ายังไม่มีข้อมูลวันนี้ ให้ดึงวันล่าสุดย้อนหลัง
        if (!$gold) {
            $gold = DB::table('daily_gold_prices')
                ->orderByDesc('date')
                ->first();
        }

        // ถ้าไม่มีข้อมูลเลย
        if (!$gold) {
            return response()->json([
                'gold_bar_buy'      => '-',
                'gold_bar_sell'     => '-',
                'gold_jewelry_buy'  => '-',
                'gold_jewelry_sell' => '-',
                'change_buy'        => '-',
                'change_sell'       => '-',
                'last_update'       => '-',
            ]);
        }

        // ---- ถ้าตารางมี field buy/sell คือ "ทองแท่ง" ----
        //    ถ้ามี field อื่นเช่น jewelry_buy, jewelry_sell ให้เพิ่มเอง
        //    ตัวอย่างนี้รองรับเฉพาะ buy/sell
        return response()->json([
            'gold_bar_buy'      => number_format($gold->buy, 2),
            'gold_bar_sell'     => number_format($gold->sell, 2),
            'gold_jewelry_buy'  => property_exists($gold, 'jewelry_buy') ? number_format($gold->jewelry_buy, 2) : '-',
            'gold_jewelry_sell' => property_exists($gold, 'jewelry_sell') ? number_format($gold->jewelry_sell, 2) : '-',
            'change_buy'        => '-', // ทำเพิ่มถ้าต้องการ
            'change_sell'       => '-',
            'last_update'       => $gold->date,
        ]);
    }
}
