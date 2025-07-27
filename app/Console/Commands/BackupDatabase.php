<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:db {--disk=local}';
    protected $description = 'Backup the database to storage/app/backups';

    public function handle()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host     = env('DB_HOST');
        $backupPath = storage_path('app/backups');
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0775, true);
        }

        $fileName = 'backup_' . $database . '_' . date('Ymd_His') . '.sql';
        $filePath = $backupPath . '/' . $fileName;

        // MariaDB/MySQL only
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info("Backup สำเร็จ: " . $filePath);
            // (optional) ลบไฟล์ backup เก่าเกิน 7 วัน
            foreach (glob($backupPath . '/backup_*.sql') as $oldfile) {
                if (filemtime($oldfile) < (time() - 7 * 86400)) {
                    unlink($oldfile);
                }
            }
        } else {
            $this->error("Backup ล้มเหลว! ตรวจสอบ mysqldump และ config .env");
        }
    }
}
