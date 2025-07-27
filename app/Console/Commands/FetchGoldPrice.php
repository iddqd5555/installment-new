<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class FetchGoldPrice extends Command
{
    protected $signature = 'gold:fetch';
    protected $description = 'ดึงราคาทองรูปพรรณ 96.5% จาก www.ทองคำราคา.com แล้วบันทึกลง daily_gold_prices';

    public function handle()
    {
        $url = 'https://xn--42cah7d0cxcvbbb9x.com/';

        $response = Http::timeout(8)->withHeaders([
            'User-Agent' => 'Mozilla/5.0'
        ])->get($url);

        if ($response->successful()) {
            $html = $response->body();

            // ดึงราคาทองรูปพรรณ 96.5%
            preg_match('/ทองรูปพรรณ\s*96\.5%.*?<td[^>]*>([\d,\.]+)<\/td>\s*<td[^>]*>([\d,\.]+)<\/td>/u', $html, $matches);
            // $matches[1] = ขายออก, $matches[2] = รับซื้อ

            $sell = isset($matches[1]) ? floatval(str_replace(',', '', $matches[1])) : null;
            $buy = isset($matches[2]) ? floatval(str_replace(',', '', $matches[2])) : null;

            if ($sell && $buy) {
                DB::table('daily_gold_prices')->updateOrInsert(
                    ['date' => Carbon::today()->format('Y-m-d')],
                    [
                        'buy' => $buy,
                        'sell' => $sell,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $this->info("ราคาทองรูปพรรณวันนี้ (ทองคำราคา.com) ขายออก $sell รับซื้อ $buy");
            } else {
                $this->error('ไม่พบข้อมูลราคาทองรูปพรรณในเว็บต้นทาง');
            }
        } else {
            $this->error('ไม่สามารถเชื่อมต่อเว็บต้นทางได้');
        }
    }
}
