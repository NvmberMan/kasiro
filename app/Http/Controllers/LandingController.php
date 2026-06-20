<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $templates = Template::published()->get();

        return view('landing', compact('templates'));
    }
}
