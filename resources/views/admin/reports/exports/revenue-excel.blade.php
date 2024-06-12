<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Orders</th>
            <th>Gross Revenue</th>
            <th>Taxes</th>
            <th>Shipping</th>
            <th>Net Revenue</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalOrders = 0;
            $totalGrossRevenue = 0;
            $totalTaxesAmount = 0;
            $totalShippingAmount = 0;
            $totalNetRevenue = 0;
        @endphp
        @foreach ($revenues as $revenue)
            <tr>
                <td>{{ date('d M Y', strtotime($revenue->date)) }}</td>
                <td>{{ number_format($revenue->num_of_orders) }}</td>
                <td>{{ number_format($revenue->gross_revenue, 2) }}</td>
                <td>{{ number_format($revenue->taxes_amount, 2) }}</td>
                <td>{{ number_format($revenue->shipping_amount, 2) }}</td>
                <td>{{ number_format($revenue->net_revenue, 2) }}</td>
            </tr>

            @php
                $totalOrders += $revenue->num_of_orders;
                $totalGrossRevenue += $revenue->gross_revenue;
                $totalTaxesAmount += $revenue->taxes_amount;
                $totalShippingAmount += $revenue->shipping_amount;
                $totalNetRevenue += $revenue->net_revenue;
            @endphp
        @endforeach
        <tr>
            <td>Total</td>
            <td>{{ number_format($totalOrders) }}</td>
            <td>{{ number_format($totalGrossRevenue, 2) }}</td>
            <td>{{ number_format($totalTaxesAmount, 2) }}</td>
            <td>{{ number_format($totalShippingAmount, 2) }}</td>
            <td>{{ number_format($totalNetRevenue, 2) }}</td>
        </tr>
    </tbody>
</table>
