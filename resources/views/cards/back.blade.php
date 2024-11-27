<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Information</title>
    <style>
        .background {
            background-image: url('{{ asset('assets/admin/back.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh; /* Full viewport height */
            width: 100vw;  /* Full viewport width */
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="content">
                <p class="m-0 p-0 text-gray-700">CVV: {{ $card->cvv }}</p>
                <p class="m-0 p-0 text-gray-700">Issued: {{ $card->created_at->format('M Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
