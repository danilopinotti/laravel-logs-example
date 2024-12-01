<?php

namespace App\Http\Controllers;

use App\Services\CepService;

class GetCepController
{
    public function __invoke(string $cep)
    {
        return (new CepService())
            ->getCep($cep);
    }
}
