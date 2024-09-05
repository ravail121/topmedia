@extends('layouts.master')
@section('content')

    @include('components.breadcum')
    <div class="row">
        <div class="col-md-3">
            <div class="card ">
                <div class="card-body">
                    <div class="card-title d-flex justify-content-center">
                        <span class="text-center">
                            {!! get_fancy_box_html(get_asset($data->file)) !!}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">
                            Likes :
                        </span>
                        <span>
                            {{$data->like}}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold">
                            Comments :
                        </span>
                        <span>
                            {{$data->comments}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card ">
                <div class="card-body">
                    <div class="card-title d-flex flex-column">
                        <h4>
                            Posts Details
                        </h4>
                    </div>
                    <div>
                        <div class="d-flex flex-column">
                            <span>Content :</span>
                            {!! nl2br($data->description) !!}
                        </div>
                    </div>
                    <div class="card-title d-flex flex-column">
                        <h4>
                            Comments
                        </h4>
                    </div>
                    <div>
                        @forelse($data->comments_list as $value)
                            <div class="d-flex flex-column pb-3">
                                <span class="font-weight-bold" style="font-weight: bold">
                                    {{ucfirst($value->user->name)}} ( {{date("Y-m-d H:i:s",strtotime($value->created_at))}} )
                                </span>
                                <span>
                                    {{ucfirst($value->comment)}}
                                </span>
                                @forelse($value->comments_reply as $value1)
                                    <div class=" p-2 " style="border-left: 1px dashed black ">
                                        <div class="d-flex flex-column">

                                        <span class="font-weight-bold" style="font-weight: bold">
                                            {{ucfirst($value1->user->name)}} ( {{date("Y-m-d H:i:s",strtotime($value1->created_at))}} )
                                        </span>
                                            <span>
                                            {{ucfirst($value1->comment)}}
                                        </span>
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        @empty
                            <div class="d-flex">
                                No Comments Found
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
        });

    </script>
@endsection
