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
            <div class="custom-messages">{!! success_error_view_generator() !!}</div>
            <div class="kt-portlet kt-portlet--mobile">
                <div class="kt-portlet__head kt-portlet__head--lg">
                    <div class="kt-portlet__head-label">
                    <span class="kt-portlet__head-icon">
                        <i class="kt-font-brand flaticon2-list"></i>
                    </span>
                        <h3 class="kt-portlet__head-title">{{print_title($title)}}</h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <table class="table table-striped- table-bordered table-hover table-checkable" id="listResults">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>title</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            oTable = $('#listResults').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [[0, "DESC"]],
                "ajax": "{{route('admin.content.listing')}}",
                "columns": [
                    {"data": "id", searchable: false, sortable: false},
                    {"data": "title", searchable: false, sortable: false},
                    {"data": "action", searchable: false, sortable: false}
                ]
            });
        });
    </script>
@endsection

