<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Closure;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function get(Request $request)
    {
        try {
            $keyword = $request->query("keyword") ?? "";
            $posts = Post::with(['createdUser', 'updatedUser'])
                ->orderBy('created_at', 'DESC')
                ->where(function (Builder $query) use ($keyword) {
                    $query->where('title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('description', 'LIKE', '%' . $keyword . '%');
                })
                ->get();
            return response()->json([
                "message" => "All Posts",
                "data" => $posts,
                "keyword" => $keyword
            ]);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function getSinglePost(Request $request)
    {
        try {
            $postId = $request->id;
            $post = Post::with(['createdUser', 'updatedUser'])->find($postId);
            if ($post) {
                return response()->json([
                    "message" => "Single Post",
                    "data" => $post,
                ]);
            } else {
                return response()->json([
                    "message" => "Invalid Post Id",
                    "data" => $post,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'title' => ['required', 'unique:posts', 'max:255'],
                'description' => ['required'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]);
            }
            $title = $request->input('title');
            $description = $request->input('description');
            $status = $request->input('status') ?? 1;

            $currentDate = Carbon::now();
            $post = new Post();
            $post->title = $title;
            $post->description = $description;
            $post->status = $status;
            $post->create_user_id = 1;
            $post->updated_user_id = 1;
            $post->created_at = $currentDate;
            $post->updated_at = $currentDate;
            $post->save();
            return response()->json([
                "message" => "Your post has been successfully created",
                "data" => $post
            ]);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $postId = $request->id;
            $rules = [
                'title' => ['required', 'max:255', function (string $attribute, mixed $value, Closure $fail) use ($postId) {
                    $totalPosts = Post::where('id', '!=', $postId)
                        ->where('title', '=', $value)
                        ->count();
                    if ($totalPosts > 0) {
                        $fail("The {$attribute} has already been taken.");
                    }
                },],
                'description' => ['required'],
                'status' => ['required']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]);
            }

            $title = $request->title;
            $description = $request->description;
            $status = $request->status;

            $post = Post::find($postId);
            $post->title = $title;
            $post->description = $description;
            $post->status = $status;
            $post->save();
            return response()->json([
                "message" => "Your post has been updated successfully.",
                "data" => $post
            ]);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            $postId = $request->id;
            $currentDate = Carbon::now();
            $post = Post::find($postId);
            $post->deleted_at = $currentDate;
            $post->deleted_user_id = 1;
            $post->updated_at = $currentDate;
            $post->save();

            return response()->json([
                "message" => "Your Post has been successfully deleted",
                "post" => $post
            ]);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function downloadCsv(Request $request)
    {
        try {
            $posts = Post::orderBy('created_at', 'DESC')->get();
            $filename = "lists.csv";
            $handle = fopen($filename, 'w+');
            //Format a line as CSV and writes it to an open file:
            fputcsv($handle, array('id', 'title', 'description', 'status', 'create_user_id', 'updated_user_id', 'deleted_user_id', 'deleted_at', 'created_at', 'updated_at'));
            foreach ($posts as $key => $post) {
                $id = $key + 1;
                $title = $post['title'];
                $description = $post['description'];
                $status = $post['status'];
                $create_user_id = $post['create_user_id'];
                $updated_user_id = $post['updated_user_id'];
                $deleted_user_id = $post['deleted_user_id'];
                $deleted_at = date('d/m/Y', strtotime($post['deleted_at']));
                $created_at = date('d/m/Y', strtotime($post['created_at']));
                $updated_at = date('d/m/Y', strtotime($post['updated_at']));
                fputcsv($handle, array($id, $title, $description, $status, $create_user_id, $updated_user_id, $deleted_user_id, $deleted_at, $created_at, $updated_at));
            }
            fclose($handle);
            return response()->download($filename)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }

    public function uploadCsv(Request $request)
    {
        try {
            $rules = [
                'upload_file' => ['required', 'mimes:csv'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]);
            }

            $csvDatas = array_map('str_getcsv', file($request->file('upload_file')));
            return response()->json([
                "message" => "Your post has been uploaded successfully.",
                "success" => $csvDatas
            ]);
            if(count($csvDatas[0]) !== 3) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'upload_file'=> "CSV file must contain exactly 3 columns."
                    ]
                ]);
            }

            // Extract header (assuming the first row as header)
            $header = array_shift($csvDatas);

            $currentDate = Carbon::now();

            // Start a new database transaction
            DB::beginTransaction();
            try {
                foreach ($csvDatas as $row) {
                    $title = $row[0];
                    $description = $row[1];
                    $status = (int) $row[2];
                    $post = new Post();
                    $post->title = $title;
                    $post->description = $description;
                    $post->status = $status;
                    $post->create_user_id = 1;
                    $post->updated_user_id = 1;
                    $post->created_at = $currentDate;
                    $post->updated_at = $currentDate;
                    $post->save();
                }
                DB::commit();
                return response()->json([
                    "message" => "Your post has been uploaded successfully.",
                    "success" => true
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    "message" => "Failed to upload the CSV file. Please try again.",
                    "success" => false
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => 500
            ]);
        }
    }
}
