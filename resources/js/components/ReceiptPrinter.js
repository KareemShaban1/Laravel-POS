// Receipt Printer Utility
class ReceiptPrinter {
    static printReceipt(orderData) {
        const {
            orderId,
            customerName,
            totalAmount,
            receivedAmount,
            items,
            createdAt,
            subtotal,
            vatRate,
            vatAmount,
            discountAmount,
        } = orderData;

        // Calculate change
        const change = receivedAmount - totalAmount;

        // Format date and time
        const orderDate = new Date(createdAt);
        const dateStr = orderDate.toLocaleDateString();
        const timeStr = orderDate.toLocaleTimeString();

        // Get currency symbol from global config
        const currencySymbol = window.APP?.currency_symbol || "$";

        // Create receipt HTML
        let receiptHTML = `
            <div id="receipt-content" style="
                width: 300px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.2;
                margin: 0 auto;
                padding: 10px;
                background: white;
            ">
                <div style="text-align: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: bold;">POS SYSTEM</h3>
                    <p style="margin: 5px 0; font-size: 10px;">Receipt #${orderId}</p>
                    <p style="margin: 5px 0; font-size: 10px;">${dateStr} ${timeStr}</p>
                </div>

                <div style="border-top: 1px dashed #000; padding-top: 10px; margin-bottom: 10px;">
                    <p style="margin: 5px 0;"><strong>Customer:</strong> ${
                        customerName || "Walk-in Customer"
                    }</p>
                </div>

                <div style="border-top: 1px dashed #000; padding-top: 10px; margin-bottom: 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px dashed #000;">
                                <th style="text-align: left; padding: 2px 0;">Item</th>
                                <th style="text-align: center; padding: 2px 0;">Qty</th>
                                <th style="text-align: right; padding: 2px 0;">Price</th>
                                <th style="text-align: right; padding: 2px 0;">Total</th>
                            </tr>
                        </thead>
                        <tbody>`;

        // Add items
        if (items && items.length > 0) {
            items.forEach(function (item, index) {
                const itemTotal =
                    parseFloat(item.product.price) * item.quantity;
                receiptHTML += `
                    <tr style="border-bottom: 1px dashed #ccc;">
                        <td style="padding: 2px 0; font-size: 11px;">${
                            item.product.name
                        }</td>
                        <td style="text-align: center; padding: 2px 0;">${
                            item.quantity
                        }</td>
                        <td style="text-align: right; padding: 2px 0;">${currencySymbol}${parseFloat(
                    item.product.price
                ).toFixed(2)}</td>
                        <td style="text-align: right; padding: 2px 0;">${currencySymbol}${itemTotal.toFixed(
                    2
                )}</td>
                    </tr>`;
            });
        }

        receiptHTML += `
                        </tbody>
                    </table>
                </div>

                <div style="border-top: 1px dashed #000; padding-top: 10px;">
                    <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                        <span>Subtotal:</span>
                        <span>${currencySymbol}${parseFloat(
            subtotal || totalAmount
        ).toFixed(2)}</span>
                    </div>`;

        // Add VAT if applicable
        if (vatRate > 0) {
            receiptHTML += `
                    <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                        <span>VAT (${vatRate}%):</span>
                        <span>${currencySymbol}${parseFloat(
                vatAmount || 0
            ).toFixed(2)}</span>
                    </div>`;
        }

        // Add discount if applicable
        if (discountAmount > 0) {
            receiptHTML += `
                    <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                        <span>Discount:</span>
                        <span>-${currencySymbol}${parseFloat(
                discountAmount || 0
            ).toFixed(2)}</span>
                    </div>`;
        }

        receiptHTML += `
                    <div style="display: flex; justify-content: space-between; margin: 5px 0; font-weight: bold; border-top: 1px dashed #000; padding-top: 5px;">
                        <span>TOTAL:</span>
                        <span>${currencySymbol}${parseFloat(
            totalAmount
        ).toFixed(2)}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin: 3px 0;">
                        <span>Paid:</span>
                        <span>${currencySymbol}${parseFloat(
            receivedAmount
        ).toFixed(2)}</span>
                    </div>`;

        // Add change if applicable
        if (change > 0) {
            receiptHTML += `
                    <div style="display: flex; justify-content: space-between; margin: 3px 0; font-weight: bold;">
                        <span>Change:</span>
                        <span>${currencySymbol}${parseFloat(change).toFixed(
                2
            )}</span>
                    </div>`;
        }

        receiptHTML += `
                </div>

                <div style="text-align: center; margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px;">
                    <p style="margin: 5px 0; font-size: 10px;">Thank you for your business!</p>
                    <p style="margin: 5px 0; font-size: 10px;">Please keep this receipt</p>
                </div>
            </div>
        `;

        // Create a new window for printing
        const printWindow = window.open("", "_blank", "width=400,height=600");
        printWindow.document.write(`
            <html>
                <head>
                    <title>Receipt #${orderId}</title>
                    <style>
                        @media print {
                            body { margin: 0; }
                            @page { margin: 0.5in; }
                        }
                        body {
                            font-family: 'Courier New', monospace;
                            margin: 0;
                            padding: 0;
                            background: white;
                        }
                    </style>
                </head>
                <body>
                    ${receiptHTML}
                    <script>
                        window.onload = function() {
                            window.print();
                            window.onafterprint = function() {
                                window.close();
                            };
                        };
                    </script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
}

// Make it available globally
window.ReceiptPrinter = ReceiptPrinter;
