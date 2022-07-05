<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Code;
use App\Models\Lang;
use Illuminate\Http\Request;

class LangController extends Controller
{
    /**
     * Get All Languages
     *
     * @author Hetarth Shah
     * @return void
     */
    public function get_languages()
    {
        $languages = Lang::select('language_name')->pluck('language_name');
        return response()->json(
            [
                'status' => true,
                'languages' => $languages
            ]
        );
    }

    /**
     * Get Language Details
     *
     * @param  mixed $language
     * @return void
     */
    public function language_details($language)
    {
        $accepted_fields = [
            "short_form",
            "thumbnail",
            "description",
            "logo",
        ];
        $data = Lang::select($accepted_fields)->where('language_name', $language)->first();
        if (isset($data)) {
            return response()->json(
                [
                    'status' => true,
                    'language' => $language,
                    'short_form' => $data->short_form,
                    'thumbnail' => $data->thumbnail,
                    'description' => $data->description,
                    'logo' => $data->logo,
                ]
            );
        } else {
            return response()->json(
                [
                    'status' => false,
                    'message' => "No such language exists."
                ]
            );
        }
    }

    /**
     * Add Language
     *
     * @param  mixed $request
     * @return void
     */
    public function add_language(Request $request)
    {
        $input = $request->all();

        $exists = Lang::where('language_name', $input['language_name'])
            ->orWhere('short_form', $input['short_form'])
            ->first();
        if (isset($exists)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Language Name or Short form already exists.'
                ]
            );
        } else {
            try {
                $data = [
                    'language_name' => $input['language_name'],
                    'short_form' => $input['short_form'],
                    'thumbnail' => $input['thumbnail'],
                    'description' => $input['lang_desc'],
                    'logo' => $input['lang_logo'],
                    'allotted' => [],
                ];

                Lang::create($data);
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Language added successfully.'
                    ]
                );
            } catch (Throwable $error) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]
                );
            }
        }
    }

    /**
     * Update Language
     *
     * @param  mixed $request
     * @param  mixed $previous_language
     * @return void
     */
    public function update_language(Request $request, $previous_language)
    {
        $input = $request->all();
        $exists = Lang::where('language_name', $input['language_name'])->first();

        // Checking if entered Language already exists
        if ($previous_language != $input['language_name'] and isset($exists)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Language already exists.'
                ]
            );
        } else {
            try {
                $update_data = [
                    "language_name" => $input['language_name'],
                    "thumbnail" => $input['thumbnail'],
                    "description" => $input['lang_desc'],
                    "logo" => $input['lang_logo'],
                ];

                // Peforming changes in DB
                Lang::where('language_name', $previous_language)->update($update_data);
                Code::where('snippet_language', $previous_language)->update(
                    [
                        'snippet_language' => $input['language_name'],
                        'snippet_thumbnail' => $input['thumbnail']
                    ]
                );
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Data updated successfully.'
                    ]
                );
            } catch (Throwable $error) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]
                );
            }
        }
    }
}
