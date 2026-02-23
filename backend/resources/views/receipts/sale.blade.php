<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .bold {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 5px;
            border-bottom: 1px dashed #ccc;
        }

        .total {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>Project_ShopSync</h2>
    <p>Sale Receipt</p>
</div>

<p>
    Receipt #: {{ $sale->id }} <br>
    Date: {{ $sale->created_at->format('Y-m-d H:i') }}
</p>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="total">
    <p>Subtotal: {{ number_format($sale->subtotal, 2) }}</p>
    <p>Tax: {{ number_format($sale->tax, 2) }}</p>
    <p class="bold">
        Total: {{ number_format($sale->total, 2) }}
    </p>
</div>

<p style="text-align:center; margin-top:20px;">
    Thank you for shopping with us!
</p>

</body>
</html>