<?php

namespace App\Http\Controllers\Admin;

use App\HelpRequests;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;


class HelpController extends WebController
{


    public function index()
    {
        $title = 'Help Request';
        return view('admin.help.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                $title => route('admin.help-requests.index'),
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
        $main = HelpRequests::select("help_requests.*","users.name")->join("users","users.id","=","help_requests.user_id");
        $return_data['recordsTotal'] = count($main->get());

        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->where('type_of_issue', 'like', "%$search%");
                $query->orWhere('name', 'like', "%$search%");
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
                        'delete' => route('admin.help-requests.destroy', $value->id),
                    ],
                ];

                $return_data['data'][] = array(
                    'id' => $offset + $key + 1,
                    'name' => $value->name,
                    'type' => $value->type_of_issue,
                    'description' => $value->description,
                    'created_at' => date("Y-m-d H:i:s",strtotime($value->created_at)),
                    'action' => $this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
    }

    public function destroy($id)
    {
        $data = HelpRequests::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Help request deleted successfully');
        } else {
            error_session('Help request not found');
        }
        return redirect()->back();
    }
}
