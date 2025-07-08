{{-- DEBUG --}}
<pre>{{ json_encode($locationLogs ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

<x-filament-panels::page>
    <h2 class="text-xl font-semibold mb-4">
        ประวัติการใช้งานลูกค้า: {{ $record->first_name }} {{ $record->last_name }} ({{ $record->phone }})
    </h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th>เวลา</th>
                    <th>Lat</th>
                    <th>Lng</th>
                    <th>Map</th>
                    <th>IP</th>
                    <th>สถานะ</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($locationLogs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->latitude }}</td>
                        <td>{{ $log->longitude }}</td>
                        <td>
                            @if($log->latitude && $log->longitude)
                                <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}"
                                   target="_blank"
                                   class="px-2 py-1 bg-green-600 text-white rounded">
                                    ดูแผนที่
                                </a>
                            @endif
                        </td>
                        <td>{{ $log->ip }}</td>
                        <td>{{ $log->vpn_status }}</td>
                        <td>{{ $log->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">ไม่มีข้อมูล log การใช้งาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
