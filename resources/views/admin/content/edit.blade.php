@extends('layouts.admin.app')
@section('h_style')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.6.2/tinymce.min.js"></script>
@endsection

@section('content')
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-subheader   kt-grid__item" id="kt_subheader">
            <div class="kt-container  kt-container--fluid ">
                <div class="kt-subheader__main">
                    <h3 class="kt-subheader__title">{{print_title($title)}}</h3>
                    <span class="kt-subheader__separator kt-hidden"></span>
                    <div class="kt-subheader__breadcrumbs">
                        <a href="javascript:" class="kt-subheader__breadcrumbs-home">
                            <i class="flaticon2-shelter"></i>
                        </a>
                        {!! $breadcrumb !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">{{print_title($title)}}</h3>
                </div>
            </div>
            <!--begin::Form-->
            <form class="kt-form kt-form--label-right" name="main_form" id="main_form" class="main_form"
                  method="post"
                  enctype="multipart/form-data" action="{{route('admin.content.update',$data->id)}}">
                {!! get_error_html($errors) !!}
                @csrf
                @method('PATCH')
                <div class="kt-portlet__body">


                    <div class="form-group row">
                        <label for="title"
                               class="col-form-label col-lg-3 col-sm-12"><span
                                class="text-danger">*</span>Title</label>
                        <div class="col-lg-4 col-md-9 col-sm-12">
                            <input type="text" name="title" id="title" class="form-control" value="{{$data->title}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="content"
                               class="col-form-label col-lg-3 col-sm-12"><span
                                class="text-danger">*</span>Content</label>
                        <div class="col-lg-9 col-md-9 col-sm-12">
                            <textarea name="content" id="content" cols="30" rows="10">
                                {!! $data->content !!}
                            </textarea>
                        </div>
                    </div>

                </div>
                <div class="kt-portlet__foot">
                    <div class="">
                        <div class="row">
                            <div class="col-lg-9 ml-lg-auto">
                                <button type="submit"
                                        class="btn btn-success kt-spinner--right kt-spinner--lg kt-spinner--light"
                                        id="btn-submit-dev">Submit
                                </button>
                                <a href="{{route('admin.content.index')}}"
                                   class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            tinymce.init({
                selector: 'textarea',
                height: 500,
                // theme: 'modern',
            });


            $("#main_form").validate({
                rules: {
                    title: {
                        required: true,
                    },
                },
                messages: {
                    title: {required: 'Please content title'},
                },
                invalidHandler: function (event, validator) {
                    var alert = $('#kt_form_1_msg');
                    alert.removeClass('kt--hide').show();
                    KTUtil.scrollTo('m_form_1_msg', -200);
                },
                submitHandler: function (form) {
                    addOverlay();
                    form.submit();
                }
            });
        });
    </script>
@endsection

