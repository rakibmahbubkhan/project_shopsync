<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Receipt #{{ $return->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        .header { text-align: center; }
        .totals { margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company['name'] }}</h2>
        <p>{{ $company['address'] }}</p>
        <p>Phone: {{ $company['phone'] }}, Email: {{ $company['email'] }}</p>
        <h3>Return Receipt #{{ $return->id }}</h3>
    </div>

    <p><strong>Customer:</strong> {{ $return->sale->customer->name }}</p>
    <p><strong>Warehouse:</strong> {{ $return->sale->warehouse->name }}</p>
    <p><strong>Processed By:</strong> {{ $return->processedBy->name }}</p>
    <p><strong>Date:</strong> {{ $return->created_at }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty Returned</th>
                <th>Unit Price</th>
                <th>Refund Amount</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $return->product->name }}</td>
                <td>{{ $return->quantity }}</td>
                <td>৳ {{ $return->refund->amount / $return->quantity }}</td>
                <td>৳ {{ $return->refund->amount }}</td>
                <td>{{ $return->refund->payment_method }}</td>
            </tr>
        </tbody>
    </table>

    <p class="totals">Total Refund: ৳ {{ $return->refund->amount }}</p>
</body>
</html>