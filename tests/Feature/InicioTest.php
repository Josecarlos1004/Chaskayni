<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InicioTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_inicial_se_muestra(): void
    {
        $respuesta = $this->get('/');

        $respuesta->assertOk();
    }
}
