@extends('layouts.mail.app')
@section('content')
    <tr>
        <td style="padding: 50px 45px 25px 45px; font-family: arial; font-size: 15px; color: #333; line-height: normal;"
            width="550">Dear <strong>{{$user->name}},</strong></td>
    </tr>
    <tr>
        <td style="padding: 0 45px 5px; font-family: arial; font-size: 14px; color: #333; line-height: normal;"
            width="550">You Recently Added as a student in {{site_name}} Account by {{$instructor->name}}, Follwing are the login credentials.
        </td>
    </tr>
    <tr>
        <td style="padding: 0 45px;" width="550">Email : {{$user->email}}</td>

    </tr>
    <tr></tr>
    <tr>
        <td style="padding: 0 45px;" width="550">Password : {{config('constants.default.user_password')}}</td>
    </tr>
@endsection
