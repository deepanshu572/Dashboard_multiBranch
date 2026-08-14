
let orderData = [];

let allOrders = [];
let userOrderCounts = {};

// State management
let currentPage = 1;
let currentSize = 10;
let lastScrollPosition = 0;

// Helper: Parse Date Safely
const parseOrderDate = (dateStr) => {
    if (!dateStr) return null;
    if (typeof dateStr !== 'string') {
        // Try to handle if it's already a Date object
        if (dateStr instanceof Date) return dateStr;
        return null;
    }
    // Handle "2024-09-17 19:03:32" -> "2024-09-17T19:03:32"
    let safeStr = dateStr.replace(" ", "T");
    let d = new Date(safeStr);
    if (isNaN(d.getTime())) return null; // Invalid date
    return d;
};

// Global variable to store the scrolling container selector
let scrollTargetSelector = 'window';

const getScrollData = () => {
    // Check main container first as it's most likely for internal scroll
    // But since body is 100%, and main-container is 100%, let's check all
    if ($('.main-container').scrollTop() > 0) return { selector: '.main-container', top: $('.main-container').scrollTop() };
    if ($('.wrapper').scrollTop() > 0) return { selector: '.wrapper', top: $('.wrapper').scrollTop() };
    if ($('body').scrollTop() > 0) return { selector: 'body', top: $('body').scrollTop() };
    if ($('html').scrollTop() > 0) return { selector: 'html', top: $('html').scrollTop() };
    if ($(window).scrollTop() > 0) return { selector: 'window', top: $(window).scrollTop() };

    // Default to window if everything is 0
    return { selector: 'window', top: 0 };
};

// Load Time Slots
const loadTimeSlots = () => {
    console.log("Loading time slots...");
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadTimeSlot' },
        success: function (response) {
            if (response != 'error' && response != null) {
                try {
                    let data = JSON.parse(response);
                    let options = '<option value="all">All Slots</option>';
                    data.forEach(slot => {
                        // Assuming slot object has 'time_slot' or similar, or it's a string
                        let slotName = slot.time_slot || slot.slot || slot;
                        options += `<option value="${slotName}">${slotName}</option>`;
                    });
                    $('#time-slot-filter').html(options);
                    console.log("Time slots loaded");
                } catch (e) {
                    console.error("Error parsing time slots:", e);
                }
            }
        },
        error: function (err) {
            console.error("Error loading time slots:", err);
        }
    });
};

const loadOrder = async (page = 1, pageSize = 10) => {
    console.log("loadOrder called", { page, pageSize });
    let params = new URLSearchParams(window.location.search);
    let type = params.get("type");
    let mobile = params.get("mobile");
    let branchId = localStorage.getItem("role_id");

       const productData =
        branchId > 0
        ? {
            type: "loadBranchOrder",
            branchId
        }
        : {
            type: "loadOrder"
        };

    console.log("URL Param Type:", type, "Mobile:", mobile);

    if (allOrders.length === 0) {
        console.log("Fetching orders from API...");
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: productData,
            success: function (response) {
                console.log("API Response received (raw):", response);
                if (response != 'error' && response != null) {
                    try {
                        let data = JSON.parse(response);
                        console.log("Parsed Data:", data);

                        if (!Array.isArray(data)) {
                            console.error("CRITICAL: Data is not an array!", data);
                            return;
                        }

                        allOrders = data;

                        // Pre-calculate user order counts
                        userOrderCounts = {};
                        allOrders.forEach(order => {
                            let uid = order.user_id;
                            if (uid) {
                                userOrderCounts[uid] = (userOrderCounts[uid] || 0) + 1;
                            }
                        });

                        // Initial Apply (handles URL param 'type' if present)
                        if (type && type !== 'all') {
                            $('#status-filter').val(type.toLowerCase());
                        }

                        // Handle mobile filter param
                        if (mobile) {
                            $('#search-input').val(mobile);
                        }

                        applyFilters(page, pageSize);
                    } catch (e) {
                        console.error("Error parsing JSON in loadOrder:", e);
                    }
                } else {
                    console.error("Response was error or null");
                }
            },
            error: function (err) {
                console.error("AJAX Error in loadOrder:", err);
            }
        });
    } else {
        console.log("Using cached allOrders");
        applyFilters(page, pageSize);
    }
};

const applyFilters = (page = 1, pageSize = 10) => {
    // Get page size from selector logic
    let selectedPageSize = parseInt($('#rows-per-page').val()) || 10;
    pageSize = selectedPageSize;

    // Update global state
    currentPage = page;
    currentSize = pageSize;

    console.log("applyFilters called with pageSize:", pageSize); 

    let dateFilter = $('#date-filter').val();
    let startDate = $('#start-date').val(); // yyyy-mm-dd
    let endDate = $('#end-date').val();     // yyyy-mm-dd
    let statusFilter = $('#status-filter').val();
    let typeFilter = $('#order-type-filter').val();
    let paymentFilter = $('#payment-method-filter').val();
    let selectedDateFilter = $('#selected-date-filter').val(); // yyyy-mm-dd
    let timeSlotFilter = $('#time-slot-filter').val();
    let searchQuery = $('#search-input').val().toLowerCase().trim();

    console.log("Current Filters:", { dateFilter, statusFilter, typeFilter, paymentFilter, selectedDateFilter, timeSlotFilter, searchQuery });

    let filteredData = allOrders.filter(item => {
        let matches = true;
        let orderDate = parseOrderDate(item.dor);

        // Date Filter (Placed Date)
        if (dateFilter !== 'all' && orderDate) {
            let today = new Date();
            today.setHours(0, 0, 0, 0);

            let itemDateStart = new Date(orderDate);
            itemDateStart.setHours(0, 0, 0, 0);

            if (dateFilter === 'today') {
                if (itemDateStart.getTime() !== today.getTime()) matches = false;
            } else if (dateFilter === 'yesterday') {
                let yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                if (itemDateStart.getTime() !== yesterday.getTime()) matches = false;
            } else if (dateFilter === 'this_week') {
                let currentDay = today.getDay(); // 0 (Sun) to 6 (Sat)
                let diffToSun = currentDay;
                let startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - diffToSun);
                startOfWeek.setHours(0, 0, 0, 0);

                let endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);
                endOfWeek.setHours(23, 59, 59, 999);

                if (orderDate < startOfWeek || orderDate > endOfWeek) matches = false;

            } else if (dateFilter === 'this_month') {
                if (orderDate.getMonth() !== today.getMonth() || orderDate.getFullYear() !== today.getFullYear()) matches = false;
            } else if (dateFilter === 'custom') {
                if (startDate) {
                    let start = new Date(startDate);
                    start.setHours(0, 0, 0, 0);
                    if (orderDate < start) matches = false;
                }
                if (endDate) {
                    let end = new Date(endDate);
                    end.setHours(23, 59, 59, 999);
                    if (orderDate > end) matches = false;
                }
            }
        }

        // Status Filter - Case Insensitive
        if (statusFilter !== 'all') {
            if (!item.status || item.status.toLowerCase() !== statusFilter.toLowerCase()) matches = false;
        }

        // Type Filter
        if (typeFilter !== 'all') {
            if (!item.order_type || item.order_type.toLowerCase() !== typeFilter.toLowerCase()) matches = false;
        }

        // Payment Filter
        if (paymentFilter !== 'all') {
            let pMethod = (item.payment_method || "").toLowerCase().replace(/ /g, "_");
            let pFilter = paymentFilter.toLowerCase();
            if (item.payment_method.toLowerCase() !== pFilter && pMethod !== pFilter) matches = false;
        }

        // Selected Date Filter (Delivery Date)
        if (selectedDateFilter) {
            // Check against item.date (Scheduled Delivery Date)
            // Assuming item.date format matches or contains YYYY-MM-DD
            if (item.date !== selectedDateFilter) {
                // Fallback check if formats differ
                if (!item.date || !item.date.includes(selectedDateFilter)) matches = false;
            }
        }

        // Time Slot Filter
        if (timeSlotFilter && timeSlotFilter !== 'all') {
            if (!item.time || item.time !== timeSlotFilter) matches = false;
        }

        // Search (Name/ID/Mobile)
        if (searchQuery) {
            let matchesSearch = false;
            // Search by Order ID
            if (String(item.id).toLowerCase().includes(searchQuery)) matchesSearch = true;
            // Search by User ID (Name) - existing logic but user_id might be name or ID? 
            if (item.user_id && String(item.user_id).toLowerCase().includes(searchQuery)) matchesSearch = true;

            // Search by Mobile logic (assuming item has user info or we need to look up)
            // Based on viewOrderDetails, item has user_id, but the main list might not have mobile directly on the item object unless it's joined.
            // Let's check if item has a mobile or phone field. If not, we might need to rely on the fact that the user table stores mobile.
            // However, looking at the renderOrders, it seems `item.user_id` is displayed. 
            // If the API returns mobile in the order object (which it should for this to work purely client side), we check it.
            if (item.mobile && String(item.mobile).includes(searchQuery)) matchesSearch = true;
            if (item.phone_number && String(item.phone_number).includes(searchQuery)) matchesSearch = true; // billing/shipping phone?

            // If we still haven't found a match, maybe `item.user_id` contains the name and we want to search by name too (which controls above cover).

            if (!matchesSearch) matches = false;
        }

        return matches;
    });

    console.log("Filtered Data Length:", filteredData.length);
    renderOrders(filteredData, page, pageSize);
}

const renderOrders = (data, page, pageSize) => {
    console.log("renderOrders called with data length:", data.length);
    $(".order_count").html(data.length);

    const totalItems = data.length;
    const totalPages = Math.ceil(totalItems / pageSize);

    if (page < 1) page = 1;
    if (page > totalPages && totalPages > 0) page = totalPages;

    const paginatedData = data.slice((page - 1) * pageSize, page * pageSize);

    let html = `
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Customer Details</th>
                <th>Total Amt</th>
                <th>Order Type</th>
                <th>Payment Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
    `;

    if (data.length === 0) {
        html += `<tr><td colspan="8" style="text-align:center; padding: 20px;">No orders found matching the criteria.</td></tr>`;
    } else {
        paginatedData.forEach((item, index) => {
            let items = JSON.stringify(item);
            let dateObj = parseOrderDate(item.dor);
            let formattedDate = dateObj ? dateObj.toLocaleString("en-US", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "numeric",
                minute: "2-digit",
                hour12: true,
            }) : item.dor;

            let userCount = userOrderCounts[item.user_id] || 0;

            html += `
                <tr>
                    <td class="sl">${item.id}</td>
                    <td>${formattedDate}</td>
                    <td>
                        <div style="font-weight: bold;">${item.user_id}</div>
                        <div style="font-size: 0.8em; color: #666;">Total Orders: ${userCount}</div>
                    </td>
                    <td>₹ ${item.total}</td>
                    <td><span class="orderType"> ${item.order_type} </span></td>
                    <td><span class="orderType"> ${item.payment_method} </span></td>
                    <td><span class="orderStatus ${getStatusClass(item.status)}">${item.status}</span></td>
                    <td>
                        <div class="btn-section flex gap-5">
                        <button class="view flex" onclick='viewOrderDetails(${items})'><i class="bi bi-eye"></i></button>
                        <button class="print flex" onclick='printOrderBill(${items})'><i class="bi bi-printer"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    html += `</tbody>`;
    $("#result").html(html);

    // Generate pagination buttons
    let paginationHtml = `<div class="pagination">`;

    if (page > 1) {
        paginationHtml += `<button class="page-btn" onclick="applyFilters(${page - 1}, ${pageSize})"><i class="bi bi-chevron-double-left"></i></button>`;
    }

    let startPage = Math.max(1, page - 1);
    let endPage = Math.min(totalPages, page + 1);

    if (startPage > 1) {
        paginationHtml += `<button class="page-btn" onclick="applyFilters(1, ${pageSize})">1</button>`;
        if (startPage > 2) paginationHtml += `<span class="ellipsis">..</span>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="applyFilters(${i}, ${pageSize})">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) paginationHtml += `<span class="ellipsis">..</span>`;
        paginationHtml += `<button class="page-btn" onclick="applyFilters(${totalPages}, ${pageSize})">${totalPages}</button>`;
    }

    if (page < totalPages) {
        paginationHtml += `<button class="page-btn" onclick="applyFilters(${page + 1}, ${pageSize})"><i class="bi bi-chevron-double-right"></i></button>`;
    }

    paginationHtml += `</div>`;
    $("#pagination").html(paginationHtml);
};

// Event Listeners
$(document).ready(function () {

    // Load time slots on init
    loadTimeSlots();

    $('#date-filter').change(function () {
        if ($(this).val() === 'custom') {
            $('.custom-date-group').show();
        } else {
            $('.custom-date-group').hide();
        }
        applyFilters(1);
    });

    // Trigger filter on change for all inputs including rows-per-page
    $('#start-date, #end-date, #status-filter, #order-type-filter, #payment-method-filter, #selected-date-filter, #time-slot-filter, #rows-per-page').change(function () {
        applyFilters(1);
    });

    $('#apply-filters').click(function () {
        applyFilters(1);
    });

    $('#reset-filters').click(function () {
        $('#date-filter').val('all').trigger('change');
        $('#start-date').val('');
        $('#end-date').val('');
        $('#status-filter').val('all');
        $('#order-type-filter').val('all');
        $('#payment-method-filter').val('all');
        $('#selected-date-filter').val('');
        $('#time-slot-filter').val('all');
        $('#rows-per-page').val('10');
        $('#search-input').val('');
        applyFilters(1);
    });

    // Hook up search button too
    $('#search-button').click(function () {
        applyFilters(1);
    });

    // Optional: Search on enter in input
    $('#search-input').keypress(function (e) {
        if (e.which == 13) {
            applyFilters(1);
        }
    });
});


function getStatusClass(status) {
    if (!status) return '';
    switch (status.toLowerCase()) {
        case 'pending': return 'status-pending';
        case 'confirmed': return 'status-confirmed';
        case 'shipped': return 'status-shipped';
        case 'out for delivery': return 'status-out-for-delivery';
        case 'delivered': return 'status-delivered';
        case 'cancelled':
        case 'canceled': return 'status-cancelled';
        case 'returned': return 'status-returned';
        default: return '';
    }
}



let orderStatus = '';
var statuses = ["Pending", "Confirmed", "Shipped", "Out for Delivery", "Delivered", "Cancelled"];
var $statusSelect = $('#status');
$statusSelect.empty();

let orderDatas = [];

// let latestOrderData  ='';

// --- MAP LOGIC ---
const openMapModal = (lat, lng, address) => {
    // Ensure we start maximized
    $('#mapModal').removeClass('minimized').css('display', 'block');

    let src = '';

    // Priority 1: Use Coordinates if available
    if (lat && lng && lat !== 0 && lng !== 0) {
        // q=lat,lng
        // z=17 (high zoom), t=m (map)
        src = `https://maps.google.com/maps?q=${lat},${lng}&t=m&z=17&ie=UTF8&output=embed`;
    }
    // Priority 2: Use Address
    else if (address) {
        let query = encodeURIComponent(address);
        // iwloc=A forces marker on the location found
        src = `https://maps.google.com/maps?q=${query}&t=m&z=15&ie=UTF8&iwloc=A&output=embed`;
    } else {
        alert("Location data not available.");
        $('#mapModal').css('display', 'none');
        return;
    }

    $('#google-map-iframe').attr('src', src);
};

const closeMapModal = () => {
    $('#mapModal').css('display', 'none');
    $('#google-map-iframe').attr('src', ''); // Stop loading/video
};

const toggleMinimizeMap = () => {
    const $modal = $('#mapModal');
    $modal.toggleClass('minimized');

    // Update icon if needed (optional)
    const $icon = $modal.find('.bi-dash-lg, .bi-fullscreen');
    if ($modal.hasClass('minimized')) {
        $icon.removeClass('bi-dash-lg').addClass('bi-fullscreen'); // Show maximize icon
    } else {
        $icon.removeClass('bi-fullscreen').addClass('bi-dash-lg'); // Show minimize icon
    }
};
// -----------------

const viewOrderDetails = (orderData) => {

    let scrollData = getScrollData();
    lastScrollPosition = scrollData.top;
    scrollTargetSelector = scrollData.selector;

    console.log(`Saving scroll position: ${lastScrollPosition} on element: ${scrollTargetSelector}`);
    orderDatas = orderData;

    console.log("orderDatas : ", orderDatas);

    $(".result").hide();
    $(".headline").hide();
    $(".order-detail-cotainer").show();
    $(".backToOrder").show();

    $(".orderid").html(`#${orderData.id}`);
    $(".orderdate").html(`${orderData.date}`);
    $(".ordertime").html(`${orderData.time}`);
    $(".orderstatus").html(`${orderData.status}`);
    $(".paymentmethode").html(`${orderData.payment_method}`);
    $(".promoDiscount").html(`- ₹ ${orderData.coupon_amount || 0}`);

    orderidfr = orderData.idfr;
    let formdata = new FormData();
    formdata.append('type', 'fetchOrderStatus');
    formdata.append('id', orderData.id);

    // Reset Map Button state
    $('#view-map-btn').hide().off('click');

    async function getOrderStatus() {
        try {
            let response = await fetch(apiurl, {
                method: 'POST',
                body: formdata
            });

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            let data = await response.json();

            if (data !== 'error' && data !== null) {

                return data; // Async function mein return directly resolve karta hai
            } else {
                throw new Error('Error: Invalid response');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            throw error;
        }
    }

    // Function call
    (async () => {
        try {
            let latestOrderData = await getOrderStatus();

            console.log('Order Status:', latestOrderData.status);

            orderStatus = latestOrderData.status.toLowerCase();

            $statusSelect.empty(); // clear existing options before appending

            statuses.forEach(function (status) {
                $statusSelect.append($('<option>', {
                    value: status.toLowerCase(),
                    text: status
                }));
            });

            // API se status ko set karna

            $statusSelect.val(orderStatus);

            // Initial disabling based on API status
            disableOptionsBasedOnStatus(orderStatus);
            // });

        } catch (error) {
            console.error('Error:', error);
        }
    })();


    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'viewOrderDetails', idfr: orderData.idfr },
        success: function (response) {

            if (response != 'error' && response != null) {
                let data = JSON.parse(response);

                let html = '';
                let itemPrice = 0;
                let totalMrp = 0;
                data.forEach((item, index) => {

                    itemPrice += item.nop * item.selling_price;
                    totalMrp += item.nop * item.mrp;
                    let productPriceDetails = '';
                    if (item.product_type == 'gift') {
                        productPriceDetails = `
                        <td>Free</td>
                        <td>Free</td>
                        <td>Free</td>
                    `;
                    } else {
                        productPriceDetails = `
                            <td>₹ ${item.selling_price}</td>
                            <td>₹ ${item.mrp - item.selling_price}</td>
                            <td>₹ ${item.nop * item.selling_price}</td>
                        `;
                    }
                    html += `
                        <tr>
                            <td class="sl">${index + 1}</td>
                            <td> <img class="order_detail_img" src="${imgurl + item.image_path}" alt="${item.name}"></td>
                            <td>${item.name}</td>
                            <td>${item.quantity} ${item.unit}</td>
                            <td>${item.nop}</td>
                            ${productPriceDetails}
                        </tr>
                    `;
                });
                $("#order-items").html(html);
                let totalDiscount = totalMrp - itemPrice;
                $(".itemPrice").html(`₹ ${totalMrp}`);
                $(".totalDiscount").html(`₹ ${totalDiscount}`);
                $(".subTotal").html(`₹ ${itemPrice}`);
                $(".handlingCharge").html(`₹ ${orderData.handling_charge}`);
                $(".delCharege").html(`₹ ${orderData.del_charge}`);
                $(".grandTotal").html(`₹ ${orderData.total}`);


                $.ajax({
                    url: apiurl,
                    type: 'POST',
                    data: { type: 'viewOrderUserDetails', user_id: orderData.user_id, address_id: orderData.address_id },
                    success: function (response) {
                        if (response != 'error' && response != null) {
                            let data = JSON.parse(response);
                            let user = data[0];

                            // Address formatting
                            const addressParts = [
                                user.o_floor ? `Floor ${user.o_floor}` : "",
                                user.street?.trim(),
                                user.area?.trim(),
                                user.city?.trim(),
                                user.state?.trim(),
                                user.pin_code?.trim()
                            ];

                            const fullAddress = addressParts.filter(Boolean).join(', ');

                            // Display in DOM
                            $(".customerName").html(`${user.full_name}`);
                            $(".customerEmail").html(`${user.email}`);
                            $(".customerPhone").html(`${user.mobile}`);
                            $(".customerAddress").html(fullAddress || "Address not available");

                            // Map Logic
                            let lat = parseFloat(user.latitude);
                            let lng = parseFloat(user.longitude);

                            if (fullAddress || (lat !== 0 && lng !== 0)) {
                                $('#view-map-btn').show().html('<i class="bi bi-geo-alt-fill"></i> View on Map').on('click', function () {
                                    // If we have coords, pass them. If not, pass 0,0 and logic will handle fetch.
                                    let passLat = (!isNaN(lat) && lat !== 0) ? lat : 0;
                                    let passLng = (!isNaN(lng) && lng !== 0) ? lng : 0;
                                    openMapModal(passLat, passLng, fullAddress);
                                });
                            }
                        }
                    }
                });


            }
        }
    })



    //  get delivery boy 

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'assignedDeliveryBoy', order_id: orderData.id },
        success: function (response) {

            if (response != 'error' && response != null) {
                let data = JSON.parse(response);

                $(".delivery_boy_card").show();
                $(".open_delivery_boy_btn").prop('disabled', true);
                $(".delivery_boy_avatar").html(`
                    <img src="${imgurl + data[0].image_path}" alt="Deliveryman">
                    `);

                $(".delivery_boy_name").html(data[0].first_name);
                $(".delivery_boy_name").html(data[0].first_name);
                $(".delivery_boy_phone").html(data[0].mobile_number);
                $(".delivery_boy_gmail").html(data[0].email);

            } else {
                $(".delivery_boy_card").hide();
            }
        }
    })




}







// Select box change event
$statusSelect.on('change', function () {

    let status = $(this).val();
    let currentRecordStatus = orderDatas.status ? orderDatas.status.toLowerCase() : '';

    if (status === currentRecordStatus) {
        console.log("Status unchanged, skipping API call.");
        return;
    }

    // API se status ko set karna
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'updateOrderStatus', id: orderDatas.id, status: status },
        success: function (response) {
            if (response == 'success') {
                successAlert("successfully updated");
                sendPushNotification(orderDatas.id, status);

                    // Real-time update in Order Details
                    $(".orderstatus").text(status);

                    // Update global data source

                    // Force String comparison and Trim to avoid mismatch
                    let targetId = String(orderDatas.id).trim();
                    let orderIndex = allOrders.findIndex(o => String(o.id).trim() === targetId);

                    console.log("Updating order status. Index:", orderIndex, "New Status:", status, "Target ID:", targetId);

                    if (orderIndex !== -1) {
                        allOrders[orderIndex].status = status;
                        // Force update the orderDatas reference too, just in case
                        orderDatas.status = status;
                    } else {
                        console.error("Could not find order in global list to update:", orderDatas.id);
                        // Emergency: Since we can't find it to update locally, we MUST reload the list
                        // But reloadOrder is async. 
                        // Let's try to reload page 1 if all else fails, or current page
                        loadOrder(currentPage, currentSize);
                        return; // Exit here, loadOrder will handle render
                    }

                    // Update local orderDatas
                    orderDatas.status = status;

                    // Remove old status classes and add new one
                    $(".orderstatus")
                        .removeClass("status-pending status-confirmed status-shipped status-out-for-delivery status-delivered status-cancelled status-returned")
                        .addClass(getStatusClass(status));

                    // Re-render list with current state instead of full reload
                    console.log("Re-rendering list with page:", currentPage, "size:", currentSize);
                    applyFilters(currentPage, currentSize);
                }
            }
        });
    disableOptionsBasedOnStatus($(this).val());
});


// Function for disabling previous options
function disableOptionsBasedOnStatus(selectedValue) {
    // console.log("selectedValue : " ,selectedValue)
    $('#status option').prop('disabled', false);
    var $statusSelect = $('#status');
    if (selectedValue === 'Delivered') {
        $('#status option').prop('disabled', true);
        $statusSelect.val('Delivered');
    } else {
        $('#status option').each(function () {
            if ($(this).val() === selectedValue) {
                return false;
            }
            $(this).prop('disabled', true);
        });
    }
}


$(".backToOrder").click(() => {

    $(".result").show();
    $(".headline").show();
    $(".order-detail-cotainer").hide();
    $(".backToOrder").hide();

    $("#status").empty();
    n = 0;
    $(".open_delivery_boy_btn").prop('disabled', false);

    // Restore scroll position with a slight delay to allow DOM reflow
    setTimeout(() => {
        console.log(`Restoring scroll position to: ${lastScrollPosition} on ${scrollTargetSelector}`);

        let $target;
        if (scrollTargetSelector === 'window') {
            $target = $('html, body'); // Target both for max compatibility
        } else {
            $target = $(scrollTargetSelector);
        }

        $target.scrollTop(lastScrollPosition);

        // Final check/retry
        setTimeout(() => {
            // If we failed, force body/html as backup
            if ($target.scrollTop() < lastScrollPosition && scrollTargetSelector !== 'window') {
                console.log("Primary target failed, trying html,body");
                $('html, body').scrollTop(lastScrollPosition);
            }
        }, 100);
    }, 100);
})




const openBill = () => {


    $(".billOrderId").html(`#${orderDatas.id}`);
    let currentDate = new Date();
    let formattedDate = currentDate.toLocaleString(); // Local date and time format
    $(".billOrderDate").html(formattedDate);

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadBillingDetails' },
        success: function (response) {
            if (response != 'error' && response != null) {
                let data = JSON.parse(response);

                $(".billing_address").html(`
                    ${data[0].address} <br>
                    Phone : ${data[0].phone_number} <br>
                    `);

            }
        }
    })

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'viewOrderDetails', idfr: orderDatas.idfr },
        success: function (response) {

            if (response != 'error' && response != null) {
                let data = JSON.parse(response);

                let html = '';
                let itemPrice = 0;
                let totalMrp = 0;
                data.forEach((item, index) => {

                    itemPrice += item.nop * item.selling_price;
                    totalMrp += item.nop * item.mrp;
                    let productDetails = '';
                    let productPrice = '';
                    if (item.product_type == 'gift') {
                        productDetails = `
                        <td>${item.name}</td>
                        `;
                        productPrice = `
                        <td class="bprice">Free</td>
                        `;
                    } else {
                        productDetails = `
                        <td>${item.name}<br>Unit Price: ₹ ${item.selling_price} <br>
                        Nop : ${item.nop}
                        <br>Discount: ₹ ${item.mrp - item.selling_price}</td>
                        `;
                        productPrice = `
                        <td class="bprice">₹ ${item.nop * item.selling_price}</td>
                        `;
                    }
                    html += `
                    <tr>
                        <td>${item.quantity} ${item.unit}</td>
                        ${productDetails}
                        ${productPrice}
                    </tr>
                    `;
                });

                $(".bill-items").html(html);
                let totalDiscount = totalMrp - itemPrice;

                function formatPrice(value) {
                    return `₹ ${parseFloat(value || 0).toFixed(2)}`;
                }

                $(".billtotalItesPrice").html(formatPrice(totalMrp));
                $(".billtotalDiscount").html(formatPrice(totalDiscount));
                $(".billsubTotal").html(formatPrice(itemPrice));
                $(".handlingCharge").html(formatPrice(orderDatas?.handling_charge));
                $(".billdelCharege").html(formatPrice(orderDatas?.del_charge));
                $(".billgrandTotal").html(`<strong>${formatPrice(orderDatas?.total)}</strong>`);

                // $.ajax({
                //     url: apiurl,
                //     type: 'POST',
                //     data: { type: 'viewOrderUserDetails', user_id: orderData.user_id, address_id: orderData.address_id },
                //     success: function (response) {
                //         if (response != 'error' && response != null) {
                //             let data = JSON.parse(response);
                //             console.log(data);
                //             $(".customerName").html(`${data[0].full_name}`);
                //             $(".customerEmail").html(`${data[0].email}`);
                //             $(".customerPhone").html(`${data[0].mobile}`);
                //             $(".customerAddress").html(`${data[0].address}`);
                //         }
                //     }
                // })

            }
        }
    })




    $(".wrapper-overlay").addClass('active');
    $(".bill-modal").addClass('active');
    $('body').css('overflow', 'hidden');

}


const closeBillModel = () => {
    $(".wrapper-overlay").removeClass('active');
    $(".bill-modal").removeClass('active');
    $('body').css('overflow', 'auto');
}



const printBill = () => {

    window.print();
}

const printOrderBill = (item) => {

    let orderDatas = item;

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadBillingDetails' },
        success: function (response) {
            if (response != 'error' && response != null) {
                let data = JSON.parse(response);

                $(".billing_address").html(`
                    ${data[0].address} <br>
                    Phone : ${data[0].phone_number} <br>
                    `);

            }
        }
    })


    $(".billOrderId").html(`#${orderDatas.id}`);
    let currentDate = new Date();
    let formattedDate = currentDate.toLocaleString(); // Local date and time format
    $(".billOrderDate").html(formattedDate);

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'viewOrderDetails', idfr: orderDatas.idfr },
        success: function (response) {

            if (response != 'error' && response != null) {
                let data = JSON.parse(response);

                let html = '';
                let itemPrice = 0;
                let totalMrp = 0;
                data.forEach((item, index) => {

                    itemPrice += item.nop * item.selling_price;
                    totalMrp += item.nop * item.mrp;

                    let productDetails = '';
                    let productPrice = '';
                    if (item.product_type == 'gift') {
                        productDetails = `
                        <td>${item.name}</td>
                        `;
                        productPrice = `
                        <td class="bprice">Free</td>
                        `;
                    } else {
                        productDetails = `
                        <td>${item.name}<br>Unit Price: ₹ ${item.selling_price}<br>
                        Nop : ${item.nop}
                        <br>Discount: ₹ ${item.mrp - item.selling_price}</td>
                        `;
                        productPrice = `
                        <td class="bprice">₹ ${item.nop * item.selling_price}</td>
                        `;
                    }
                    html += `
                    <tr>
                        <td>${item.quantity} ${item.unit}</td>
                        ${productDetails}
                        ${productPrice}
                    </tr>
                    `;
                });

                $(".bill-items").html(html);
                let totalDiscount = totalMrp - itemPrice;

                function formatPrice(value) {
                    return `₹ ${parseFloat(value || 0).toFixed(2)}`;
                }

                $(".billtotalItesPrice").html(formatPrice(totalMrp));
                $(".billtotalDiscount").html(formatPrice(totalDiscount));
                $(".billsubTotal").html(formatPrice(itemPrice));
                $(".handlingCharge").html(formatPrice(orderDatas?.handling_charge));
                $(".billdelCharege").html(formatPrice(orderDatas?.del_charge));
                $(".billgrandTotal").html(`<strong>${formatPrice(orderDatas?.total)}</strong>`);



            }
        }
    })





    $(".wrapper-overlay").addClass('active');
    $(".bill-modal").addClass('active');
    $('body').css('overflow', 'hidden');

}




const changeDeliveryman = () => {
    let status = $("#status").val();
    console.log(status);
    if (status.toLowerCase() == 'delivered') {

    }
}
















// ======================== delivery boy model open ========================================





const openDeliveryBoyModel = () => {
    $("#driverModal").show();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'deliveryBoyList' },
        success: function (response) {
            if (response != 'error') {
                let data = JSON.parse(response);
                let deliveryBoyList = '';
                data.forEach((item) => {
                    deliveryBoyList += `
                    <div class="driver-card">
                    <img src="${imgurl + item.image_path}" alt="Driver">
                    <div class="driver-info">
                        <strong>${item.first_name} ${item.last_name}</strong>
                    </div>
                    <button class="assign-btn" onclick="assignOrder(${item.id})">Assign Order</button>
                </div>
                    `;
                })

                $(".driver_list").html(deliveryBoyList);
            }
        }
    })

}

const closeDeliveryBoyModel = () => {
    $("#driverModal").hide();
}

function assignOrder(id) {
    let status = $("#status").val();

    let delivery_date = $("#delivery-date").val();
    let delivery_time = $("#delivery-time").val();

    let orderid = $(".orderid").html();


    orderid = orderid.replace('#', '');

    console.log(id, status, orderid)

    if (status == 'pending') {
        errorAlert('Please Confirmed the order before proceeding.');
        return;
    }

    // if (delivery_date == null || delivery_date == '') {
    //     errorAlert('Please set the date before proceeding.');
    //     return;
    // }

    // if (delivery_time == null || delivery_time == '') {
    //     errorAlert('Please set the time before proceeding.');
    //     return;
    // }

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'assignOrder', orderid: orderid, driver_id: id, delivery_date: delivery_date, delivery_time: delivery_time },
        success: function (response) {
            if (response == 'success') {
                successAlert('This order has successfully assigned.');

                sendAssignedDeliveryBoyPushNotification(id);
            } else {
                errorAlert(response);
            }
        }
    })


}

// Escape key press to close
window.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeModal();
});







//  send order push notification

function encrypt(data) {
    return btoa(data);
}
const sendPushNotification = (id, status) => {

    const formData = new FormData();
  formData.append('dbUserNm', encrypt('u373855149_bachatfresh2'));
  formData.append('dbPass', encrypt('Bachatfresh2@123'));
  formData.append('dbName', encrypt('u373855149_bachatfresh2'));
  formData.append('projectId', 'bachat-fresh-kirana-466e6');
  formData.append('pvKeyUrl', 'https://indiantechsolution.com/pvkey/bachatfresh2/pvkey.json');
    formData.append('id', id);
    formData.append('status', status);


    fetch('https://indiantechsolution.com/push_notification/send_order_push.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => console.log('Notification sent successfully'))
        .catch((error) => console.error('Error:', error));

}

const sendAssignedDeliveryBoyPushNotification = (id) => {

    console.log("sendAssignedDeliveryBoyPushNotification called with id:", id);

    const formData = new FormData();
    formData.append('dbUserNm', encrypt('u373855149_bachatfresh2'));
    formData.append('dbPass', encrypt('Bachatfresh2@123'));
    formData.append('dbName', encrypt('u373855149_bachatfresh2'));
    formData.append('projectId', 'bachat-fresh--delivery-partner');
    formData.append('pvKeyUrl', 'https://indiantechsolution.com/deliverypartner/bachatfresh2/pvkey.json');
    formData.append('driverId', id);



    fetch('https://indiantechsolution.com/push_notification/assign_order_push.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => console.log('Notification sent successfully'))
        .catch((error) => console.error('Error:', error));

}