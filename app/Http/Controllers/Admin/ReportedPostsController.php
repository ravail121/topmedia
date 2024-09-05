<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\ReportedPosts;
use Illuminate\Http\Request;


class ReportedPostsController extends WebController
{


    public function index()
    {
        $title = 'Reported Posts';
        return view('admin.reported_posts.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                $title => route('admin.reported-posts.index'),
            ]),
        ]);
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
        $main = ReportedPosts::select("reported_posts.*","rb.name as reported_by","pb.name as post_by","posts.description")
            ->join("users as rb","rb.id","=","reported_posts.user_id")
            ->join("posts","posts.id","=","reported_posts.post_id")
            ->join("users as pb","pb.id","=","posts.user_id");
        $return_data['recordsTotal'] = count($main->get());

        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->where('rb.name', 'like', "%$search%");
                $query->orWhere('pb.name', 'like', "%$search%");
                $query->orWhere('posts.description', 'like', "%$search%");
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
                        'delete' => route('admin.reported-posts.destroy', $value->id),
                    ],
                ];

                $return_data['data'][] = array(
                    'id' => $offset + $key + 1,
                    'reported_by' => $value->reported_by,
                    'post_content' => $value->description,
                    'post_by' => $value->post_by,
                    'created_at' => date("Y-m-d H:i:s",strtotime($value->created_at)),
                    //'action' => $this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
    }

    public function destroy($id)
    {
        $data = ReportedPosts::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Post deleted successfully');
        } else {
            error_session('post not found');
        }
        return redirect()->back();
    }
}
