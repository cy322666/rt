<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Doc;
use App\Services\amoCRM\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class DocController extends Controller
{
    /**
     * @throws \Exception
     */
    public function get()
    {
        $amoApi = (New Client(Account::query()->first()))->init();

        $lead = $amoApi->service->leads()->find(2435059);

        $link = $lead->cf('PDF - договор')->getValue();

        if ($link) {

            $file = fopen($link, 'r');

            dd($file);
        }
    }

    public function push()
    {
        $docs = [];

        foreach ($docs as $doc) {

            Artisan::call('app:push-doc', ['doc' => null]);
        }
    }
}
