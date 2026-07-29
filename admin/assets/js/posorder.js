const loadOrder = async (page = 1, pageSize = 10) => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadPosOrder' },
        success: function (response) {
            if (response != 'error' && response != null) {
                let data = JSON.parse(response);
                $(".order_count").html(data.length);

                const totalItems = data.length;
                const totalPages = Math.ceil(totalItems / pageSize);
                const paginatedData = data.slice((page - 1) * pageSize, page * pageSize);

                let html = `
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Customer ID</th>
                            <th>Total Amount</th>
                            <th>Order Type</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                paginatedData.forEach((item, index) => {
                    let cartData = [];
                    try {
                        cartData = JSON.parse(item.cart_data || '[]');
                    } catch (e) {
                        console.error("Invalid cart_data:", e);
                    }

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.date}</td>
                            <td>${item.customer_id}</td>
                            <td>₹ ${item.total}</td>
                            <td>${item.order_type}</td>
                            <td>${item.payment_type || 'Cash'}</td>
                            <td>${item.status}</td>
                            <td>
                                <div class="btn-section flex gap-5">
                                    <button class="view flex" onclick='viewPosOrderDetails(${JSON.stringify(item)})'>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="print flex" onclick='printPosOrder(${JSON.stringify(item)})'>
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody>`;
                $("#result").html(html);

                // Pagination buttons
                let paginationHtml = `<div class="pagination">`;
                if (page > 1)
                    paginationHtml += `<button class="page-btn" onclick="loadOrder(${page - 1}, ${pageSize})"><i class="bi bi-chevron-double-left"></i></button>`;
                for (let i = 1; i <= totalPages; i++) {
                    paginationHtml += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="loadOrder(${i}, ${pageSize})">${i}</button>`;
                }
                if (page < totalPages)
                    paginationHtml += `<button class="page-btn" onclick="loadOrder(${page + 1}, ${pageSize})"><i class="bi bi-chevron-double-right"></i></button>`;
                paginationHtml += `</div>`;
                $("#pagination").html(paginationHtml);
            }
        }
    });
};


const viewPosOrderDetails = (orderData) => {
    console.log("Selected POS Order:", orderData);

    $(".result").hide();
    $(".headline").hide();
    $(".order-detail-cotainer").show();
    $(".backToOrder").show();

    $(".orderid").html(`#${orderData.id}`);
    $(".orderdate").html(orderData.date);
    $(".orderstatus").html(orderData.status);
    $(".paymentmethode").html(orderData.payment_type || "Cash");

    // Parse cart_data
    let cartItems = [];
    try {
        cartItems = JSON.parse(orderData.cart_data || '[]');
    } catch (err) {
        console.error("Error parsing cart_data:", err);
    }

    let html = '';
    let itemPrice = 0;
    let totalMrp = 0;

    cartItems.forEach((item, index) => {
        itemPrice += parseFloat(item.selling_price) * parseFloat(item.nop);
        totalMrp += parseFloat(item.mrp) * parseFloat(item.nop);

        html += `
            <tr>
                <td>${index + 1}</td>
                <td><img class="order_detail_img" src="${imgurl + item.image_path}" alt="${item.name}"></td>
                <td>${item.name}</td>
                <td>${item.qty} ${item.unit}</td>
                <td>${item.nop}</td>
                <td>₹ ${item.selling_price}</td>
                <td>₹ ${(item.mrp - item.selling_price).toFixed(2)}</td>
                <td>₹ ${(item.nop * item.selling_price).toFixed(2)}</td>
            </tr>
        `;
    });

    $("#order-items").html(html);

    let totalDiscount = totalMrp - itemPrice;

    $(".itemPrice").html(`₹ ${totalMrp.toFixed(2)}`);
    $(".totalDiscount").html(`₹ ${totalDiscount.toFixed(2)}`);
    $(".subTotal").html(`₹ ${itemPrice.toFixed(2)}`);
    $(".extraDiscount").html(`- ₹ ${parseFloat(orderData.extra_discount || 0).toFixed(2)}`);

    $(".delCharege").html(`₹ ${orderData.delivery_charge || 0}`);
    $(".grandTotal").html(`₹ ${orderData.total || 0}`);
};


const printPosOrder = (orderData) => {
    console.log("Selected POS Order:", orderData);

    // 🧾 Bill header
    $(".billOrderId").html(`#${orderData.id}`);
    let currentDate = new Date();
    let formattedDate = currentDate.toLocaleString();
    $(".billOrderDate").html(formattedDate);

    // 🏠 Load store address (same API as order.js)
    $.ajax({
        url: apiurl,
        type: "POST",
        data: { type: "loadBillingDetails" },
        success: function (response) {
            if (response !== "error" && response !== null) {
                let data = JSON.parse(response);
                $(".billing_address").html(`
                    ${data[0].address} <br>
                    Phone: ${data[0].phone_number} <br>
                `);
            }
        },
    });

    // 🧩 Parse `cart_data` from pos_order table
    let cartItems = [];
    try {
        cartItems = JSON.parse(orderData.cart_data || "[]");
    } catch (err) {
        console.error("Invalid cart_data:", err);
    }

    // 🧮 Bill item generation
    let html = "";
    let itemPrice = 0;
    let totalMrp = 0;

    cartItems.forEach((item, index) => {
        const nop = parseFloat(item.nop || 0);
        const mrp = parseFloat(item.mrp || 0);
        const sell = parseFloat(item.selling_price || 0);
        const discount = mrp - sell;
        const totalItemPrice = nop * sell;

        itemPrice += totalItemPrice;
        totalMrp += nop * mrp;

        html += `
            <tr>
                <td>${item.qty} ${item.unit}</td>
                <td>
                    ${item.name}<br>
                    Unit Price: ₹ ${sell.toFixed(2)}<br>
                    Nop: ${nop}<br>
                    Discount: ₹ ${discount.toFixed(2)}
                </td>
                <td class="bprice">₹ ${totalItemPrice.toFixed(2)}</td>
            </tr>
        `;
    });

    $(".bill-items").html(html);

    // 🧾 Totals calculation
    let totalDiscount = totalMrp - itemPrice;

    const formatPrice = (value) => `₹ ${parseFloat(value || 0).toFixed(2)}`;

    $(".billtotalItesPrice").html(formatPrice(totalMrp));
    $(".billtotalDiscount").html(formatPrice(totalDiscount));
    $(".billsubTotal").html(formatPrice(itemPrice));
    $(".extraDiscount").html(`- ${formatPrice(orderData?.extra_discount)}`);
    $(".billdelCharege").html(formatPrice(orderData?.delivery_charge));
    $(".billgrandTotal").html(`<strong>${formatPrice(orderData?.total)}</strong>`);

    // ✅ Open the same bill modal (no change in layout)
    $(".wrapper-overlay").addClass("active");
    $(".bill-modal").addClass("active");
    $("body").css("overflow", "hidden");
};


const closeBillModel = () => {
    $(".wrapper-overlay").removeClass("active");
    $(".bill-modal").removeClass("active");
    $("body").css("overflow", "auto");
};

const printBill = () => {
    window.print();
};

$(".backToOrder").click(() => {

    $(".result").show();
    $(".headline").show();
    $(".order-detail-cotainer").hide();
    $(".backToOrder").hide();

    $("#status").empty();
    n = 0;
    $(".open_delivery_boy_btn").prop('disabled', false);
})
