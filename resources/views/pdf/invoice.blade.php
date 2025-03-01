<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .details {
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Order Receipt</h1>
        <p>Order Number: {{ $order->order_number ?? ""}}</p>
    </div>

    <div class="details">
        <p><strong>Customer Name:</strong> {{ $order->customer_name ?? "" }}</p>
        <p><strong>Email:</strong> {{ $order->email ?? "" }}</p>
        <p><strong>Phone:</strong> {{ $order->phone  ??  ""}}</p>
        <p><strong>Delivery Address:</strong> {{ $order->delivery_address ?? ""}}</p>
        <p><strong>Gross Amount:</strong> {{ $order->gross_amount ?? ""}}</p>
        <p><strong>Net Amount:</strong> {{ $order->net_amount ?? ""}}</p>
    </div>

    <div class="footer">
        <p>Thank you for your purchase!</p>
    </div>
</body>

</html>