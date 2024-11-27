<!-- resources/views/admin-views/clients/back.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Card Back</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
        }
        .card-container {
            position: relative;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset('path_to_back_image') }}');
            background-size: cover;
            background-repeat: no-repeat;
        }
        .card-details {
            position: absolute;
            top: 50px;
            left: 20px;
            color: white;
        }
        .card-details p {
            margin: 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="card-details">
            <p>Helpline: {{ $card->helpline }}</p>
            <p>Email: {{ $client->email }}</p>
            <p>Phone: {{ $client->phone }}</p>
        </div>
    </div>
</body>
</html>
