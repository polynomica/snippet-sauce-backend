<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Lang;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    /**
     * Get count of total Snippets
     *
     * @return void
     */
    public function total_snippets()
    {
        $count = Code::count();
        return response()->json(
            [
                'status' => true,
                'total_snippets' => $count
            ]
        );
    }

    /**
     * Display all Snippets
     *
     * @return void
     */
    public function display()
    {
        $accepted_fields = [
            'snippet_id',
            'snippet_language',
            'snippet_title',
            'snippet_thumbnail',
            'snippet_timestamp',
            'snippet_author',
            'author_pic',
            'author_bio',
        ];
        $data = Code::select($accepted_fields)->orderBy('updated_at', 'desc')->limit(30)->get();
        if (count($data) == 0) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Currently it seems that there no snippets, new snippets will be added soon!'
                ]
            );
        }
        return response()->json(
            [
                'status' => true,
                'snippet_data' => $data
            ]
        );
    }

    /**
     * Search Snippets by snippet_id
     *
     * @param  mixed $snippet_id
     * @return void
     */
    public function search($snippet_id)
    {
        $accepted_fields = [
            'snippet_id',
            'snippet_language',
            'snippet_title',
            'snippet_code',
            'snippet_description',
            'snippet_tag',
            'snippet_seo',
            'snippet_timestamp',
            'snippet_demo_url',
            'snippet_blog',
            'snippet_author',
            'author_pic',
            'author_bio',
        ];

        $data = Code::select($accepted_fields)->where('snippet_id', $snippet_id)->first();
        if (empty($data)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No snippet found, check your sauce!'
                ]
            );
        } else {
            return response()->json(
                [
                    'status' => true,
                    'snippet_data' => $data
                ]
            );
        }
    }

    /**
     * Filter Snippets by Language
     *
     * @param  mixed $request
     * @return void
     */
    public function filter(Request $request)
    {
        $input = $request->all();

        // Two possible conditions based on given inputs
        if (!isset($input['language'])) {
            $data = $this->display();
            return $data;
        } else {
            $filtered_language_data = Lang::select('language_name', 'description', 'logo')->where('language_name', $input['language'])->first();

            $accepted_fields = [
                'snippet_id',
                'snippet_language',
                'snippet_title',
                'snippet_seo',
                'snippet_thumbnail',
                'snippet_timestamp',
                'snippet_author',
                'author_pic',
                'author_bio',
            ];
            $data = Code::select($accepted_fields)->where('snippet_language', $input['language'])->get();

            if (!isset($data)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => "Currently it seems that there no snippets for {$input['language']}, Be the first to add one!",
                        'lang_data' => $filtered_language_data
                    ]
                );
            } else {
                return response()->json(
                    [
                        'status' => true,
                        'snippet_data' => $data,
                        'lang_data' => $filtered_language_data
                    ]
                );
            }
        }
    }

    public function title_search($title)
    {
        return true;
        // $data = Code::all();
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
        //     return response()->json(
        //         [
        //             'status' => false,
        //             'message' => 'Currently it seems that there no snippets for ' . $title . ', Be the first to add one!'
        //         ]
        //     );
        // }

        // return response()->json(
        //     [
        //         'status' => true,
        //         'snippet_data' => $snippet_data
        //     ]
        // );
    }
}
