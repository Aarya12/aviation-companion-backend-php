<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="description" content="Free Web tutorials">
        <meta name="keywords" content="HTML, CSS, JavaScript">
        <meta name="author" content="John Doe">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
      </head>
<body>
    <table>
        <thead>
            <tr>
                <th><span style="font-weight: 400;">Note Added By </span> {{$data->name}}</th>
            </tr>
            <tr>
                <th>Datetime : <span style="font-weight: 400;">{{$data->datetime}}</span></th>
            </tr>
            <tr>
                <th>Tags : <span style="font-weight: 400;">{{$data->tags}}</span></th>
            </tr>

            <tr>
                <th>Total Hours : <span style="font-weight: 400;">{{$data->total_hours}}</span></th>
            </tr>
            <tr>
                <th>Note : </th>
            </tr>
            <tr>
                <td>{{$data->note}}</td>
            </tr>
            @if(!empty($data->private_note))
            <tr>
                <th>Private Note : </th>
            </tr>
            <tr>
                <td>{{$data->private_note}}</td>
            </tr>
            @endif
        </thead>
    </table>

</body>
</html>


