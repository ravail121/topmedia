<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Posts;
use Illuminate\Http\Request;


class PostsController extends WebController
{


    public function index()
    {
        $title = "Posts";

        return view('admin.posts.index', [
        'title' => $title,
        'breadcrumb' => breadcrumb([
            $title => route('admin.posts.index'),
        ]),
    ]);
        $title = 'Posts';

    }

    public function listing()
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $main = Posts::select("posts.*","users.name","users.username")->join("users","users.id","=","posts.user_id");
        $return_data['recordsTotal'] = count($main->get());

        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
                $query->orWhere('username', 'like', "%$search%");
            });
        }

        $return_data['recordsFiltered'] = count($main->get());
        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
        if (!empty($all_data)) {
            foreach ($all_data as $key => $value) {

                $param = [
                    'id' => $value->id,
                    'url' => [
                        'delete' => route('admin.posts.destroy', $value->id),
                        'view' => route('admin.posts.show', $value->id),
                    ],
                ];

                $return_data['data'][] = array(
                    'id' => $offset + $key + 1,
                    'name' => $value->name,
                    'username' => $value->username,
                    'description' => $value->description,
                    'likes' => $value->like,
                    'comment' => $value->comments,
                    'created_at' => date("Y-m-d H:i:s",strtotime($value->created_at)),
                    'action' => $this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
    }

    public function destroy($id)
    {
        $data = Posts::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Post deleted successfully');
        } else {
            error_session('post not found');
        }
        return redirect()->back();
    }

    public function show($id)
    {
        $title = "Post Details";
        $data = Posts::with("comments_list")->find($id);
        if ($data) {
            return view('admin.posts.view', [
                'title' => $title,
                'data' => $data,
                'breadcrumb' => breadcrumb([
                    $title => route('admin.posts.show',$id),
                    "Posts" => route('admin.posts.index'),
                ]),
            ]);
        } else {
            error_session('post not found');
        }
        return redirect()->back();
    }


}
