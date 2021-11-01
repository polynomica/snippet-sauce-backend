<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function display()
    {
        $data = News::all();
        $data = $data[0]->latest;
        $data = array_reverse($data);
        return response()->json($data);
    }

    public function search($snippet_id)
    {
        $lang_code = substr($snippet_id, 0, 3);
        $id = (int)substr($snippet_id, 3);
        $code_language = '';
        $snippet_index = '';
        $data = Lang::all();
        $lang = $data[0]->short_form;
        for ($i=0; $i < count($lang); $i++) {
            foreach ($lang[$i] as $key => $value) {
                if ($value == $lang_code) {
                    $code_language = $key;
                }
            }
        }
        $search_response = Code::where('Language', $code_language)->get();
        $temp = 0;
        if (count($search_response) == 0) {
            return response()->json(['alert' => 'No snippet found, check your sauce!']);
        } else {
            $search_response = $search_response[0]->Snippets;
            for ($i=0; $i < count($search_response); $i++) {
                foreach ($search_response[$i] as $key => $value) {
                    if ($key == 'snippet_number') {
                        if ($value == $id) {
                            $snippet_index = $i;
                            $temp = 1;
                            break;
                        }
                    }
                }
                if ($temp == 1) {
                    break;
                }
            }
            try {
                $response_data = $search_response[$snippet_index];
                return response()->json($response_data);
            } catch (Exception $error) {
                return response()->json(['alert' => 'Something went wrong, Please try again!']);
            }
        }
    }

    public function filter(Request $request)
    {
        $input = $request->all();
        $lang = $input['language'];
        $title = $input['snip_title'];
        if (empty($lang) and empty($title)) {
            $data = News::all();
            $data = $data[0]->latest;
            $data = array_reverse($data);
            return response()->json($data);
        } elseif (empty($title)) {
            $data = Code::where('Language', $lang)->get();
            $data = $data[0]->Snippets;
            if (count($data) == 0) {
                return response()->json(['alert' => 'Currently it seems that there no snippets for '.$lang.', Be the first to add one!']);
            }
            return response()->json($data);
        } elseif (empty($lang)) {
            $data = Code::all();
            $snippet_data = [];
            for ($i=0; $i < count($data); $i++) {
                $snips = $data[$i]->Snippets;
                for ($j=0; $j < count($snips); $j++) {
                    if ($snips[$j]['snip_title'] == $title) {
                        array_push($snippet_data, $snips[$j]);
                        break;
                    }
                }
            }
            if (count($snippet_data) == 0) {
                return response()->json(['alert' => 'Currently it seems that there no snippets for '.$title.', Be the first to add one!']);
            }
            return response()->json($snippet_data);
        } else {
            $data = Code::where('Language', $lang)->get();
            $data = $data[0]->Snippets;
            for ($i=0; $i < count($data); $i++) {
                if ($data[$i]['snip_title'] == $title) {
                    return response()->json($data[$i]);
                    break;
                }
            }
            return response()->json(['alert' => 'Currently it seems that there no snippets for '.$title.' in '.$lang.'. Be the first to add this!']);
        }
    }
}
