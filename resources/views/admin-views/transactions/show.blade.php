<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
            max-width: 300px; /* Adjust width for thermal printer */
            margin: 0 auto;
        }
        .receipt {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        .section {
            margin-bottom: 10px;
        }
        .section h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .details, .footer {
            text-align: center;
        }
        .details p, .footer p {
            margin: 5px 0;
        }
        .footer {
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .btn-print {
            display: block;
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h2>Maslink (B) Credit Services</h2>
            <p>Transaction Receipt</p>
            <p>{{ \Carbon\Carbon::now()->format('F d, Y H:i') }}</p>
        </div>

        <!-- Transaction Details -->
        <div class="section details">
            <h3>Transaction Details</h3>
            <p><strong>Transaction ID:</strong> {{ $transaction->id }}</p>
            <p><strong>Client:</strong> {{ $transaction->client->name }}</p>
            <p><strong>Amount:</strong> UGX {{ number_format($transaction->amount, 0) }}</p>
            <p><strong>Balance:</strong> UGX {{ number_format($transaction->credit_balance, 0) }}</p>
            <p><strong>Note:</strong> {{ ucfirst($transaction->note) }}</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for choosing Maslink (B) Credit Services!</p>
            <p>Contact us: 0759670263(Benon) 0707670776(Moses)</p> 
        </div>
    </div>

    <!-- Print Button -->
    <button onclick="window.print();" class="btn-print">Print Receipt</button>

</body>
</html>

