<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Payment Statement</title>
    <style>
        /* Reset Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333333;
            line-height: 1.6;
        }

        /* Container */
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header img {
            max-width: 150px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 18px;
            color: #7f8c8d;
        }

        /* Print Button */
        .print-button {
            text-align: right;
            margin-bottom: 30px;
        }

        .print-button button {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
        }

        .print-button button:hover {
            background-color: #1a252f;
        }

        .print-button button svg {
            margin-right: 8px;
            fill: #ffffff;
        }

        /* Sections */
        .section {
            margin-bottom: 35px;
        }

        .section h2 {
            font-size: 24px;
            color: #34495e;
            margin-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9f7fd;
            transition: background-color 0.3s ease;
        }

        /* Summary */
        .summary {
            font-size: 18px;
            color: #2c3e50;
            margin-top: 20px;
        }

        .summary p {
            margin-bottom: 8px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 14px;
        }

        .footer p {
            margin: 5px 0;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .header h1 {
                font-size: 24px;
            }

            .header p {
                font-size: 16px;
            }

            .section h2 {
                font-size: 20px;
            }

            th, td {
                padding: 10px 12px;
            }

            .summary {
                font-size: 16px;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background-color: #ffffff;
            }

            .container {
                box-shadow: none;
                border-radius: 0;
            }

            .print-button {
                display: none;
            }

            .header img {
                max-width: 120px;
            }

            .header h1 {
                font-size: 28px;
            }

            .header p {
                font-size: 16px;
            }

            .section h2 {
                font-size: 20px;
            }

            th, td {
                padding: 10px 12px;
                font-size: 14px;
            }

            .summary {
                font-size: 16px;
            }

            .footer p {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Section -->
    <div class="header">
         <!-- Header -->
        <h1>{{\App\CentralLogics\Helpers::get_business_settings('business_name')}} Services</h1>
        <h2>Client Payment Statement</h2>
        <p>Client: <strong>{{ $client->name }}</strong></p>
        <p>Balance: <strong>UGX {{ number_format($client->credit_balance, 0) }} /=</strong></p>
    </div>

    <!-- Print Button -->
    <div class="print-button">
        <button onclick="window.print()">
            <!-- Print Icon SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                <path d="M2 6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1H2V6zm0 3h12v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3z"/>
                <path fill-rule="evenodd" d="M14 4h-4.5a1 1 0 0 1-1-1V1a1 1 0 0 1 1-1H14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1zM1 7h14v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7z"/>
            </svg>
            Print Statement
        </button>
    </div>

    <!-- Payment History Section -->
    <div class="section">
        <h2>Payment History</h2>
        @if($payments->isEmpty())
            <p>No payments made yet.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Date</th>
                        <th>Amount (UGX)</th>
                        <th>Balance (UGX)</th>
                        <th>Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->created_at->format('d M Y H:i:s') }}</td>
                            <td>UGX {{ number_format($payment->amount, 0) }} /=</td>
                            <td>UGX {{ number_format($payment->credit_balance, 0) }} /=</td>

                            <td>{{ $payment->agent->f_name }} {{ $payment->agent->l_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <!-- Summary Section -->
        <div class="summary">
            <p><strong>Total Amount Paid:</strong> UGX {{ number_format($totalAmountPaid, 0) }} /=</p>
             <p><strong>Remaining Balance:</strong> UGX {{ number_format($remainingBalance, 0) }} /=</p>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        
         <p>Thank you for choosing {{\App\CentralLogics\Helpers::get_business_settings('business_name')}} Services!</p>
        <p>Contact us: {{\App\CentralLogics\Helpers::get_business_settings('phone')}} or {{\App\CentralLogics\Helpers::get_business_settings('hotline_number')}}</p>
        <p>Generated by Sanaa | {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
    </div>
</div>

</body>
</html>
