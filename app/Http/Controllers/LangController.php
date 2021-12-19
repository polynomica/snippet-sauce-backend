<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\News;
use App\Models\Code;
use App\Models\Lang;
use App\Models\Allot;
use Illuminate\Http\Request;

class LangController extends Controller
{
    public function get_languages()
    {
        $languages = Lang::all();
        $languages = $languages[0]->Languages;
        return response()->json([
            'status' => true,
            'languages' => $languages
        ]);
    }

    public function language_details($language)
    {
        $data = Lang::all();
        $languages = $data[0]->Languages;
        $short_form = $data[0]->short_form;
        $thumbnail = $data[0]->thumbnail;
        if ( in_array($language, $languages) ) {
            $lang_short_form = '';
            $lang_thumbnail = '';
            for ($i=0; $i < count($short_form); $i++) {
                foreach ($short_form[$i] as $key => $value) {
                    if ($key == $language) {
                        $lang_short_form = $value;
                        break;
                    }
                }
            }
            for ($i=0; $i < count($thumbnail); $i++) {
                foreach ($thumbnail[$i] as $key => $value) {
                    if ($key == $language) {
                        $lang_thumbnail = $value;
                        break;
                    }
                }
            }
            return response()->json([
                'status' => true,
                'language' => $language,
                'short_form' => $lang_short_form,
                'thumbnail' => $lang_thumbnail
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "No such language exists."
            ]);
        }
        
    }

    public function add_language(Request $request)
    {
        //Formatting input data
        $input = $request->all();
        $data = Lang::all();
        $lang_data = $data[0]->Languages;
        $create_data = [
            'Language' => $input['language_name'],
            'Snippets' => [],
        ];
        $allotted_id = [
            'Language' => $input['language_name'],
            'allotted' => [],
        ];
        $short_form = [
            $input['language_name'] => $input['short_form'],
        ];
        $thumbnail = [
            $input['language_name'] => $input['thumbnail'],
        ];
        $snip_short_form = $data[0]->short_form;

        for ($i=0; $i < count($snip_short_form); $i++) {
            foreach ($snip_short_form[$i] as $key => $value) {
                if ($input['short_form'] == $value) {
                    return response()->json([
                        'message' => 'Short form already exists, Please choose a new short form.'
                    ]);
                }
            }
        }

        if ( in_array($input['language_name'], $lang_data) ) {
            return response()->json([
                'status' => false,
                'message' => 'Language already exists.'
            ]);
        } else {
            try {
                Lang::where('Languages', 'exists', true)->push('Languages', $input['language_name']);
                Lang::where('Languages', 'exists', true)->push('short_form', $short_form);
                Lang::where('Languages', 'exists', true)->push('thumbnail', $thumbnail);
                Code::create($create_data);
                Allot::create($allotted_id);
                return response()->json([
                    'status' => true,
                    'message' => 'Language added successfully.'
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
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
        $code_data = $code_data[0]->Snippets;
        $latest_data = $latest_data[0]->latest;

        //Checking if entered Language already exists
        if ( $previous_language != $input['language_name'] and in_array($input['language_name'], $languages) ) {
            return response()->json([
                'message' => 'Language already exists.'
            ]);
        } else {
            try {
                //Finding index of previous language and thumbnail and updating the array with new data
                $languages[ array_search($previous_language, $languages) ] = $input['language_name'];
                for ($i=0; $i < count($thumbnail); $i++) {
                    foreach ($thumbnail[$i] as $key => $value) {
                        if ($key == $previous_language) {
                            unset($thumbnail[$i][$key]);
                            $thumbnail[$i][ $input['language_name'] ] = $input['thumbnail'];
                        }
                    }
                }

                for ($i=0; $i < count($short_form); $i++) {
                    foreach ($short_form[$i] as $key => $value) {
                        if ($key == $previous_language) {
                            unset($short_form[$i][$key]);
                            $short_form[$i][ $input['language_name'] ] = $value;
                        }
                    }
                }

                for ($i=0; $i < count($code_data); $i++) {
                    if ($code_data[$i]['snippet_language'] == $previous_language) {
                        $code_data[$i]['snippet_language'] = $input['language_name'];
                        $code_data[$i]['snippet_thumbnail'] = $input['thumbnail'];
                    }
                }

                for ($i=0; $i < count($latest_data); $i++) {
                    if ($latest_data[$i]['snippet_language'] == $previous_language) {
                        $latest_data[$i]['snippet_language'] = $input['language_name'];
                        $latest_data[$i]['snippet_thumbnail'] = $input['thumbnail'];
                    }
                }

                //Peforming changes in DB
                Lang::where('Languages', 'exists', true)->update([
                    'Languages' => $languages,
                    'short_form' => $short_form,
                    'thumbnail' => $thumbnail
                ]);
                Allot::where('Language', $previous_language)->update([
                    'Language' => $input['language_name']
                ]);
                News::where('latest', 'exists', true)->update([
                    'latest' => $latest_data
                ]);
                Code::where('Language', $previous_language)->update([
                    'Language' => $input['language_name'],
                    'Snippets' => $code_data
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Data updated successfully.'
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        }
    }
}
