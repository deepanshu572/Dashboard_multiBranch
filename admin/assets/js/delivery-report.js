let fullOrderData = [];
let deliveryMen = [];
let deliveryTable = null;


/* ===============================
   LOAD DELIVERY MEN
================================ */
async function loadDeliveryBoys() {
    const formData = new FormData();
    formData.append('type', 'loadDeliveryMan');

    try {
        const response = await fetch(apiurl, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data && data !== 'error') {
            deliveryMen = data;
            const select = $('#deliveryBoyFilter');
            select.empty();
            select.append('<option value="">All Delivery Boys</option>');

            data.forEach(boy => {
                select.append(`<option value="${boy.id}">${boy.first_name} ${boy.last_name}</option>`);
            });
        }
    } catch (error) {
        console.error("Error loading delivery men:", error);
    }
}

/* ===============================
   LOAD ORDER DATA
================================ */
function loadDeliveryReport() {
    // We use 'loadOrder' to get full order details including delivery boy assignment
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'getAllDeliveryOrders' },
        success: function (response) {
            try {
                fullOrderData = JSON.parse(response);
                if (!Array.isArray(fullOrderData)) fullOrderData = [];

                // Process data to adding date timestamps for easier filtering
                fullOrderData.forEach(item => {
                    item.dor_ts = new Date(item.dor.replace(" ", "T")).getTime();
                    item.dor_date = item.dor.split(" ")[0];
                    item.amount = parseFloat(item.total);
                });

                renderDeliveryReport();
            } catch (e) {
                console.error("Error parsing order data", e);
            }
        },
        error: function () {
            alert('Failed to load delivery report');
        }
    });
}

/* ===============================
   WEEK RANGE HELPER
================================ */
function getCurrentWeekRange() {
    const today = new Date();
    const day = today.getDay();
    const diff = day === 0 ? -6 : 1 - day;

    const monday = new Date(today);
    monday.setDate(today.getDate() + diff);
    monday.setHours(0, 0, 0, 0);

    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    sunday.setHours(23, 59, 59, 999);

    return { start: monday.getTime(), end: sunday.getTime() };
}

/* ===============================
   RENDER REPORT
================================ */
function renderDeliveryReport(filterType = '') {

    let startDate = $('#startDate').val();
    let endDate = $('#endDate').val();
    let deliveryBoyId = $('#deliveryBoyFilter').val();
    let paymentMethod = $('#paymentMethodFilter').val();
    let orderStatus = $('#orderStatusFilter').val();

    let startTS = null;
    let endTS = null;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const endOfDay = new Date();
    endOfDay.setHours(23, 59, 59, 999);

    // Apply Quick Date Filters
    if (filterType === 'today') {
        startTS = today.getTime();
        endTS = endOfDay.getTime();
        // Set date inputs for visual feedback
        $('#startDate').val(today.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);
    } else if (filterType === 'yesterday') {
        const yest = new Date(today);
        yest.setDate(today.getDate() - 1);
        startTS = yest.getTime();
        endTS = new Date(yest).setHours(23, 59, 59, 999);

        $('#startDate').val(yest.toISOString().split('T')[0]);
        $('#endDate').val(yest.toISOString().split('T')[0]);
    } else if (filterType === 'this_week') {
        const r = getCurrentWeekRange();
        startTS = r.start;
        endTS = r.end;
        $('#startDate').val(new Date(r.start).toISOString().split('T')[0]);
        $('#endDate').val(new Date(r.end).toISOString().split('T')[0]);
    } else if (filterType === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        lastDay.setHours(23, 59, 59, 999);

        startTS = firstDay.getTime();
        endTS = lastDay.getTime();

        $('#startDate').val(firstDay.toISOString().split('T')[0]);
        $('#endDate').val(lastDay.toISOString().split('T')[0]);
    } else {
        // Manual Date Range
        if (startDate) startTS = new Date(startDate).getTime();
        if (endDate) endTS = new Date(endDate + 'T23:59:59').getTime();
    }

    // Filter Logic
    const filteredData = fullOrderData.filter(item => {
        // Date Filter
        if (startTS && item.dor_ts < startTS) return false;
        if (endTS && item.dor_ts > endTS) return false;

        // Delivery Boy Filter
        // Note: checking if item.delivery_man_id exists and matches
        if (deliveryBoyId) {
            // Some APIs might return delivery_man_id as string or int
            if (item.delivery_man_id != deliveryBoyId) return false;
        }

        // Payment Method Filter
        if (paymentMethod) {
            if (paymentMethod === 'online') {
                // Check for various online statuses/types if needed
                if (item.payment_methode.toLowerCase() === 'cash on delivery') return false;
            } else {
                if (item.payment_methode.toLowerCase() !== paymentMethod.toLowerCase()) return false;
            }
        }

        // Order Status Filter
        if (orderStatus) {
            if (item.status.toLowerCase() !== orderStatus.toLowerCase()) return false;
        }

        return true;
    });

    updateTable(filteredData);
    updateSummary(filteredData);
}

/* ===============================
   UPDATE TABLE
================================ */
function updateTable(data) {
    if (!deliveryTable) {
        deliveryTable = $('#deliveryTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['csvHtml5', 'excelHtml5', 'pdfHtml5'],
            responsive: true,
            pageLength: 20
        });
    }

    deliveryTable.clear();

    data.forEach((item, index) => {

        // Find Delivery Boy Name
        let dbName = "Unassigned";
        if (item.delivery_man_id) {
            const boy = deliveryMen.find(b => b.id == item.delivery_man_id);
            if (boy) dbName = `${boy.first_name} ${boy.last_name}`;
            else dbName = `ID: ${item.delivery_man_id}`;
        }

        deliveryTable.row.add([
            index + 1,
            item.id,
            item.dor,
            `<span style="font-weight:600; color:#555;">${dbName}</span>`,
            `${item.user_id} <br><small>${item.address ? item.address.substring(0, 20) + '...' : ''}</small>`,
            `<span class="badge ${item.payment_methode === 'Cash On Delivery' ? 'bg-warning' : 'bg-info'} text-dark">${item.payment_methode}</span>`,
            `<span class="badge bg-secondary">${item.status}</span>`,
            `₹ ${parseFloat(item.total).toFixed(2)}`
        ]);
    });

    deliveryTable.draw();
}

/* ===============================
   UPDATE SUMMARY
================================ */
function updateSummary(data) {
    let totalOrders = data.length;
    let totalAmount = 0;
    let totalCOD = 0;
    let totalOnline = 0;

    data.forEach(item => {
        let amt = parseFloat(item.total) || 0;
        totalAmount += amt;

        if (item.payment_methode.toLowerCase() === 'cash on delivery') {
            totalCOD += amt;
        } else {
            totalOnline += amt;
        }
    });

    $('.total_orders').text(totalOrders);
    $('.total_amount').text(`₹ ${totalAmount.toFixed(2)}`);
    $('.total_cod').text(`₹ ${totalCOD.toFixed(2)}`);
    $('.total_online').text(`₹ ${totalOnline.toFixed(2)}`);
}

/* ===============================
   CLEAR FILTERS
================================ */
function clearDeliveryFilters() {
    $('#startDate').val('');
    $('#endDate').val('');
    $('#deliveryBoyFilter').val('');
    $('#paymentMethodFilter').val('');
    $('#orderStatusFilter').val('');
    $('.btn-filter').removeClass('active');

    renderDeliveryReport();
}

/* ===============================
   EVENTS & INIT
================================ */
$(document).ready(function () {
    loadDeliveryBoys().then(() => {
        loadDeliveryReport();
    });
});

$(document).on('click', '.btn-filter', function () {
    $('.btn-filter').removeClass('active');
    $(this).addClass('active');
    renderDeliveryReport($(this).data('filter'));
});
