<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TopupUserCreditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topup:credit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Topup user credit in every start of the month';

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
     * @return int
     */
    public function handle()
    {
        $roleUser = Role::where('name', 'user')->first();
        $totalUsers = 0;
        foreach ($roleUser->users as $userAccount) {
            $result = DB::transaction(function() use ($userAccount) {
                $user = User::lockForUpdate()->find($userAccount->id);
                if ($user->is_premium_user) {
                    $user->credit = $user->credit + 40;
                } else {
                    $user->credit = $user->credit + 20;
                }
                $user->save();
                return true;
            });
            
            if ($result) {
                $totalUsers++;
            }
        }
        $this->info('Topup credit successfully to '.$totalUsers.' users');
    }
}
