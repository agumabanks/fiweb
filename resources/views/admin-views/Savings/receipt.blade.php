<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt</title>
    <style>
        @media print {
            @page {
                size: 80mm auto; /* Width: 80mm, Height: auto */
                margin: 0;
            }
            body {
                width: 80mm;
                margin: 0;
                padding: 10px;
                font-family: 'Arial', sans-serif;
                font-size: 12px;
            }
            .no-print {
                display: none;
            }
        }

        body {
            width: 80mm;
            margin: 0;
            padding: 10px;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
        }
        .header, .footer {
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 0;
            font-size: 12px;
        }
        .details, .transaction {
            width: 100%;
            margin: 10px 0;
        }
        .details th, .details td, .transaction th, .transaction td {
            padding: 2px 0;
            text-align: left;
        }
        .transaction th {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .signature {
            margin-top: 20px;
            text-align: right;
        }
        .no-print {
            margin-top: 20px;
            text-align: center;
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <!-- Company Logo (Optional) -->
        <!-- <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="width: 100px;"> -->
        <h2>Maslink (B) Credit Services</h2>
        <p>Transaction Receipt</p>
    </div>

    <!-- Account Information -->
    <table class="details">
        <tr>
            <th>Account Number:</th>
            <td>{{ $savings->account_number }}</td>
        </tr>
        <tr>
            <th>Client Name:</th>
            <td>{{ $savings->client->name }}</td>
        </tr>
        <tr>
            <th>Agent:</th>
            <td>{{ $savings->agent ? $savings->agent->f_name . ' ' . $savings->agent->l_name : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Interest Rate:</th>
            <td>{{ number_format($savings->interest_rate, 0) }}%</td>
        </tr>
        <tr>
            <th>Current Balance:</th>
            <td>{{ number_format($savings->balance, 0) }} /=</td>
        </tr>
    </table>

    <!-- Transaction Details -->
    <table class="transaction">
        <tr>
            <th>Date:</th>
            <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>Transaction ID:</th>
            <td>{{ $transaction->id }}</td>
        </tr>
        <tr>
            <th>Type:</th>
            <td>{{ ucfirst($transaction->type) }}</td>
        </tr>
        <tr>
            <th>Amount:</th>
            <td>
                @if ($transaction->type === 'deposit')
                    +{{ number_format($transaction->amount, 0) }} /=
                @elseif ($transaction->type === 'withdrawal')
                    -{{ number_format($transaction->amount, 0) }} /=
                @endif
            </td>
        </tr>
        <tr>
            <th>Description:</th>
            <td>{{ $transaction->description ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- Additional Information -->
    <p>Thank you for banking with Us. By Sanaa.</p>

    <!-- Signature -->
    <div class="signature">
        _________________________<br>
        Authorized Signature
    </div>

    <!-- Print Button (Optional, since print dialog auto-opens) -->
    <div class="no-print">
        <button onclick="window.print();" style="padding: 5px 10px; font-size: 12px;">Print Again</button>
    </div>
</body>
</html>
