let fullSalesData = [];
let processedSalesData = [];
let salesTable = null;

/* ===============================
   LOAD SALES REPORT
================================ */
function loadSalesReport() {
    let branchId = localStorage.getItem('role_id') || 0;
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadSalesReport', branchId},
        success: function (response) {
            fullSalesData = JSON.parse(response);
            preprocessSalesData();
            renderSalesReport();
        },
        error: function () {
            alert('Failed to load sales report');
        }
    });
}

/* ===============================
   PREPROCESS DATA
================================ */
function preprocessSalesData() {
    processedSalesData = fullSalesData.map(item => {
        const dateOnly = item.dor.split('T')[0];
        return {
            ...item,
            dor_date: dateOnly,
            dor_ts: new Date(dateOnly).getTime(),
            nop: Number(item.nop) || 0,
            selling_price: Number(item.selling_price) || 0,
            purchase_price: Number(item.purchase_price) || 0,
            del_charge: Number(item.del_charge) || 0,
            handling_charge: Number(item.handling_charge) || 0,
            coupon_amount: Number(item.coupon_amount) || 0,
            wallet_amount: Number(item.wallet_amount) || 0
        };
    });
}

/* ===============================
   WEEK RANGE
================================ */
function getCurrentWeekRange() {
    const today = new Date();
    const day = today.getDay();
    const diff = day === 0 ? -6 : 1 - day;

    const monday = new Date(today);
    monday.setDate(today.getDate() + diff);

    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);

    const f = d => d.toISOString().split('T')[0];
    return { start: f(monday), end: f(sunday) };
}

/* ===============================
   MAIN FILTER & RENDER
================================ */
function renderSalesReport(filterType = '') {

    let startDate = $('#startDate').val();
    let endDate = $('#endDate').val();
    let statusFilter = $('#orderStatusFilter').val();

    const today = new Date();

    if (filterType === 'today') {
        startDate = endDate = today.toISOString().split('T')[0];
    }
    else if (filterType === 'this_week') {
        const r = getCurrentWeekRange();
        startDate = r.start;
        endDate = r.end;
    }
    else if (filterType === 'this_month') {
        startDate = new Date(today.getFullYear(), today.getMonth(), 1)
            .toISOString().split('T')[0];
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0)
            .toISOString().split('T')[0];
    }

    const startTS = startDate ? new Date(startDate).getTime() : null;
    const endTS = endDate ? new Date(endDate + 'T23:59:59').getTime() : null;

    const filteredData = processedSalesData.filter(item => {
        if (startTS && item.dor_ts < startTS) return false;
        if (endTS && item.dor_ts > endTS) return false;
        if (statusFilter && item.order_status !== statusFilter) return false;
        return true;
    });

    renderTableAndSummary(filteredData);
}

/* ===============================
   TABLE + SUMMARY (DATATABLE WAY)
================================ */
function renderTableAndSummary(data) {

    if (!salesTable) {
        salesTable = $('#salesTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['csvHtml5', 'excelHtml5', 'pdfHtml5'],
            responsive: true
        });
    }

    salesTable.clear();

    let totalQty = 0;
    let totalAmount = 0;

    let orderSummary = {};

    data.forEach(item => {
        if (!orderSummary[item.idfr]) {
            orderSummary[item.idfr] = {
                coupon: item.coupon_amount,
                wallet: item.wallet_amount,
                delivery: item.del_charge,
                handling: item.handling_charge
            };
        }
    });

    data.forEach((item, index) => {

        const amount = item.nop * item.selling_price;
        totalQty += item.nop;
        totalAmount += amount;

        salesTable.row.add([
            index + 1,
            `<img src="${imgurl + item.image_path}" class="product-img" alt="Product">
                    ${item.name}`,
            `${item.quantity} ${item.unit}`,
            item.nop,
            item.dor_date,
            `₹ ${amount.toFixed(2)}`
        ]);
    });

    salesTable.draw();

    $('.total_orders').text(Object.keys(orderSummary).length);
    $('.total_item_qty').text(totalQty);
    $('.total_amount').text(`₹ ${totalAmount.toFixed(2)}`);

    let c = 0, w = 0, d = 0, h = 0;
    Object.values(orderSummary).forEach(o => {
        c += o.coupon;
        w += o.wallet;
        d += o.delivery;
        h += o.handling;
    });

    // Calculate Total Purchase Cost
    let totalPurchaseCost = 0;
    data.forEach(item => {
        totalPurchaseCost += (item.nop * item.purchase_price);
    });

    // Profit Formula: (Sales - Purchase) - Wallet - Coupon + Delivery + Handling
    // Note: Sales (totalAmount) already includes the selling price * qty
    // We treat Wallet and Coupon as deductions from the "cash" collected, but usually, 
    // profit is (Revenue - Cost). 
    // However, the user specifically asked for: 
    // (Total Sales - Total Purchase Cost) - Wallet - Coupon + Delivery + Handling

    // Let's interpret "Total Sales" as the sum of selling prices (totalAmount).
    // If Wallet/Coupon are part of the payment (i.e. already deducted from what customer paid cash),
    // subtracting them again from Profit essentially means "Real Cash Profit".

    // Let's stick strictly to the user's requested formula:
    // Profit = (totalAmount - totalPurchaseCost) - w - c + d + h

    const grossProfit = totalAmount - totalPurchaseCost;
    const netProfit = grossProfit - w - c + d + h;

    $('.total_coupon').text(`₹ ${c.toFixed(2)}`);
    $('.total_wallet').text(`₹ ${w.toFixed(2)}`);
    $('.total_delivery').text(`₹ ${d.toFixed(2)}`);
    $('.total_handling').text(`₹ ${h.toFixed(2)}`);
    $('.total_profit').text(`₹ ${netProfit.toFixed(2)}`);

    // Color coding for profit
    if (netProfit >= 0) {
        $('.total_profit').css('color', 'green');
    } else {
        $('.total_profit').css('color', 'red');
    }
}

/* ===============================
   CLEAR FILTER
================================ */
function clearSalesReport() {
    $('#startDate').val('');
    $('#endDate').val('');
    $('#orderStatusFilter').val('');
    $('.filter_btn').removeClass('active');
    renderSalesReport();
}

/* ===============================
   EVENTS
================================ */
$(document).on('click', '.filter_btn', function () {
    $('.filter_btn').removeClass('active');
    $(this).addClass('active');
    renderSalesReport($(this).data('filter'));
});

$('#orderStatusFilter').on('change', function () {
    renderSalesReport();
});

/* ===============================
   INIT
================================ */
$(document).ready(function () {
    loadSalesReport();
});
