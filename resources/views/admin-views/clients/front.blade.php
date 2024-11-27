<!-- resources/views/admin-views/clients/front.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Card Front</title>
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
            background-image: url('{{ asset('path_to_front_image') }}');
            background-size: cover;
            background-repeat: no-repeat;
        }
        .card-details {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
        }
        .card-details h1 {
            margin: 0;
            font-size: 24px;
        }
        .card-details p {
            margin: 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="card-details">
            <h1>{{ $client->name }}</h1>
            <p>Card Number: {{ $card->number }}</p>
            <p>Expiry Date: {{ $card->expiry_date }}</p>
        </div>
    </div>
</body>
</html>
