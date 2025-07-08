<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserLocationLog;

class UserLocationController extends Controller
{
    public function updateLocation(Request $request)
    {
        $user = $request->user();
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $ip = $request->ip();

        // Default
        $vpn_status = 'ok';
        $notes = '';

        // 1. Mock Location (มือถือส่ง is_mocked มาก็เช็คเพิ่มได้)
        $isMocked = $request->input('is_mocked');
        if ($isMocked === true || $isMocked === 'true' || $isMocked === 1) {
            $vpn_status = 'mock';
            $notes .= 'พบการจำลองตำแหน่ง (Mock Location). ';
        }

        // 2. นอกขอบเขตประเทศไทย
        if (!$lat || !$lng || !$this->isInThailand($lat, $lng)) {
            $vpn_status = 'foreign';
            $notes .= 'ตำแหน่งนอกประเทศไทย. ';
        }

        // 3. ตรวจสอบ IP (ถ้าอยากเช็ค VPN จริง อาจต้องใช้ service ภายนอก)
        // (simple: ถ้า ip ไม่ใช่ไทย ให้ flag ว่า 'vpn')
        if (!$this->isThaiIP($ip)) {
            $vpn_status = 'vpn';
            $notes .= 'ตรวจพบ IP ไม่ใช่ประเทศไทย (อาจเป็น VPN). ';
        }

        // บันทึก log ทุกครั้ง (สำคัญ)
        UserLocationLog::create([
            'user_id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'phone' => $user->phone,
            'ip' => $ip,
            'latitude' => $lat,
            'longitude' => $lng,
            'vpn_status' => $vpn_status,
            'notes' => $notes,
        ]);

        // save เฉพาะกรณี ok
        if ($vpn_status === 'ok') {
            $user->latitude = $lat;
            $user->longitude = $lng;
            $user->save();
            return response()->json(['status' => 'ok']);
        } else {
            return response()->json(['status' => $vpn_status, 'msg' => $notes], 202);
        }
    }

    private function isInThailand($lat, $lng)
    {
        return ($lat >= 5.0 && $lat <= 21.0) && ($lng >= 97.0 && $lng <= 106.0);
    }
    private function isThaiIP($ip)
    {
        // ตัวอย่างง่ายๆ: เฉพาะ 110/111/112/113... ขึ้นต้นไทย (ควรใช้ API จริงเช็ค IP to country)
        return preg_match('/^(1|27|43|49|58|101|103|110|111|112|113|114|115|116|118|119|120|121|122|124|125|126|128|134|139|171|172|175|180|182|183|202|203|210|218|219|223)\./', $ip);
    }
}
