<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class JarvisController extends Controller
{
    public function database()
    {
        return Storage::download('database.sqlite');
    }
}
