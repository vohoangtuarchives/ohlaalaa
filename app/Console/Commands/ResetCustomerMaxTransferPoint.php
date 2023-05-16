<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ResetCustomerMaxTransferPoint extends Command
{

    protected $signature = 'reset:customer-monthly-transfer-points';


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        User::where("can_transfer_point", '=', 1)->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $user->max_transfer_point = $user->shopping_point * config("tuezy.monthly_transfer_percents", 0.2);
                $user->transfered_shopping_point = 0;
                $user->save();
            }
        });
    }
}
