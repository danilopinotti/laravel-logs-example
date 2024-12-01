<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CepService
{
    public function getCep(string $cep): array
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) !== 8) {
            return ['erro' => 'CEP inválido'];
        }

        return Http::get("https://viacep.com.br/ws/{$cep}/json/")
            ->json();
    }
}
