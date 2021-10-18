<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    public function display()
    {
        // Code::create(['Languages' => [['Python' => [['id' => 1, 'code' => 'code1']]]]]);
        $data = Code::all();
        $data = $data[0]->Languages[0]['Python'];
        array_push($data, [
            'id' => 3,
            'code' => 'code3'
        ]);
        return response()->json($data);
    }
}
