<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\InstallmentPayment; // ✅ เพิ่มตรงนี้
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            DailyReportResource\Widgets\DailyReportOverview::class,
        ];
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        $filters = $this->tableFilters['payment_due_date'] ?? [];

        session([
            'daily_reports.date_from' => $filters['date_from'] ?? Carbon::today()->toDateString(),
            'daily_reports.date_until' => $filters['date_until'] ?? Carbon::today()->toDateString(),
        ]);

        return parent::applyFiltersToTableQuery($query);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {

                    // ดึงจาก session ตามวันที่เลือกไว้
                    $dateFrom = session('daily_reports.date_from', Carbon::today()->toDateString());
                    $dateUntil = session('daily_reports.date_until', Carbon::today()->toDateString());

                    $records = InstallmentPayment::with('installmentRequest')
                        ->whereBetween('payment_due_date', [
                            Carbon::parse($dateFrom)->startOfDay(),
                            Carbon::parse($dateUntil)->endOfDay()
                        ])
                        ->get();

                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $sheet->fromArray([
                        'ลำดับ', 'วันที่ชำระ', 'เวลา', 'ชื่อลูกค้า',
                        'หมายเลขสัญญา', 'เลขที่ใบแจ้งหนี้',
                        'ราคาทองบาทละ (บาท)', 'จำนวนทอง (บาททอง)', 'ยอดที่ต้องชำระ (บาท)',
                        'ยอดที่ชำระแล้ว (บาท)', 'ยอดคงเหลือ (บาททอง)', 'ยอดคงเหลือมูลค่า (บาท)',
                        'พนักงานที่รับผิดชอบ', 'สถานะ'
                    ], NULL, 'A1');

                    $row = 2;
                    $counter = 1;

                    foreach ($records as $record) {
                        $paymentDate = Carbon::parse($record->payment_due_date);

                        $sheet->fromArray([
                            $counter++,
                            $paymentDate->format('Y-m-d'),
                            $paymentDate->format('H:i:s'),
                            $record->installmentRequest->fullname ?? '-',
                            $record->installmentRequest->contract_number ?? '-',
                            $record->installmentRequest->payment_number ?? '-',
                            number_format($record->installmentRequest->approved_gold_price ?? 0, 2),
                            number_format($record->installmentRequest->gold_amount ?? 0, 2),
                            // รวมราคาทอง
                            number_format(
                            ($record->installmentRequest->approved_gold_price ?? 0) * ($record->installmentRequest->gold_amount ?? 0), 2
                            ),
                            number_format($record->amount_paid ?? 0, 2),
                            // ยอดคงเหลือ (ทอง)
                            number_format(
                            max(0, ($record->installmentRequest->gold_amount ?? 0)
                                - (($record->installmentRequest->total_paid ?? 0) / max(1, ($record->installmentRequest->approved_gold_price ?? 1)))
                            ), 2
                            ),
                            // ยอดคงเหลือ (บาท)
                            number_format(
                            max(0,
                                (($record->installmentRequest->approved_gold_price ?? 0)
                                * ($record->installmentRequest->gold_amount ?? 0))
                                - ($record->installmentRequest->total_paid ?? 0)
                            ), 2
                            ),
                            $record->installmentRequest->responsible_staff ?? '-',
                            $record->status ?? '-'
                        ], NULL, 'A' . $row++);
                    }

                    // Auto Column Width
                    foreach (range('A', 'N') as $columnID) {
                        $sheet->getColumnDimension($columnID)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);

                    return response()->streamDownload(function () use ($writer) {
                        $writer->save('php://output');
                    }, 'daily-report-' . $dateFrom . '-to-' . $dateUntil . '.xlsx');
                }),
        ];
    }
}
