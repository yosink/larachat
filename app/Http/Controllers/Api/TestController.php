<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function index()
    {
        $arr = ['id'=>1,'name'=>'name'];
        echo json_encode($arr);
        echo '<br/>';
        echo serialize($arr);

    }
}
