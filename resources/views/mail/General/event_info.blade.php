@extends('layouts.mail.app')
@section('content')
    <tr>
        <td style="padding: 50px 45px 25px 45px; font-family: arial; font-size: 15px; color: #333; line-height: normal;"
            width="550">Event Agenda: <strong>{{$event->agenda}},</strong></td>
    </tr>
    <tr>
        <td style="padding: 0 45px 5px; font-family: arial; font-size: 14px; color: #333; line-height: normal;"
            width="550">New event added in {{site_name}} Companion by <strong>{{$instructor->name}}</strong>.
        </td>
    </tr>
    <tr>
        <td style="padding: 0 45px;" width="550">Date time : {{$event->datetime}}</td>

    </tr>
    <tr></tr>
    <tr>
        <td style="padding: 0 45px;" width="550">Location : {{$location->name}}</td>

    </tr>
    <tr></tr>
    <tr>
        <td style="padding: 0 45px;" width="550">Mobile : {{$event->country_code.' '.$event->mobile}}</td>

    </tr>
    <tr></tr>
    <tr>
        <th style="padding: 0 45px; text-align: left;" width="550">Description : </th>
        <td></td>

    </tr>
    <tr>
        <td style="padding: 0 121px;" width="550">{{$event->description}}</td>
        <td></td>

    </tr>
@endsection
