<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class JarvisController extends Controller
{
    public function database()
    {
        return Storage::download('database.sqlite');
    }
}
