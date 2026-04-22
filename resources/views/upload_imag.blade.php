<html>
    <head>
        <title>Upload image</title>
    </head>
    <body>
        <form method='post' enctype='multipart/form-data'>
            @csrf
            <input type='file' name='icon' />
            <input type='submit' value='Upload' />
        </form>
    </body>
</html>