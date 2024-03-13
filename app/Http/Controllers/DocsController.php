<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DocsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Docs');
    }
}
