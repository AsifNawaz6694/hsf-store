<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Datatables;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

class DBBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take the Database backup every night by 10.30pm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dbname = 'hsf_store';
        $path = public_path('/database-backup');
        if (!file_exists($path)) {
            mkdir($path);
        }
        $backup_file = $dbname . date("Y-m-d-H-i-s") . '.gz';
        $command = "mysqldump --opt -h 127.0.0.1 -u root hsf_store | gzip > database-backup/$backup_file";
        system($command);
        $file = $path . '/' . $backup_file;
        Mail::send([], [], function ($message) use ($file) {
            $message->from('asiif23@gmail.com', 'Database Backup')
                ->to('asiif23@gmail.com', 'Asif Nawaz')
                ->cc('rabnawaz2186@gmail.com', 'Rab Nawaz')
                ->attach($file)
                ->subject('HSF Database Backup');
        });
        File::deleteDirectory($path);
    }
}
