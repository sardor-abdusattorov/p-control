<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateCurrencyRates extends Command
{
    // Имя команды
    protected $signature = 'currency:update';

    // Описание команды
    protected $description = 'Обновляет курсы валют';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currencies = Currency::where('status', 1)->get();

        foreach ($currencies as $currency) {
            $url = 'https://cbu.uz/uz/arkhiv-kursov-valyut/json/' . $currency->short_name . '/';
            $response = Http::get($url);

            if ($response->failed()) {
                Log::error("Не удалось получить данные для валюты: " . $currency->short_name);
                continue;
            }

            $data = $response->json();

            if (empty($data)) {
                Log::error("Нет данных для валюты: " . $currency->short_name);
                continue;
            }

            $rateData = $data[0];

            $currency->value = $rateData['Rate'];
            $currency->updated_at = now();

            if (!$currency->save()) {
                Log::error("Не удалось сохранить обновления для валюты: " . $currency->short_name);
            } else {
                Log::info("Курс валюты обновлен: " . $currency->short_name . " - " . $currency->value);
            }
        }
        $this->info("Курсы валют успешно обновлены.");
    }
}
