<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $instance = resolve('swoolehandler');
        dd($instance);
    }
}
