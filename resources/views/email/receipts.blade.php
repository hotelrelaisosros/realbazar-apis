<!DOCTYPE html>
<html>

<head>
    <title>Your Order Receipt</title>
</head>

<body>

    <h1>Thank you for your order, {{ $order->customer_name ?? "" }}!</h1>
    <p>Here are your order details:</p>
    <ul>
        <li><strong>Order Number:</strong> {{ $order->order_number ?? "" }}</li>
        <li><strong>Email:</strong> {{ $order->email?? "" }}</li>
        <li><strong>Phone:</strong> {{ $order->phone ?? ""}}</li>
        <li><strong>Delivery Address:</strong> {{ $order->delivery_address ?? ""}}</li>
        <li><strong>Gross Amount:</strong> {{ $order->gross_amount ?? ""}}</li>
        <li><strong>Net Amount:</strong> {{ $order->net_amount ?? ""}}</li>
    </ul>
    <p>You can download your receipt by clicking the button below:</p>
    <a href="{{ $receiptUrl ?? "" }}" style="padding: 10px; background-color: #007bff; color: white; text-decoration: none;">Download Receipt</a>
</body>

</html>