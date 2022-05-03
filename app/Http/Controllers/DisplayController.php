<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use App\Models\Allot;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function total_snippets()
    {
        $data = Allot::select('allotted')->where('allotted.0', 'exists', true)->get();
        $total = 0;
        foreach ($data as $value) {
            $total = $total + count( $value['allotted'] );
        }
        return response()->json([
            'status' => true,
            'total_snippets' => $total
        ]);
    }

    public function display()
    {
        $accepted_fields = [
            'latest.snippet_id',
            'latest.snippet_language',
            'latest.snippet_title',
            'latest.snippet_thumbnail',
            'latest.snippet_timestamp',
            'latest.snippet_author',
            'latest.author_pic',
            'latest.author_bio',
        ];
        $data = News::select($accepted_fields)->first();
        $data = $data->latest;

        if (count($data) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Currently it seems that there no snippets, new snippets will be added soon!'
            ]);
        }

        $data = array_reverse($data);   //Arrange data by newest first
        return response()->json([
            'status' => true,
            'snippet_data' => $data
        ]);
    }

    public function search($snippet_id)
    {
        $accepted_fields = [
            'Snippets.snippet_id',
            'Snippets.snippet_language',
            'Snippets.snippet_title',
            'Snippets.snippet_code',
            'Snippets.snippet_description',
            'Snippets.snippet_tag',
            'Snippets.snippet_seo',
            'Snippets.snippet_timestamp',
            'Snippets.snippet_demo_url',
            'Snippets.snippet_blog',
            'Snippets.snippet_author',
            'Snippets.author_pic',
            'Snippets.author_bio',
        ];

        // Using $elemMatch in Raw queries
        $search_response = Code::select($accepted_fields)->whereRaw([
            'Snippets' => [
                '$elemMatch' => [
                    'snippet_id' => $snippet_id
                ]
            ]
        ])->first();

        if (empty($search_response)) {
            return response()->json([
                'status' => false,
                'message' => 'No snippet found, check your sauce!'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'snippet_data' => $search_response->Snippets[0]
            ]);
        }
    }

    public function filter(Request $request)
    {
        $input = $request->all();
        $lang = $input['language'];

        //Two possible conditions based on given inputs
        if (empty($lang) or $lang == '') {

            $accepted_fields = [
                'latest.snippet_id',
                'latest.snippet_language',
                'latest.snippet_title',
                'latest.snippet_thumbnail',
                'latest.snippet_timestamp',
                'latest.snippet_author',
                'latest.author_pic',
                'latest.author_bio',
            ];
            $data = News::select($accepted_fields)->first();
            $data = $data->latest;
            $data = array_reverse($data);

            return response()->json([
                'status' => true,
                'snippet_data' => $data
            ]);
        } else {
            $language_data = Lang::select("logo.{$lang}", "description.{$lang}")->first();
            $lang_logo = head( array_filter( $language_data->logo ) );
            $lang_desc = head( array_filter( $language_data->description ) );
            $lang_logo = $lang_logo[$lang];
            $lang_desc = $lang_desc[$lang];
            $filtered_language_data = [
                'name' => $lang,
                'logo' => $lang_logo,
                'desc' => $lang_desc,
            ];

            $accepted_fields = [
                'Snippets.snippet_id',
                'Snippets.snippet_language',
                'Snippets.snippet_title',
                'Snippets.snippet_seo',
                'Snippets.snippet_thumbnail',
                'Snippets.snippet_timestamp',
                'Snippets.snippet_author',
                'Snippets.author_pic',
                'Snippets.author_bio',
            ];
            $lang_data = Code::select($accepted_fields)->where('Language', $lang)->first();

            if (empty($lang_data)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Currently it seems that there no snippets for '.$lang.', Be the first to add one!',
                    'lang_data' => $filtered_language_data
                ]);
            } else {
                $data = $lang_data->Snippets;
                if (empty($data)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Currently it seems that there no snippets for '.$lang.', Be the first to add one!',
                        'lang_data' => $filtered_language_data
                    ]);
                } else {
                    $data = $lang_data->Snippets;
                    return response()->json([
                        'status' => true,
                        'snippet_data' => $data,
                        'lang_data' => $filtered_language_data
                    ]);
                }
            }
        }
    }

    public function title_search($title)
    {
        $data = Code::all();
        // $snippet_data = [];
        // echo $data;
        // $data = data_get($data, "*.Snippets.*.snippet_title");
        // $data = $data[0];
        // $count = Str::contains($data, ['A']);
        // dd($count, $data);

        // for ($i=0; $i < count($data); $i++) {
        //     $snips = $data[$i]->Snippets;
        //     for ($j=0; $j < count($snips); $j++) {
        //         if ($snips[$j]['snippet_title'] == $title) {
        //             array_push($snippet_data, $snips[$j]);
        //             break;
        //         }
        //     }
        // }
        // if (count($snippet_data) == 0) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Currently it seems that there no snippets for '.$title.', Be the first to add one!'
        //     ]);
        // }

        // return response()->json([
        //     'status' => true,
        //     'snippet_data' => $snippet_data
        // ]);
    }
}
