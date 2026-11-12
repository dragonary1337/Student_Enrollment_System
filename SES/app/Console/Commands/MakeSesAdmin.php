<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSesAdmin extends Command
{
    protected $signature = 'ses:make-admin {email : Email address of the account to promote}';

    protected $description = 'Activate an account as the initial SES administrator';

    public function handle(): int
    {
        if (User::query()->where('role', 'Admin')->where('status', 'Active')->exists()) {
            $this->components->error('An active administrator already exists.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->components->error('No registered account was found for that email.');

            return self::FAILURE;
        }

        if (! $this->confirm("Promote {$user->email} to the active system administrator?")) {
            return self::SUCCESS;
        }

        $user->forceFill([
            'role' => 'Admin',
            'status' => 'Active',
        ])->save();

        $this->components->info('Administrator account activated.');

        return self::SUCCESS;
    }
}
