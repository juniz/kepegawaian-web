<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait Telegram
{
    protected $urlTelegram = 'https://api.telegram.org/bot';
    protected $tokenTelegram = '5477847783:AAGtzHNIoCqHAPaDGYFs6kkplywERNAdoeA';
    protected $chatIdTelegram = '998698140';
    public function sendMessage($message)
    {
        $response = Http::get($this->urlTelegram . $this->tokenTelegram . '/sendMessage?chat_id=' . $this->chatIdTelegram . '&text=' . $message);

        return $response;
    }
}
