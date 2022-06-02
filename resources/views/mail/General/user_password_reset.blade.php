@extends('layouts.mail.app')
@section('content')
    <tr>
        <td style="padding: 50px 45px 25px 45px; font-family: arial; font-size: 15px; color: #333; line-height: normal;"
            width="550">Dear <strong>{{$user->name}},</strong></td>
    </tr>
    <tr>
        <td style="padding: 0 45px 5px; font-family: arial; font-size: 14px; color: #333; line-height: normal;"
            width="550"> We have received your password change request. This e-mail contains the information you need to change your password.
        </td>
    </tr>
    <tr>
        <td style="padding: 0 45px;" width="550">Click this link to <a href="{{route('front.forgot_password_view',$user->reset_token)}}"
                                                    style="font-size: 14px; font-family: arial; font-weight: bold; line-height: normal;">enter your new password</a></td>
    </tr>
@endsection
