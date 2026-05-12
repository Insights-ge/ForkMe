<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GeneralSettings $settings): View
    {
        return view('welcome');
    }
}
