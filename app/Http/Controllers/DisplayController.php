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
            'snippet_author',
            'author_pic',
            'author_bio',
        ];
        $data = Code::select($accepted_fields)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();
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
            'snippet_thumbnail',
            'snippet_tag',
            'snippet_seo',
            'snippet_demo_url',
            'snippet_blog',
            'snippet_author',
            'author_pic',
            'author_bio',
        ];

        $data = Code::select($accepted_fields)->where('snippet_id', $snippet_id)->first();
        if (!isset($data)) {
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
        $filtered_language_data = Lang::with('snippets')->select('language_name', 'description', 'logo')->where('language_name', $input['language'])->first();

        if (!isset($filtered_language_data)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => "Currently it seems that {$input['language']} language doesn't exists for us. Snap it into existence by raising an issue at our service repo."
                ]
            );
        }

        $lang_data = [
            "language_name" => $filtered_language_data->language_name,
            "description" => $filtered_language_data->description,
            "logo" => $filtered_language_data->logo
        ];

        if (count($filtered_language_data->snippets) == 0) {
            return response()->json(
                [
                    'status' => true,
                    'message' => "Currently it seems that there no snippets for {$input['language']}, Be the first to add one!",
                    'lang_data' => $lang_data
                ]
            );
        } else {
            return response()->json(
                [
                    'status' => true,
                    'snippet_data' => $filtered_language_data->snippets,
                    'lang_data' => $lang_data
                ]
            );
        }
    }

    /**
     * Get Similar Snippets for a Language
     *
     * @param  mixed $language
     * @return void
     */
    public function similar_snippets($language)
    {
        $data = Code::select('snippet_id', 'snippet_title')
            ->where('snippet_language', $language)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(
            [
                'status' => true,
                'snippet_data' => $data ?? [],
            ]
        );
    }

    /**
     * Title Search
     *
     * @param  mixed $title
     * @return void
     */
    public function title_search($title)
    {
        $accepted_fields = [
            'snippet_id',
            'snippet_language',
            'snippet_title',
            'snippet_thumbnail',
            'snippet_author',
            'author_pic',
            'author_bio',
        ];

        $snippet_data = Code::select($accepted_fields)
            ->where('snippet_title', 'like', "%{$title}%")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(
            [
                'status' => true,
                'snippet_data' => $snippet_data ?? []
            ]
        );
    }
}
