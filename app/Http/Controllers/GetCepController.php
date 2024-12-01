<?php

namespace App\Http\Controllers;

use App\Services\CepService;
use Illuminate\Support\Facades\Log;

class GetCepController
{
    public function __invoke(string $cep)
    {
        Log::info('Consulta de CEP', ['cep' => $cep]);

        return (new CepService())
            ->getCep($cep);
    }
}
