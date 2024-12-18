<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PushDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:push-doc {doc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (New Client(Account::query()->first()))->init();

        $lead = $amoApi->service->leads()->find(2435059);

        $link = $lead->cf('PDF - договор')->getValue();

        if ($link) {

            $file = file_get_contents($link);

//            $file = fopen($link, 'r');

            dd($file);
        }
    }
}
