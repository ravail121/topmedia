@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}"
          id="bootstrap-style" rel="stylesheet" type="text/css"/>
@endsection

@section('content')

    @include('components.breadcum')
    <div class="row">
        <div class="col-md-4">
            <div class="card ">
                <div class="card-body">
                    <div class="card-title">
                        <div class="kt-widget__media text-center w-100">
                            {!! get_fancy_box_html(get_asset($data->profile_image)) !!}
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Name:</span>
                        <a href="#">{{$data->name ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Userame:</span>
                        <a href="#">{{$data->username ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Crypto Address:</span>
                    </div>
                    <div class="d-flex justify-content-between text-right">
                        <a href="#">{{$data->crypto_address ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Email:</span>
                        <a href="mailto:{{$data->email}}">{{$data->email}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Mobile no:</span>
                        <a href="tel:{{$data->country_code.' '.$data->mobile}}">{{$data->country_code.' '.$data->mobile}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Location:</span>
                        <a href="#">{{$data->location ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Post Count:</span>
                        <a href="#">{{$data->posts_count ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Followers:</span>
                        <a href="#">{{$data->followers_count ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Following:</span>
                        <a href="#">{{$data->following_count ?? "-"}}</a>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">Status:</span>
                        <span class="kt-widget__data">{!! user_status($data->status,$data->deleted_at) !!}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card ">
                <div class="card-body">
                    <div>
                        <ul class="nav nav-tabs float-right" role="tablist" id="tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#post-tab" role="tab">
                                    Posts
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-bs-toggle="tab" data-bs-target="#followers-tab" role="tab">
                                    Followers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-bs-toggle="tab" data-bs-target="#following-tab" role="tab">
                                    Following
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="posts-tab" role="tabpanel">
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table id="listResults"
                                               class="table table-bordered dt-responsive mb-4  nowrap w-100 mb-">
                                            <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Post Content</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade show" id="followers-tab" role="tabpanel">
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table id="followerResults"
                                               class="table table-bordered dt-responsive mb-4  nowrap w-100 mb-">
                                            <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Name</th>
                                                <th>Date</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade show" id="following-tab" role="tabpanel">
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table id="followingListResults"
                                               class="table table-bordered dt-responsive mb-4  nowrap w-100 mb-">
                                            <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Name</th>
                                                <th>Date</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @section('script')
            <script src="{{asset('/assets/admin/vendors/general/validate/jquery.validate.min.js')}}"></script>
            <script src="{{asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js')}}"></script>
            <script type="text/javascript">
                $(document).ready(function () {
                    oTable = $('#listResults').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "order": [
                            [0, "DESC"]
                        ],
                        "ajax": "{{route('admin.user.post_listing',$data->id)}}",
                        "columns": [
                            {
                                "data": "id",
                                searchable: false,
                                sortable: false
                            },
                            {
                                "data": "content",
                                sortable: false
                            },
                            {
                                "data": "created_at",
                                searchable: false,
                                sortable: false
                            },
                            {
                                "data": "action",
                                searchable: false,
                                sortable: false
                            }
                        ]
                    });
                    oTable1 = $('#followerResults').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "order": [
                            [0, "DESC"]
                        ],
                        "ajax": "{{route('admin.user.follower_listing',$data->id)}}",
                        "columns": [
                            {
                                "data": "id",
                                searchable: false,
                                sortable: false
                            },
                            {
                                "data": "name",
                                searchable: false,
                                sortable: false
                            },
                            {
                                "data": "created_at",
                                searchable: false,
                                sortable: false
                            }
                        ]
                    });
                    oTable2 = $('#followingListResults').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "order": [
                            [0, "DESC"]
                        ],
                        "ajax": "{{route('admin.user.following_listing',$data->id)}}",
                        "columns": [
                            {
                                "data": "id",
                                searchable: false,
                                sortable: false
                            },
                            {
                                "data": "name",
                                sortable: false
                            },
                            {
                                "data": "created_at",
                                searchable: false,
                                sortable: false
                            },
                        ]
                    });
                });

            </script>
@endsection
