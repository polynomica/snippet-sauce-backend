<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\News;
use App\Models\Code;
use App\Models\Lang;
use App\Models\Allot;
use Illuminate\Http\Request;

class LangController extends Controller
{
    /**
     * Get all Languages
     *
     * @author Hetarth Shah
     * @return void
     */
    public function get_languages()
    {
        $languages = Lang::select('language_name')
            ->pluck('language_name')
            ->toArray();
        $languages = array_values(array_filter($languages));
        return response()->json(
            [
                'status' => true,
                'languages' => $languages
            ]
        );
    }

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
                    'logo' => $input['lang_logo']
                ];
                $allotted_id = [
                    'language' => $input['language_name'],
                    'allotted' => [],
                ];

                Lang::create($data);
                Allot::create($allotted_id);
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

    public function update_language(Request $request, $previous_language)
    {
        $input = $request->all();
        $data = Lang::all();
        $code_data = Code::where('Language', $previous_language)->get();
        $latest_data = News::all();
        $languages = $data[0]->Languages;
        $thumbnail = $data[0]->thumbnail;
        $short_form = $data[0]->short_form;
        $logo = $data[0]->logo;
        $description = $data[0]->description;
        $code_data = $code_data[0]->Snippets;
        $latest_data = $latest_data[0]->latest;

        // Checking if entered Language already exists
        if ($previous_language != $input['language_name'] and in_array($input['language_name'], $languages)) {
            return response()->json(
                [
                    'message' => 'Language already exists.'
                ]
            );
        } else {
            try {
                // Finding index of previous language and thumbnail and updating the array with new data
                $languages[array_search($previous_language, $languages)] = $input['language_name'];
                for ($i = 0; $i < count($thumbnail); $i++) {
                    foreach ($thumbnail[$i] as $key => $value) {
                        if ($key == $previous_language) {
                            unset($thumbnail[$i][$key]);
                            $thumbnail[$i][$input['language_name']] = $input['thumbnail'];
                        }
                    }
                }

                for ($i = 0; $i < count($short_form); $i++) {
                    foreach ($short_form[$i] as $key => $value) {
                        if ($key == $previous_language) {
                            unset($short_form[$i][$key]);
                            $short_form[$i][$input['language_name']] = $value;
                        }
                    }
                }

                for ($i = 0; $i < count($logo); $i++) {
                    foreach ($logo[$i] as $key => $value) {
                        if ($key == $previous_language) {
                            unset($logo[$i][$key]);
                            $logo[$i][$input['language_name']] = $input['lang_logo'];
                        }
                    }
                }

                for ($i = 0; $i < count($description); $i++) {
                    foreach ($description[$i] as $key => $value) {
                        if ($key == $previous_language) {
                            unset($description[$i][$key]);
                            $description[$i][$input['language_name']] = $input['lang_desc'];
                        }
                    }
                }

                for ($i = 0; $i < count($code_data); $i++) {
                    if ($code_data[$i]['snippet_language'] == $previous_language) {
                        $code_data[$i]['snippet_language'] = $input['language_name'];
                        $code_data[$i]['snippet_thumbnail'] = $input['thumbnail'];
                    }
                }

                for ($i = 0; $i < count($latest_data); $i++) {
                    if ($latest_data[$i]['snippet_language'] == $previous_language) {
                        $latest_data[$i]['snippet_language'] = $input['language_name'];
                        $latest_data[$i]['snippet_thumbnail'] = $input['thumbnail'];
                    }
                }

                // Peforming changes in DB
                Lang::where('Languages', 'exists', true)->update(
                    [
                        'Languages' => $languages,
                        'short_form' => $short_form,
                        'thumbnail' => $thumbnail,
                        'logo' => $logo,
                        'description' => $description
                    ]
                );
                Allot::where('Language', $previous_language)->update(
                    [
                        'Language' => $input['language_name']
                    ]
                );
                News::where('latest', 'exists', true)->update(
                    [
                        'latest' => $latest_data
                    ]
                );
                Code::where('Language', $previous_language)->update(
                    [
                        'Language' => $input['language_name'],
                        'Snippets' => $code_data
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
