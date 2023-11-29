<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attachment</title>
    <link rel="icon" href="{{URL::asset('assets/img/brand/favicon.png')}}" type="image/x-icon"/>
    @if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
    <style>
        
        html, body {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
    @endif
</head>
<body>
    @if ($fileExtension === 'pdf')
        <embed src="data:application/pdf;base64,{{ $base64File }}" type="application/pdf" width="100%" height="800px">
    @elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
        <div>
            <img src="data:image/{{ $fileExtension }};base64,{{ $base64File }}" width="400" height="400" alt="File">
        </div>
    @else
        <p>Unsupported file type.</p>
    @endif
</body>
</html>