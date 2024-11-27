<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100" 
        style="background-image: url({{ public_path('/assets/admin/front.png') }}); background-size: cover;">
    <div class="container mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-md relative" > 
            <div class="absolute bottom-0 right-0 w-full p-4">
                <p class="text-white text-right mt-2">{{ $client->name }}</p> 
            </div>
        </div>
    </div>
</body>
</html>


