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
        $publicIp = $request->input('public_ip');
        $isMocked = $request->input('is_mocked');

        // -- ตรวจสอบเงื่อนไขสำคัญ --
        $vpn_status = 'ok';
        $notes = '';
        if ($isMocked === true || $isMocked === 'true' || $isMocked == 1) {
            $vpn_status = 'mock';
            $notes .= 'พบการจำลองตำแหน่ง (Mock Location). ';
        }
        if (!$lat || !$lng || !$this->isInThailand($lat, $lng)) {
            $vpn_status = 'foreign';
            $notes .= 'ตำแหน่งอยู่นอกประเทศไทย. ';
        }
        if (!$this->isThaiIP($publicIp ?? $ip)) {
            $vpn_status = 'vpn';
            $notes .= 'ตรวจพบ IP ไม่ใช่ประเทศไทย (VPN?). ';
        }

        // -- Log เฉพาะเมื่อ "user เปลี่ยนตำแหน่งจริง" หรือ vpn_status ไม่ใช่ ok --
        $shouldLog = false;
        if ($vpn_status !== 'ok') $shouldLog = true;
        else if ($user->latitude != $lat || $user->longitude != $lng) $shouldLog = true;

        if ($shouldLog) {
            UserLocationLog::create([
                'user_id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
                'ip' => $publicIp ?: $ip,
                'latitude' => $lat,
                'longitude' => $lng,
                'vpn_status' => $vpn_status,
                'notes' => $notes,
            ]);
        }

        // -- อัปเดตตำแหน่งล่าสุดใน users เฉพาะกรณีตำแหน่งปกติ
        if ($vpn_status === 'ok') {
            $user->latitude = $lat;
            $user->longitude = $lng;
            $user->location_updated_at = now();
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
        if (!$ip) return false;
        return preg_match('/^(1|27|43|49|58|101|103|110|111|112|113|114|115|116|118|119|120|121|122|124|125|126|128|134|139|171|172|175|180|182|183|202|203|210|218|219|223)\./', $ip);
    }
}
