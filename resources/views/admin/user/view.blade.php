@extends('layouts.admin.app')

@section('content')
    <div class="portlet-toggler">
        <div class="kt-subheader   kt-grid__item" id="kt_subheader">
            <div class="kt-container  kt-container--fluid ">
                <div class="kt-subheader__main">
                    <h3 class="kt-subheader__title">
                        {{print_title($title)}}
                    </h3>
                    <span class="kt-subheader__separator kt-hidden"></span>
                    <div class="kt-subheader__breadcrumbs">
                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                        {!! $breadcrumb !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
            <div class="kt-grid kt-grid--desktop kt-grid--ver kt-grid--ver-desktop kt-app">
                <button class="kt-app__aside-close" id="kt_user_profile_aside_close">
                    <i class="la la-close"></i>
                </button>
                <div class="kt-grid__item kt-app__toggle kt-app__aside" id="kt_user_profile_aside">
                    <div class="kt-portlet kt-portlet--height-fluid-">
                        <div class="kt-portlet__head  kt-portlet__head--noborder">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                </h3>
                            </div>
                        </div>
                        <div class="kt-portlet__body kt-portlet__body--fit-y">
                            <!--begin::Widget -->
                            <div class="kt-widget kt-widget--user-profile-1">
                                <div class="kt-widget__head">
                                    <div class="kt-widget__media text-center w-100">
                                        {!! get_fancy_box_html($data->profile_image) !!}
                                    </div>
                                </div>
                                <div class="kt-widget__body">
                                    <div class="kt-widget__content">
                                        <div class="kt-widget__info">
                                            <span class="kt-widget__label">Name:</span>
                                            <a href="#" class="kt-widget__data">{{$data->name}}</a>
                                        </div>
                                        <div class="kt-widget__info">
                                            <span class="kt-widget__label">Email:</span>
                                            <a href="#" class="kt-widget__data">{{$data->email}}</a>
                                        </div>
                                        <div class="kt-widget__info">
                                            <span class="kt-widget__label">username:</span>
                                            <a href="#" class="kt-widget__data">{{$data->username}}</a>
                                        </div>
                                        <div class="kt-widget__info">
                                            <span class="kt-widget__label">mobile:</span>
                                            <span
                                                class="kt-widget__data">{{$data->country_code .' '.$data->mobile}}</span>
                                        </div>
                                        <div class="kt-widget__info">
                                            <span class="kt-widget__label">status:</span>
                                            <span
                                                class="kt-widget__data">{!! user_status($data->status,$data->deleted_at) !!}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-grid__item kt-grid__item--fluid kt-app__content">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="kt-portlet kt-portlet--tabs kt-portlet--height-fluid">
                                <div class="kt-portlet__head">
                                    <div class="kt-portlet__head-label">
                                        <h3 class="kt-portlet__head-title">Details</h3>
                                    </div>
                                    <div class="kt-portlet__head-toolbar">
                                        <ul class="nav nav-tabs nav-tabs-line nav-tabs-bold nav-tabs-line-brand"
                                            role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab"
                                                   href="#topup_listing" role="tab">
                                                    Top up
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab"
                                                   href="#transaction_receive" role="tab">
                                                    Transaction Receive
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab"
                                                   href="#transaction_sent" role="tab">
                                                    Transaction Sent
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab"
                                                   href="#contact_listing" role="tab">
                                                    User Contact
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="kt-portlet__body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="topup_listing">
                                            <div class="kt-widget2">
                                                <table
                                                    class="table table-striped- table-bordered table-hover table-checkable"
                                                    id="topup_listing_table">
                                                    <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>amount</th>
                                                        <th>Charge</th>
                                                        <th>total</th>
                                                        <th>transaction id</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="transaction_receive">
                                            <div class="kt-widget2">
                                                <table
                                                    class="table table-striped- table-bordered table-hover table-checkable"
                                                    id="transaction_receive_table">
                                                    <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>amount</th>
                                                        <th>sender name</th>
                                                        <th>transaction id</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="transaction_sent">
                                            <div class="kt-widget2">
                                                <table
                                                    class="table table-striped- table-bordered table-hover table-checkable"
                                                    id="transaction_sent_table">
                                                    <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>amount</th>
                                                        <th>receiver name</th>
                                                        <th>transaction id</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="contact_listing">
                                            <div class="kt-widget2">
                                                <table
                                                    class="table table-striped- table-bordered table-hover table-checkable"
                                                    id="contact_listing_table">
                                                    <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Number</th>
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
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            {{--$('#contact_listing_table').DataTable({--}}
            {{--    "processing": true,--}}
            {{--    "serverSide": true,--}}
            {{--    "order": [[0, "DESC"]],--}}
            {{--    "ajax": "{{route('admin.user.contact_listing',$data->id)}}",--}}
            {{--    "columns": [--}}
            {{--        {"data": "id", searchable: false, sortable: false},--}}
            {{--        {"data": "number", searchable: false, sortable: false},--}}
            {{--    ]--}}
            {{--});--}}
        });
    </script>
@endsection
