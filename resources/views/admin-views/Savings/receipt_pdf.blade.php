<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header, .footer {
            text-align: center;
            position: fixed;
            width: 100%;
        }
        .header {
            top: -60px;
        }
        .footer {
            bottom: -40px;
            font-size: 10px;
            color: #aaa;
        }
        .content {
            margin-top: 80px;
            margin-bottom: 60px;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
        .details, .transaction {
            width: 100%;
            margin-bottom: 20px;
        }
        .details th, .details td, .transaction th, .transaction td {
            padding: 5px;
            text-align: left;
        }
        .transaction th {
            background-color: #f2f2f2;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <!-- You can add a logo here -->
        <!-- Example: <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo"> -->
        <h2>Sanaa Finance</h2>
        <p>Transaction Receipt</p>
    </div>

    <!-- Content -->
    <div class="content">
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
                <td>{{ number_format($savings->accountType->interest_rate, 2) }}%</td>
            </tr>
            <tr>
                <th>Current Balance:</th>
                <td>${{ number_format($savings->balance, 2) }}</td>
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
                        +${{ number_format($transaction->amount, 2) }}
                    @elseif ($transaction->type === 'withdrawal')
                        -${{ number_format($transaction->amount, 2) }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Description:</th>
                <td>{{ $transaction->description ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Additional Information -->
        <p>Thank you for banking with Sanaa Finance. If you have any questions, feel free to contact our support team.</p>

        <!-- Signature -->
        <div class="signature">
            _________________________<br>
            Authorized Signature
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} Sanaa Finance. All rights reserved.
    </div>

</body>
</html>
