<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function extract_info($snippet_id)
    {
        //Extracting language short form and id from input
        $lang_code = substr($snippet_id, 0, 3);
        $id = (int)substr($snippet_id, 3);
        $code_language = '';
        $return_data = [];
        
        //Finding Language from its short form
        $data = Lang::all();
        $lang = $data[0]->short_form;
        for ($i=0; $i < count($lang); $i++) {
            foreach ($lang[$i] as $key => $value) {
                if ($value == $lang_code) {
                    $code_language = $key;
                }
            }
        }
        $return_data[0] = $id;
        $return_data[1] = $code_language;
        return $return_data;
    }

    public function display()
    {
        $data = News::all();
        $data = $data[0]->latest;
        
        if (count($data) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Currently it seems that there no snippets, new snippets will be added soon!'
            ]);
        }

        $data = array_reverse($data);   //Arrange data by newest first
        $removed_fields = [
            'snippet_number',
            'snippet_code',
            'snippet_description',
            'snippet_tag',
            'snippet_seo',
            'snippet_demo_url',
            'snippet_blog'
        ];
        for ($i=0; $i < count($data); $i++) {
            foreach ($data[$i] as $key => $value) {
                if (in_array($key, $removed_fields)) {
                    unset($data[$i][$key]);
                }
            }
        }
        return response()->json([
            'status' => true,
            'snippet_data' => $data
        ]);
    }

    public function search($snippet_id)
    {
        $data = $this->extract_info($snippet_id);
        $id = $data[0];
        $code_language = $data[1];
        $search_response = Code::where('Language', $code_language)->get();

        //Finding the snippet from language based on its id, as every snippet will always be unique break the loop once we find it to reduce time complexity
        if (count($search_response) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'No snippet found, check your sauce!'
            ]);
        } else {
            $temp = 0;
            $snippet_index = '';
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
                return response()->json([
                    'status' => true,
                    'snippet_data' => $response_data
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        }
    }

    public function filter(Request $request)
    {
        $input = $request->all();
        $lang = $input['language'];

        //Two possible conditions based on given inputs
        if (empty($lang)) {
            $data = News::all();
            $data = $data[0]->latest;
            $data = array_reverse($data);

            $removed_fields = [
                'snippet_number',
                'snippet_code',
                'snippet_description',
                'snippet_tag',
                'snippet_seo',
                'snippet_demo_url',
                'snippet_blog'
            ];
            for ($i=0; $i < count($data); $i++) {
                foreach ($data[$i] as $key => $value) {
                    if (in_array($key, $removed_fields)) {
                        unset($data[$i][$key]);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'snippet_data' => $data
            ]);
        } else {
            $lang_data = Code::where('Language', $lang)->get();

            if (count($lang_data) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Currently it seems that there no snippets for '.$lang.', Be the first to add one!'
                ]);
            }

            $data = $lang_data[0]->Snippets;

            if (count($data) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Currently it seems that there no snippets for '.$lang.', Be the first to add one!'
                ]);
            }

            $removed_fields = [
                'snippet_number',
                'snippet_code',
                'snippet_description',
                'snippet_tag',
                'snippet_demo_url',
                'snippet_blog'
            ];
            for ($i=0; $i < count($data); $i++) {
                foreach ($data[$i] as $key => $value) {
                    if (in_array($key, $removed_fields)) {
                        unset($data[$i][$key]);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'snippet_data' => $data
            ]);
        }
    }

    public function title_search($title)
    {
        $data = Code::all();
        $snippet_data = [];

        for ($i=0; $i < count($data); $i++) {
            $snips = $data[$i]->Snippets;
            for ($j=0; $j < count($snips); $j++) {
                if ($snips[$j]['snippet_title'] == $title) {
                    array_push($snippet_data, $snips[$j]);
                    break;
                }
            }
        }
        if (count($snippet_data) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Currently it seems that there no snippets for '.$title.', Be the first to add one!'
            ]);
        }

        return response()->json([
            'status' => true,
            'snippet_data' => $snippet_data
        ]);
    }
}
