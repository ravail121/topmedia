<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\ReportedUser;
use Illuminate\Http\Request;


class ReportedUsersController extends WebController
{


    public function index()
    {
        $title = 'Reported User';
        return view('admin.reported_users.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                $title => route('admin.reported-users.index'),
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
        $main = ReportedUser::select("reported_users.*","rb.name as reported_by","rt.name as reported_to")
            ->join("users as rb","rb.id","=","reported_users.user_id")
            ->join("users as rt","rt.id","=","reported_users.profile_id");
        $return_data['recordsTotal'] = count($main->get());

        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->where('rb.name', 'like', "%$search%");
                $query->orWhere('rt.name', 'like', "%$search%");
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
                    ],
                ];

                $return_data['data'][] = array(
                    'id' => $offset + $key + 1,
                    'reported_by' => $value->reported_by,
                    'reported_to' => $value->reported_to,
                    'created_at' => date("Y-m-d H:i:s",strtotime($value->created_at)),
                    //'action' => $this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
    }

    public function destroy($id)
    {
        $data = ReportedUser::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Post deleted successfully');
        } else {
            error_session('post not found');
        }
        return redirect()->back();
    }
}
