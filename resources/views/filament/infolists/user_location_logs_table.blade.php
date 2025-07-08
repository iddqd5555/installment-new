@php $logs = $getState(); @endphp

@if(count($logs))
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border border-gray-300 rounded-xl bg-white shadow">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-3 border-b">วันเวลา</th>
                    <th class="py-2 px-3 border-b">IP</th>
                    <th class="py-2 px-3 border-b">VPN</th>
                    <th class="py-2 px-3 border-b">Latitude</th>
                    <th class="py-2 px-3 border-b">Longitude</th>
                    <th class="py-2 px-3 border-b">Notes</th>
                    <th class="py-2 px-3 border-b">แผนที่</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="py-2 px-3 border-b whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="py-2 px-3 border-b">{{ $log->ip }}</td>
                        <td class="py-2 px-3 border-b">{{ $log->vpn_status }}</td>
                        <td class="py-2 px-3 border-b">{{ $log->latitude }}</td>
                        <td class="py-2 px-3 border-b">{{ $log->longitude }}</td>
                        <td class="py-2 px-3 border-b">{{ $log->notes }}</td>
                        <td class="py-2 px-3 border-b">
                            @if($log->latitude && $log->longitude)
                                <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="inline-flex items-center px-3 py-1 rounded bg-blue-500 text-white text-xs font-bold hover:bg-blue-600">
                                    ดูแผนที่
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-gray-400 italic">ไม่มีประวัติการเปลี่ยนแปลง</div>
@endif
