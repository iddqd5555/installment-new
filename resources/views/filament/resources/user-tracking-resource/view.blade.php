<x-filament::page>
    <h2>ประวัติการใช้งานลูกค้า: {{ $record->first_name }} {{ $record->last_name }} ({{ $record->phone }})</h2>
    <div class="overflow-x-auto mt-4">
        <table class="table-auto min-w-full">
            <thead>
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
            <tbody>
                @php
                  $logs = \App\Models\UserLocationLog::where('user_id', $record->id)->orderByDesc('created_at')->get();
                @endphp
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->latitude }}</td>
                        <td>{{ $log->longitude }}</td>
                        <td>
                            @if($log->latitude && $log->longitude)
                                <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}"
                                   target="_blank"
                                   class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                    <i class="bi bi-geo-alt-fill me-1"></i>ดูแผนที่
                                </a>
                            @endif
                        </td>
                        <td>{{ $log->ip }}</td>
                        <td>
                            @switch($log->vpn_status)
                                @case('ok')
                                    <span class="badge badge-success">ปกติ</span>
                                    @break
                                @case('mock')
                                    <span class="badge badge-danger">Mock</span>
                                    @break
                                @case('vpn')
                                    <span class="badge badge-danger">VPN</span>
                                    @break
                                @case('foreign')
                                    <span class="badge badge-warning">นอกไทย</span>
                                    @break
                                @default
                                    {{ $log->vpn_status }}
                            @endswitch
                        </td>
                        <td>{{ $log->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">ไม่พบประวัติการใช้งาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::page>
