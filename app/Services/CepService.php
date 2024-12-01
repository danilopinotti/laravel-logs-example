<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CepService
{
    public function getCep(string $cep): array
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) !== 8) {
            Log::error('CEP invalido', ['cep' => $cep]);
            return ['erro' => 'CEP invalido'];
        }

        return Http::get("https://viacep.com.br/ws/{$cep}/json/")
            ->json();
    }
}
