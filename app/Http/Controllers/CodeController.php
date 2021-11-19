<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use App\Models\Allot;
use Illuminate\Http\Request;

class CodeController extends Controller
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

    public function update_latest($array)
    {
        $temp = News::all();
        $temp = $temp[0]->latest;
        $data = $array;

        //Adding in recently added data array
        if (count($temp) > 30) {
            array_shift($temp);
            array_push($temp, $data[0]);    //data = [ {} ], so to avoid adding nested object array we take only the object from the array
            try {
                News::where('latest', 'exists', true)->update([
                    'latest' => $temp
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Snippet added successfully.'
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        } else {
            try {
                News::where('latest', 'exists', true)->push('latest', $data);
                return response()->json([
                    'status' => true,
                    'message' => 'Snippet added successfully.'
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        }
    }

    public function delete_latest($snippet_id)
    {
        //Find and delete the snippet, if it exists in the recently added array
        $latest = News::all();
        $latest = $latest[0]->latest;
        for ($i=0; $i < count($latest); $i++) {
            if ($latest[$i]['snippet_id'] == $snippet_id) {
                unset($latest[$i]);
                $latest = array_values($latest);
                try {
                    News::where('latest', 'exists', true)->update([
                        'latest' => $latest
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Snippet removed successfully.'
                    ]);
                } catch (Exception $error) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]);
                }
                break;
            }
        }
    }

    public function create_snippet(Request $request)
    {
        //Preparing input data
        $input = $request->all();
        $languages = Lang::all();
        $short_form = $languages[0]->short_form;
        $thumbnail = $languages[0]->thumbnail;
        $languages = $languages[0]->Languages;
        $timestamp = Carbon::now();
        $timestamp = $timestamp->toISOString();
        $valid = [];

        for ($i=0; $i <= 999999; $i++) {
            array_push($valid, $i);
        }

        //Checking if language exists or not
        if ( in_array($input['snippet_language'], $languages) ) {
            $snippet_thumbnail = '';
            $string = '';

            for ($i=0; $i < count($thumbnail); $i++) {
                foreach ($thumbnail[$i] as $key => $value) {
                    if ($key == $input['snippet_language']) {
                        $snippet_thumbnail = $value;
                    }
                }
            }

            for ($i=0; $i < count($short_form); $i++) {
                foreach ($short_form[$i] as $key => $value) {
                    if ($key == $input['snippet_language']) {
                        $string = $value;
                    }
                }
            }
            $occupied = Allot::where('Language', $input['snippet_language'])->get();
            $occupied = $occupied[0]->allotted;
            $free_id = array_values( array_diff($valid, $occupied) );

            //Checking if all IDs are allotted or not
            if ($free_id == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'IDs have run out.'
                ]);
            }

            //Preparing the input data
            $rand_value = $free_id[ mt_rand(0, (count($free_id) - 1)) ];
            $snippet_id = $string.sprintf("%06s", $rand_value);
            $response = app('App\Http\Controllers\UserController')->author_details($input['snippet_author']);
            $response = $response->getData();
            if ( !($response->status) ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Check github username.'
                ]);
            }
            $author_pic = $response->author_avatar;
            $data = [
                [
                    'snippet_id' => $snippet_id,
                    'snippet_number' => $rand_value,
                    'snippet_language' => $input['snippet_language'],
                    'snippet_title' => $input['snippet_title'],
                    'snippet_code' => $input['snippet_code'],
                    'snippet_description' => $input['snippet_description'],
                    'snippet_tag' => $input['snippet_tag'],
                    'snippet_seo' => $input['snippet_seo'],
                    'snippet_thumbnail' => $snippet_thumbnail,
                    'snippet_timestamp' => $timestamp,
                    'snippet_demo_url' => $input['snippet_demo_url'],
                    'snippet_blog' => $input['snippet_blog'],
                    'snippet_author' => $input['snippet_author'],
                    'author_pic' => $author_pic,
                ]
            ];

            try {
                Code::where('Language', $input['snippet_language'])->push('Snippets', $data);
                Allot::where('Language', $input['snippet_language'])->push('allotted', $rand_value);
                $status = ( $this->update_latest($data) )->getData();
                if ($status->status) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Snippet added successfully.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]);
                }
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Language does not exists.'
            ]);
        }
    }

    public function update_snippet(Request $request)
    {
        $input = $request->all();
        $snippet_id = $input['snippet_id'];
        $extract_data = $this->extract_info($snippet_id);
        $id = $extract_data[0];
        $code_language = $extract_data[1];

        //Checking if updated language is same as previous language
        if ($code_language != $input['snippet_language']) {
            //If updated language is not equal to previous language then port the updated snippet to new language after deleting its instance from previous language
            try {
                $data = ( $this->create_snippet($request) )->getData();    //getData() is used to get contents of json response
                if ($data->status) {
                    $this->delete_snippet($snippet_id);
                    return response()->json([
                        'status' => true,
                        'message' => 'Snippet updated successfully.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]);
                }
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        } else {
            //Preparing updated data
            $info = Code::where('Language', $input['snippet_language'])->get();
            $info = $info[0]->Snippets;
            $timestamp = Carbon::now();
            $timestamp = $timestamp->toISOString();
            $response = app('App\Http\Controllers\UserController')->author_details($input['snippet_author']);
            $response = $response->getData();
            if ( !($response->status) ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Check github username.'
                ]);
            }
            $author_pic = $response->author_avatar;
            $updated_data = [
                [
                    'snippet_id' => $snippet_id,
                    'snippet_number' => $id,
                    'snippet_language' => $input['snippet_language'],
                    'snippet_title' => $input['snippet_title'],
                    'snippet_code' => $input['snippet_code'],
                    'snippet_description' => $input['snippet_description'],
                    'snippet_tag' => $input['snippet_tag'],
                    'snippet_seo' => $input['snippet_seo'],
                    'snippet_thumbnail' => $input['snippet_thumbnail'],
                    'snippet_timestamp' => $timestamp,
                    'snippet_demo_url' => $input['snippet_demo_url'],
                    'snippet_blog' => $input['snippet_blog'],
                    'snippet_author' => $input['snippet_author'],
                    'author_pic' => $author_pic,
                ]
            ];

            $temp = 0;
            for ($i=0; $i < count($info); $i++) {
                foreach ($info[$i] as $key => $value) {
                    if ($info[$i]['snippet_number'] == $id) {
                        unset($info[$i]);
                        $info[$i] = $updated_data[0];
                        $temp = 1;
                        break;
                    }
                }

                if ($temp == 1) {
                    break;
                }
            }

            try {
                Code::where('Language', $input['snippet_language'])->update([
                    'Snippets' => $info
                ]);
                $status_1 = ( $this->delete_latest($snippet_id) )->getData();
                $status_2 = ( $this->update_latest($updated_data) )->getData();
                if ($status_1->status == false or $status_2->status == false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Snippet updated successfully.'
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        }
    }

    public function delete_snippet($snippet_id)
    {
        $data = $this->extract_info($snippet_id);
        $id = $data[0];
        $code_language = $data[1];
        $search_response = Code::where('Language', $code_language)->get();

        //Checking if 
        if (count($search_response) == 0) {
            return response()->json([
                'message' => 'No snippet found, check your sauce!'
            ]);
        } else {
            $temp = 0;
            $snippet_index = '';
            $search_response = $search_response[0]->Snippets;

            //Finding the snippet from language based on its id, as every snippet will always be unique break the loop once we find it to reduce time complexity
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
            unset($search_response[$snippet_index]);
            $search_response = array_values($search_response);

            try {
                Code::where('Language', $code_language)->update([
                    'Snippets' => $search_response
                ]);
            } catch (Exception $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        }

        //Free up the unique Id of the deleted snippet
        try {
            $status = ( $this->delete_latest($snippet_id) )->getData();
            if ($status->status == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
            Allot::where('Language', $code_language)->pull('allotted', $id);
            return response()->json([
                'status' => true,
                'message' => 'Snippet deleted successfully.'
            ]);
        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, Please try again!'
            ]);
        }
    }
}
