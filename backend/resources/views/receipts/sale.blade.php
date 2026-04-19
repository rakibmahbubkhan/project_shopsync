<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $sale->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: #e5e7eb;
            padding: 20px;
            font-size: 10px;
            line-height: 1.4;
        }
        
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Header Section */
        .header {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            padding: 15px;
            text-align: center;
        }
        
        .store-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }
        
        .store-details {
            font-size: 8px;
            opacity: 0.9;
            line-height: 1.3;
        }
        
        .receipt-title {
            background: rgba(255,255,255,0.15);
            padding: 6px;
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        /* Info Sections - Compact */
        .info-section {
            padding: 10px;
            border-bottom: 1px dashed #e5e7eb;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 9px;
        }
        
        .info-label {
            font-weight: 700;
            color: #4F46E5;
            min-width: 65px;
        }
        
        .info-value {
            color: #1f2937;
            text-align: right;
            flex: 1;
        }
        
        .section-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4F46E5;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Items Table - Compact */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        
        .items-table th {
            background: #f3f4f6;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-table td {
            padding: 6px 4px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 9px;
        }
        
        .product-name {
            font-weight: 600;
        }
        
        .product-sku {
            font-size: 7px;
            color: #6b7280;
        }
        
        /* Totals Section - Compact */
        .totals {
            padding: 10px;
            background: #f9fafb;
            margin-top: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 9px;
        }
        
        .total-row.grand {
            border-top: 2px solid #4F46E5;
            margin-top: 6px;
            padding-top: 6px;
            font-size: 12px;
            font-weight: 800;
            color: #4F46E5;
        }
        
        /* Payment Status */
        .status-badge {
            display: inline-block;
            background: #10B981;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: 700;
        }
        
        /* Footer */
        .footer {
            padding: 10px;
            text-align: center;
            border-top: 1px dashed #e5e7eb;
            font-size: 7px;
            color: #6b7280;
        }
        
        .thankyou {
            font-size: 9px;
            font-weight: 700;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        
        /* Divider */
        .divider {
            border-top: 1px dashed #e5e7eb;
            margin: 8px 0;
        }
        
        /* Barcode */
        .barcode {
            text-align: center;
            margin-top: 8px;
        }
        
        /* Print Optimization */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .receipt {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">{{ $company['name'] }}</div>
            <div class="store-details">
                {{ $company['address'] }}<br>
                📞 {{ $company['phone'] }} | ✉️ {{ $company['email'] }}
            </div>
            <div class="receipt-title">
                TAX INVOICE / RECEIPT
            </div>
        </div>
        
        <!-- Transaction Info -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Invoice No:</span>
                <span class="info-value">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cashier:</span>
                <span class="info-value">{{ $sale->user->name ?? 'System' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value"><span class="status-badge">✓ PAID</span></span>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div class="info-section">
            <div class="section-title">CUSTOMER DETAILS</div>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
            </div>
            @if(isset($sale->customer->phone))
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $sale->customer->phone }}</span>
            </div>
            @endif
            @if(isset($sale->customer->email))
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value" style="font-size: 7px;">{{ $sale->customer->email }}</span>
            </div>
            @endif
        </div>
        
        <!-- Warehouse & Payment -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Warehouse:</span>
                <span class="info-value">{{ $sale->warehouse->name ?? 'Main Store' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment:</span>
                <span class="info-value">{{ ucfirst($sale->payment_method) }}</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items Table -->
        <div class="info-section" style="padding-top: 0;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="45%">ITEM</th>
                        <th width="15%">QTY</th>
                        <th width="20%">PRICE</th>
                        <th width="20%">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td>
                            <div class="product-name">{{ $item->product->name ?? 'Product' }}</div>
                            @if($item->product->sku)
                            <div class="product-sku">{{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format($item->selling_price, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="divider"></div>
        
        <!-- Totals -->
        <div class="totals">
            @php
                $subtotal = $sale->total_amount - $sale->tax + $sale->discount;
            @endphp
            <div class="total-row">
                <span>SUB TOTAL</span>
                <span>৳{{ number_format($subtotal, 2) }}</span>
            </div>
            @if($sale->discount > 0)
            <div class="total-row">
                <span>DISCOUNT</span>
                <span style="color: #10B981;">- ৳{{ number_format($sale->discount, 2) }}</span>
            </div>
            @endif
            @if($sale->tax > 0)
            <div class="total-row">
                <span>TAX ({{ ($sale->tax / $subtotal * 100) }}%)</span>
                <span>+ ৳{{ number_format($sale->tax, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand">
                <span>TOTAL</span>
                <span>৳{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>
        
        <!-- Amount in Words -->
        <div class="info-section" style="background: #f9fafb; padding: 6px 10px;">
            <div class="info-row">
                <span class="info-label">In Words:</span>
                <span class="info-value" style="font-size: 7px; text-transform: uppercase;">
                    {{ ucwords(\App\Helpers\NumberToWords::convert(floor($sale->total_amount))) }} Taka Only
                </span>
            </div>
        </div>
        
        <!-- Barcode -->
        <div class="barcode">
            <svg width="200" height="25" xmlns="http://www.w3.org/2000/svg">
                @php
                    $barcode = 'INV' . str_pad($sale->id, 8, '0', STR_PAD_LEFT);
                    for($i = 0; $i < strlen($barcode); $i++) {
                        $width = 1.5 + (ord($barcode[$i]) % 2);
                        $x = $i * 6;
                        echo "<rect x='{$x}' y='0' width='{$width}' height='25' fill='#000'/>";
                    }
                @endphp
            </svg>
            <div style="font-size: 8px; margin-top: 2px; font-family: monospace;">
                {{ 'INV' . str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="thankyou">-- THANK YOU --</div>
            <div>Items returned within 7 days with original receipt</div>
            <div style="margin-top: 4px;">For queries: support@shopsync.com</div>
            <div style="margin-top: 4px;">This is a computer-generated receipt</div>
        </div>
    </div>
</body>
</html>