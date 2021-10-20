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
        // $data = $data[0]->Languages[0]['Python'];
        // array_push($data, [
        //     'id' => 3,
        //     'code' => 'code3'
        // ]);
        return response()->json($data);
    }

    public function filter(Request $request)
    {
        // $input = $request->all();
        $language = $request->language;
        $language = 'h';
        // $snippet_title = 'h';
        $snippet_title = $request->snippet_title;
        $data = Code::all();
        if (empty($language) && empty($snippet_title)) {
            return response()->json($data);
        } elseif (empty($snippet_title)) {
            $data = $data[0]->Languages;
            return response()->json($data);
        } elseif (empty($language)) {
            return response()->json($data);
        } else {
            return response()->json($data);
        }
    }
}
