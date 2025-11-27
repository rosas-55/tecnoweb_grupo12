<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reportes/Index');
    }
}


