@if($logs->isEmpty())
    <div style="margin:24px 0">ไม่พบประวัติ GPS 7 วันล่าสุด</div>
@else
    <div style="margin:24px 0">
        <h3 style="font-size:1.2rem;font-weight:bold">ประวัติ GPS 7 วันล่าสุด</h3>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:7px 10px;border:1px solid #eee;">วันเวลา</th>
                    <th style="padding:7px 10px;border:1px solid #eee;">Lat</th>
                    <th style="padding:7px 10px;border:1px solid #eee;">Lng</th>
                    <th style="padding:7px 10px;border:1px solid #eee;">สถานะ</th>
                    <th style="padding:7px 10px;border:1px solid #eee;">IP</th>
                    <th style="padding:7px 10px;border:1px solid #eee;">บันทึก</th>
                    <th style="padding:7px 10px;border:1px solid #eee;">Map</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td style="padding:6px 8px;border:1px solid #eee;">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                        <td style="padding:6px 8px;border:1px solid #eee;">{{ $log->latitude ?? '-' }}</td>
                        <td style="padding:6px 8px;border:1px solid #eee;">{{ $log->longitude ?? '-' }}</td>
                        <td style="padding:6px 8px;border:1px solid #eee;">{{ $log->vpn_status ?? '-' }}</td>
                        <td style="padding:6px 8px;border:1px solid #eee;">{{ $log->ip ?? '-' }}</td>
                        <td style="padding:6px 8px;border:1px solid #eee;">{{ $log->notes ?? '-' }}</td>
                        <td style="padding:6px 8px;border:1px solid #eee;">
                            @if($log->latitude && $log->longitude)
                                <a href="https://maps.google.com/?q={{$log->latitude}},{{$log->longitude}}" target="_blank" style="color:#1976d2">Map</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
