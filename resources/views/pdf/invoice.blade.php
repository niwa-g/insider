<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 0;
            padding: 32px;
        }
        h1 { font-size: 24px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        .muted { color: #64748b; }
        .header { width: 100%; margin-bottom: 28px; }
        .header td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; }
        .meta { text-align: right; }
        .meta strong { font-size: 18px; }
        .parties { width: 100%; margin-bottom: 24px; }
        .parties td { width: 50%; vertical-align: top; padding-right: 16px; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.lines th {
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
            padding: 8px 6px;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
        }
        table.lines th.num, table.lines td.num { text-align: right; }
        table.lines td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 6px;
            vertical-align: top;
        }
        table.totals { width: 280px; margin-left: auto; margin-top: 16px; border-collapse: collapse; }
        table.totals td { padding: 6px 0; }
        table.totals td.num { text-align: right; }
        table.totals tr.grand td {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .notes { margin-top: 28px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    @php
        $currency = $invoice->currency ?: ($organization->currency ?? 'USD');
        $money = fn ($amount) => $currency.' '.number_format((float) $amount, 2);
        $orgLines = array_filter([
            $organization->legal_name ?: $organization->name,
            $organization->address_line1,
            $organization->address_line2,
            trim(implode(', ', array_filter([$organization->city, $organization->state, $organization->postal_code]))),
            $organization->country,
            $organization->email,
            $organization->phone,
            $organization->tax_id ? 'Tax ID: '.$organization->tax_id : null,
        ]);
        $clientLines = array_filter([
            $client?->company_name,
            $client?->trading_name && $client->trading_name !== $client->company_name ? $client->trading_name : null,
            $client?->address,
            $client?->country,
            $client?->email,
            $client?->phone,
            $client?->tax_information ? 'Tax: '.$client->tax_information : null,
        ]);
    @endphp

    <table class="header">
        <tr>
            <td>
                <div class="brand">{{ $organization->name }}</div>
                @foreach ($orgLines as $line)
                    @if ($loop->first) @continue @endif
                    <div class="muted">{{ $line }}</div>
                @endforeach
            </td>
            <td class="meta">
                <strong>INVOICE</strong><br>
                <div style="margin-top: 6px;">{{ $invoice->number }}</div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <h2>Bill to</h2>
                @forelse ($clientLines as $line)
                    <div @if ($loop->first) style="font-weight: bold;" @endif>{{ $line }}</div>
                @empty
                    <div class="muted">—</div>
                @endforelse
            </td>
            <td>
                <h2>Details</h2>
                <div><span class="muted">Issue date:</span> {{ optional($invoice->issue_date)->format('Y-m-d') }}</div>
                <div><span class="muted">Due date:</span> {{ optional($invoice->due_date)->format('Y-m-d') ?: '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ number_format((float) $line->quantity, 2) }}</td>
                    <td class="num">{{ $money($line->unit_price) }}</td>
                    <td class="num">{{ $money($line->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="muted">Subtotal</td>
            <td class="num">{{ $money($invoice->subtotal) }}</td>
        </tr>
        @if ((float) $invoice->discount_amount > 0)
            <tr>
                <td class="muted">Discount</td>
                <td class="num">-{{ $money($invoice->discount_amount) }}</td>
            </tr>
        @endif
        @if ((float) $invoice->tax_amount > 0)
            <tr>
                <td class="muted">Tax</td>
                <td class="num">{{ $money($invoice->tax_amount) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td>Total</td>
            <td class="num">{{ $money($invoice->total) }}</td>
        </tr>
    </table>

    @if ($invoice->notes)
        <div class="notes">
            <h2>Notes</h2>
            <div>{!! nl2br(e($invoice->notes)) !!}</div>
        </div>
    @endif
</body>
</html>
