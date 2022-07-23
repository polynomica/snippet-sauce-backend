<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Lang;
use Illuminate\Http\Request;
use Throwable;

class CodeController extends Controller
{
    /**
     * Extract Info from snippet_id
     *
     * @param  mixed  $snippet_id
     * @return void
     */
    public function extract_info($snippet_id)
    {
        // Extracting language short form and id from input
        $lang_code = substr($snippet_id, 0, 3);
        $id = (int) substr($snippet_id, 3);

        // Finding Language from its short form
        $data = Lang::select('language_name')->where('short_form', $lang_code)->first();

        return [$id, $data->language_name];
    }

    /**
     * Create Snippet
     *
     * @param  mixed  $request
     * @return void
     */
    public function create_snippet(Request $request)
    {
        $input = $request->all();
        $lang_data = Lang::where('language_name', $input['snippet_language'])->first();

        // Checking if language exists or not
        if (! isset($lang_data)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Language does not exists.',
                ]
            );
        } else {
            $snippet_thumbnail = $lang_data->thumbnail;
            $short_form = $lang_data->short_form;

            $occupied = Lang::select('allotted')->where('language_name', $input['snippet_language'])->first();
            $occupied = $occupied->allotted;
            $valid = range(0, 999999);
            $free_id = array_values(array_diff($valid, $occupied));

            // Checking if all IDs are allotted or not
            if (count($free_id) == 0) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'IDs have run out.',
                    ]
                );
            }

            // Preparing the input data
            $rand_value = $free_id[mt_rand(0, (count($free_id) - 1))];
            $snippet_id = $short_form.sprintf('%06s', $rand_value);
            $response = app(UserController::class)->author_details($input['snippet_author']);
            $response = $response->getData();
            if (! ($response->status)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Check github username.',
                    ]
                );
            }
            $author_pic = $response->author_avatar;
            $author_bio = $response->author_bio;
            $data = [
                'snippet_id' => $snippet_id,
                'snippet_language' => $input['snippet_language'],
                'snippet_title' => $input['snippet_title'],
                'snippet_code' => $input['snippet_code'],
                'snippet_description' => $input['snippet_description'],
                'snippet_tag' => $input['snippet_tag'],
                'snippet_seo' => $input['snippet_seo'],
                'snippet_thumbnail' => $snippet_thumbnail,
                'snippet_demo_url' => $input['snippet_demo_url'],
                'snippet_blog' => $input['snippet_blog'],
                'snippet_author' => $input['snippet_author'],
                'author_pic' => $author_pic,
                'author_bio' => $author_bio,
            ];

            try {
                Code::create($data);
                Lang::where('language_name', $input['snippet_language'])->push('allotted', $rand_value);

                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Snippet added successfully.',
                    ]
                );
            } catch (Throwable $error) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!',
                    ]
                );
            }
        }
    }

    /**
     * Update Snippet
     *
     * @param  mixed  $request
     * @param  mixed  $snippet_id
     * @return void
     */
    public function update_snippet(Request $request, $snippet_id)
    {
        $input = $request->all();

        // Preparing updated data
        $response = app(UserController::class)->author_details($input['snippet_author']);
        $response = $response->getData();
        if (! ($response->status)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Check github username.',
                ]
            );
        }
        $author_pic = $response->author_avatar;
        $author_bio = $response->author_bio;

        $updated_data = [
            'snippet_title' => $input['snippet_title'],
            'snippet_code' => $input['snippet_code'],
            'snippet_description' => $input['snippet_description'],
            'snippet_tag' => $input['snippet_tag'],
            'snippet_seo' => $input['snippet_seo'],
            'snippet_demo_url' => $input['snippet_demo_url'],
            'snippet_blog' => $input['snippet_blog'],
            'snippet_author' => $input['snippet_author'],
            'author_pic' => $author_pic,
            'author_bio' => $author_bio,
        ];

        try {
            Code::where('snippet_id', $snippet_id)->update($updated_data);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Snippet updated successfully.',
                ]
            );
        } catch (Throwable $error) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!',
                ]
            );
        }
    }

    /**
     * Delete Snippet
     *
     * @param  mixed  $snippet_id
     * @return void
     */
    public function delete_snippet($snippet_id)
    {
        try {
            $data = $this->extract_info($snippet_id);
            $id = $data[0];
            $code_language = $data[1];

            // Free up the unique Id of the deleted snippet
            Lang::where('language_name', $code_language)->pull('allotted', $id);
            Code::where('snippet_id', $snippet_id)->delete();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Snippet deleted successfully.',
                ]
            );
        } catch (Throwable $error) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!',
                ]
            );
        }
    }
}
