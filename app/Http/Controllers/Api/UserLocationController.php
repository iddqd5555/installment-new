<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserLocationController extends Controller
{
    public function updateLocation(Request $request)
    {
        $user = $request->user();
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // Check ว่าเป็นไทยไหม (กัน emulator/VPN/mock)
        if (!$lat || !$lng || !$this->isInThailand($lat, $lng)) {
            \Log::warning('Reject GPS: ', ['user_id' => $user->id, 'lat' => $lat, 'lng' => $lng, 'ip' => $request->ip()]);
            return response()->json(['status' => 'reject', 'msg' => 'ตำแหน่งคุณไม่อยู่ในประเทศไทยหรือผิดปกติ'], 403);
        }

        $user->latitude = $lat;
        $user->longitude = $lng;
        $user->save();

        \Log::info('Update GPS ok', ['user_id' => $user->id, 'lat' => $lat, 'lng' => $lng, 'ip' => $request->ip()]);
        return response()->json(['status' => 'ok']);
    }

    private function isInThailand($lat, $lng)
    {
        // check lat/lng ไทย (ง่ายๆ)
        return ($lat >= 5.0 && $lat <= 21.0) && ($lng >= 97.0 && $lng <= 106.0);
    }
}
