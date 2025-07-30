<!DOCTYPE html>
<html>

<head>
    <title>Kontak Google</title>
</head>

<body>
    <h1>Kontak dari Google</h1>
    <ul>
        @foreach ($contacts as $contact)
            <li>
                <strong>{{ $contact['name'] }}</strong><br>
                Email: {{ $contact['email'] }}<br>
                Telepon: {{ $contact['phone'] }}
            </li>
        @endforeach
    </ul>
</body>

</html>
