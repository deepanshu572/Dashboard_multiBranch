const apiurl = window.location.pathname.includes('pages/') ? '../api/' : 'api/';
const imgurl = `../`;

console.log(apiurl)

// Global AJAX Interceptor to append staff username for Audit Logging and handle Auth Errors
$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
    // 0. Ensure credentials (sessions) are sent
    options.xhrFields = options.xhrFields || {};
    options.xhrFields.withCredentials = true;

    // 1. Audit Logging
    if (options.type && options.type.toUpperCase() === "POST") {
        let loggedUser = localStorage.getItem('admin_username') || 'Unknown';
        if (typeof options.data === "string") {
            options.data += "&log_admin_username=" + encodeURIComponent(loggedUser);
        } else if (window.FormData && options.data instanceof FormData) {
            options.data.append('log_admin_username', loggedUser);
        }
    }

    // 2. Auth Error Handling
    const originalSuccess = options.success;
    options.success = function(response) {
        try {
            let res = typeof response === 'string' ? JSON.parse(response) : response;
            if (res && res.status === 'auth_error') {
                localStorage.clear();
                location.href = (window.location.pathname.includes('pages/')) ? '../index.html' : 'index.html';
                return;
            }
        } catch (e) {}
        if (originalSuccess) originalSuccess.apply(this, arguments);
    };
});

// Global Fetch Interceptor to append staff username for Audit Logging and handle session
// const originalFetch = window.fetch;
// window.fetch = async function() {
//     let [resource, config] = arguments;
//     config = config || {};
    
//     // Ensure credentials (sessions) are sent for fetch too
//     config.credentials = 'include';

//     if (config.method && config.method.toUpperCase() === 'POST' && config.body) {
//         let loggedUser = localStorage.getItem('admin_username') || localStorage.getItem('admin_role') || 'Unknown';
        
//         if (typeof config.body === 'string') {
//             if (config.body.indexOf('log_admin_username=') === -1) {
//                 config.body += "&log_admin_username=" + encodeURIComponent(loggedUser);
//             }
//         } else if (window.FormData && config.body instanceof FormData) {
//             if (!config.body.has('log_admin_username')) {
//                 config.body.append('log_admin_username', loggedUser);
//             }
//         }
//     }

//     const response = await originalFetch(resource, config);
    
//     // Clone response to check body without consuming it
//     const clone = response.clone();
//     try {
//         const json = await clone.json();
//         if (json && json.status === 'auth_error') {
//             localStorage.clear();
//             location.href = (window.location.pathname.includes('pages/')) ? '../index.html' : 'index.html';
//         }
//     } catch (e) { }

//     return response;
// };
const originalFetch = window.fetch;

window.fetch = async function(resource, config = {}) {

    // Get request URL
    const url = typeof resource === "string" ? resource : resource.url;

    // Check if request is to your own server
    const isLocalRequest =
        url.startsWith(window.location.origin) ||
        url.startsWith("/") ||
        !url.startsWith("http");

    // Send credentials ONLY for your own APIs
    if (isLocalRequest) {
        config.credentials = "include";
    } else {
        config.credentials = "omit";
    }

    // Append username only to your own POST requests
    if (
        isLocalRequest &&
        config.method &&
        config.method.toUpperCase() === "POST" &&
        config.body
    ) {
        let loggedUser =
            localStorage.getItem("admin_username") ||
            localStorage.getItem("admin_role") ||
            "Unknown";

        if (typeof config.body === "string") {

            if (!config.body.includes("log_admin_username=")) {
                config.body +=
                    "&log_admin_username=" +
                    encodeURIComponent(loggedUser);
            }

        } else if (config.body instanceof FormData) {

            if (!config.body.has("log_admin_username")) {
                config.body.append("log_admin_username", loggedUser);
            }

        }
    }

    const response = await originalFetch(resource, config);

    // Check auth only for your own APIs
    if (isLocalRequest) {
        const clone = response.clone();

        try {
            const json = await clone.json();

            if (json?.status === "auth_error") {
                localStorage.clear();
                location.href = window.location.pathname.includes("pages/")
                    ? "../index.html"
                    : "index.html";
            }

        } catch (e) {}
    }

    return response;
};


$(".sidebar-box").click(function () {
    const down = $(this).find('.down');
    down.toggleClass('active');
    const subBox = $(this).children('.sub-box');
    if (down.hasClass('active')) {
        subBox.css("marginTop", "15px");
        subBox.css("height", "auto");
        const height = subBox.outerHeight();
        subBox.css("height", "0");
        subBox.show().animate({ height: height }, 300);
    } else {
        subBox.css("marginTop", "0px");
        subBox.css("height", "0");
    }
})

$(".navbar .bi-chevron-bar-right").click(() => {
    $(".sidebar").addClass("active");
    $(".main-container").css("paddingLeft", "220px");
})
$(".sidebar-header i").click(() => {
    $(".sidebar").removeClass("active");
    $(".main-container").css("paddingLeft", "5%");
})


$(".dashBox").click(() => {
    location.href = 'dashboard.html';
})
$(".brandsBox").click(() => {
    location.href = 'brands.html';
})
$(".branchBox").click(() => {
    location.href = 'branch.html';
})
$(".productBox").click(() => {
    location.href = 'product.html';
})
$(".categoryBox").click(() => {
    location.href = 'category.html';
})
$(".bottomCategoryBox").click(() => {
    location.href = 'middle-Category.html';
})
$(".subCategoryBox").click(() => {
    location.href = 'sub-category.html';
})
$(".bannerBox").click(() => {
    location.href = 'banner.html';
})

$(".posBoxNewList").click(() => {
    location.href = 'pos.html';
})
$(".posBoxOrders").click(() => {
    location.href = 'pos-order.html';
})

$(".orderBoxAll").click(() => {
    location.href = 'order.html?type=all';
})
$(".orderBoxPending").click(() => {
    location.href = 'order.html?type=pending';
})
$(".orderBoxConfirmed").click(() => {
    location.href = 'order.html?type=confirmed';
})

$(".orderBoxPackaging").click(() => {
    location.href = 'order.html?type=packaging';
})

$(".orderBoxDelivered").click(() => {
    location.href = 'order.html?type=delivered';
})

$(".orderBoxReturned").click(() => {
    location.href = 'order.html?type=returned';
})

// $(".orderBoxReturned").click(()=>{
//     location.href='order.html?type=returned';
// })

$(".orderBoxCanceled").click(() => {
    location.href = 'order.html?type=cancelled';
})

$(".customerListBox").click(() => {
    location.href = 'user.html?type=list';
})
$(".customerWalletBox").click(() => {
    location.href = 'user.html?type=wallet';
})
$(".customerFundBox").click(() => {
    warningAlert('This will be an add-on')
})

$(".customerReportBox").click(() => {
    warningAlert('This will be an add-on')
})
$(".flashSaleBox").click(() => {
    location.href = 'flash-sale.html';
})
$(".categoryDiscountBox").click(() => {
    warningAlert('This will be an add-on')
})
$(".productReviewBox").click(() => {
    warningAlert('This will be an add-on')
})
$(".deliveryManBox").click(() => {
    location.href = 'delivery-man.html';
})
$(".staffBox").click(() => {
    location.href = 'staff.html';
})
$(".couponsBox").click(() => {
    location.href = 'coupon.html';
})

$(".salesReportBox").click(() => {
    location.href = 'sales-report.html';
})

$(".activityReportBox").click(() => {
    location.href = 'activity-report.html';
})

$(".deliveryReportBox").click(() => {
    location.href = 'delivery-report.html';
})

$(".otherSetupBox").click(() => {
    location.href = 'other.html';
})

$(".pushNotificationBox").click(() => {
    location.href = 'push-notification.html';
})




const successAlert = (msg) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'colored-toast'
        }
    });
};

const errorAlert = (msg) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'colored-toast'
        }
    });
};

const warningAlert = (msg) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'colored-toast'
        }
    });
};




function showConfirmationDialog(message) {
    return Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        customClass: {
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        }
    }).then((result) => {
        return result.isConfirmed;
    });
}




const adminLogin = () => {



    let username = $("#username").val();
    let password = $("#password").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'adminLogin', username: username, password: password },
        success: function (response) {
            let res;
            try {
                res = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (res && res.status == 'success') {
                    localStorage.setItem('admin_login_status', true);
                    localStorage.setItem('admin_role', res.role || 'admin');
                    localStorage.setItem('admin_permissions', res.permissions || 'all');
                    localStorage.setItem('admin_username', res.username || username);
                    location.href = 'pages/dashboard.html';
                } else if (res && res.status == 'disabled') {
                    errorAlert('Your account has been disabled.');
                } else {
                    errorAlert('Invalid username or password');
                }
            } catch (e) {
                console.error("Login Response Error:", response);
                if (typeof response === 'string' && response.trim() == 'success') {
                    localStorage.setItem('admin_login_status', true);
                    location.href = 'pages/dashboard.html';
                } else {
                    errorAlert('Login failed: Malformed response from server');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            errorAlert('Network error: Could not connect to server');
        }
    })
}

// Logout handler removed from here and moved to $(document).ready()

const checkLoginStatus = () => {
    let localStatus = localStorage.getItem('admin_login_status');
    let currentPage = window.location.pathname.split('admin/').pop();
    console.log(currentPage);
    if (currentPage === 'index.html' || currentPage === '') {
        if (localStatus === 'true') {
            location.href = 'pages/dashboard.html';
        }
        return;
    }

    if (localStatus !== 'true') {
        location.href = (window.location.pathname.includes('pages/')) ? '../index.html' : 'index.html';
        return;
    }

    // Deep check with backend to ensure session is still valid
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'checkAuth' },
        success: function(response) {
            try {
                let res = typeof response === 'string' ? JSON.parse(response) : response;
                if (res.status !== 'success') {
                    localStorage.clear();
                    location.href = (window.location.pathname.includes('pages/')) ? '../index.html' : 'index.html';
                } else {
                    // Update localStorage with latest server data in case of manual manipulation
                    localStorage.setItem('admin_role', res.role);
                    localStorage.setItem('admin_permissions', res.permissions);
                    localStorage.setItem('admin_username', res.username);
                    applyRBAC(); // Re-apply RBAC with verified data
                }
            } catch (e) {
                console.error("Auth check failed", e);
            }
        }
    });
}

checkLoginStatus();

const applyRBAC = () => {
    let role = localStorage.getItem('admin_role');
    let permissions = localStorage.getItem('admin_permissions');

    // Only 'admin' role can see Manage Staff
    if (role !== 'admin') {
        $("#manageStaffMenu").hide();
    }
    
    // Granular menu hiding based on `permissions` string parsing
    if (role === 'staff' && permissions && permissions !== 'all') {
        let permsArray = permissions.split(',');
        
        // --- Product & Category Setup ---
        if (!permsArray.includes('add_category') && !permsArray.includes('Category')) $(".categoryBox").hide();
        if (!permsArray.includes('add_subcategory') && !permsArray.includes('Category')) $(".subCategoryBox").hide();
        if (!permsArray.includes('add_brands') && !permsArray.includes('Product')) $(".brandsBox").hide();
        if (!permsArray.includes('add_product') && !permsArray.includes('Product')) $(".productBox").hide();
        
        if (!permsArray.includes('add_category') && !permsArray.includes('add_subcategory') && !permsArray.includes('Category')) {
            $(".categoryBox").parent().parent().hide(); // Hide parent dropdown
        }

        // --- POS Billing ---
        if (!permsArray.includes('pos_new_sale')) $(".posBoxNewList").hide();
        if (!permsArray.includes('pos_orders')) $(".posBoxOrders").hide();
        if (!permsArray.includes('pos_new_sale') && !permsArray.includes('pos_orders')) {
            $(".posBox").parent().parent().hide(); // Hide parent dropdown
        }

        // --- Order Management ---
        if (!permsArray.includes('orders_all') && !permsArray.includes('Order')) $(".orderBoxAll").hide();
        if (!permsArray.includes('orders_pending') && !permsArray.includes('Order')) $(".orderBoxPending").hide();
        if (!permsArray.includes('orders_confirmed') && !permsArray.includes('Order')) $(".orderBoxConfirmed").hide();
        if (!permsArray.includes('orders_packaging') && !permsArray.includes('Order')) $(".orderBoxPackaging").hide();
        if (!permsArray.includes('orders_delivered') && !permsArray.includes('Order')) $(".orderBoxDelivered").hide();
        if (!permsArray.includes('orders_returned') && !permsArray.includes('Order')) $(".orderBoxReturned").hide();
        if (!permsArray.includes('orders_canceled') && !permsArray.includes('Order')) $(".orderBoxCanceled").hide();
        
        if (!permsArray.includes('orders_all') && !permsArray.includes('orders_pending') && 
            !permsArray.includes('orders_confirmed') && !permsArray.includes('orders_packaging') &&
            !permsArray.includes('orders_delivered') && !permsArray.includes('orders_returned') &&
            !permsArray.includes('orders_canceled') && !permsArray.includes('Order')) {
            $(".orderBoxAll").parent().parent().hide(); // Hide parent 'Orders' dropdown
        }

        // --- Promotions ---
        if (!permsArray.includes('banners')) $(".bannerBox").hide();
        if (!permsArray.includes('coupons')) $(".couponsBox").hide();
        if (!permsArray.includes('flash_sale')) $(".flashSaleBox").hide();
        if (!permsArray.includes('category_discount')) $(".categoryDiscountBox").hide();

        // --- User Management ---
        if (!permsArray.includes('user_management')) {
            $(".customerListBox").parent().parent().hide(); // Hide parent dropdown
        }

        // --- General ---
        if (!permsArray.includes('dashboard_stats')) {
            $(".dashBox").hide();
            if (window.location.pathname.includes('dashboard.html')) {
                $(".dashboard-card-grid, .order-chart, .sales-chart").hide();
            }
        }
        if (!permsArray.includes('sales_report')) $(".salesReportBox").hide();
        if (!permsArray.includes('delivery_report')) $(".deliveryReportBox").hide();
        if (!permsArray.includes('delivery_man')) $(".deliveryManBox").hide();
        if (!permsArray.includes('product_review')) $(".productReviewBox").hide();
        if (!permsArray.includes('push_notification')) $(".pushNotificationBox").hide();
        
        // Hide activity report for all regular staff
        $(".activityReportBox").hide();

        // --- Other Setup (Settings) ---
        // If they have NO permissions inside Other Setup, hide the main Other Setup sidebar link entirely
        const otherSetupPerms = [
            'other_delivery_charge', 'other_min_order', 'other_handle_charge', 'other_gift_product',
            'other_billing_det', 'other_brands_of_day', 'other_password', 'other_time_slot',
            'other_title', 'other_main_title', 'other_hero_banner', 'other_main_banner',
            'other_area', 'other_gift_price', 'other_shop_status', 'other_advanced_set', 'other_contact_info'
        ];
        
        let hasAnyOtherPerm = otherSetupPerms.some(p => permsArray.includes(p));
        if (!hasAnyOtherPerm) {
            $(".otherSetupBox").hide();
        }

        // Granular hiding of specific List Items inside `other.html` dropdown
        if (window.location.pathname.includes('other.html')) {
            if (!permsArray.includes('other_delivery_charge')) $("li[onclick*='delChargeForm']").hide();
            if (!permsArray.includes('other_min_order')) $("li[onclick*='minOrderValueForm']").hide();
            if (!permsArray.includes('other_handle_charge')) $("li[onclick*='handlingChargeForm']").hide();
            if (!permsArray.includes('other_gift_product')) $("li[onclick*='addGiftProductForm']").hide();
            if (!permsArray.includes('other_billing_det')) $("li[onclick*='setBillingDetailsForm']").hide();
            if (!permsArray.includes('other_brands_of_day')) $("li[onclick*='setBrandsOfTheForm']").hide();
            if (!permsArray.includes('other_password')) $("li[onclick*='openModal']").hide();
            if (!permsArray.includes('other_time_slot')) $("li[onclick*='handleTimeSlot']").hide();
            if (!permsArray.includes('other_title')) $("li[onclick*='handleTitle']").hide();
            if (!permsArray.includes('other_main_title')) $("li[onclick*='handleMainTitle']").hide();
            if (!permsArray.includes('other_hero_banner')) $("li[onclick*='handleHeroBanner']").hide();
            if (!permsArray.includes('other_main_banner')) $("li[onclick*='handleMainBanner']").hide();
            if (!permsArray.includes('other_area')) $("li[onclick*='handleArea']").hide();
            if (!permsArray.includes('other_gift_price')) $("li[onclick*='setGiftPrice']").hide();
            if (!permsArray.includes('other_shop_status')) $("li[onclick*='shopModelOpen']").hide();
            if (!permsArray.includes('other_advanced_set')) $("li[onclick*='shopAdvancedSettings']").hide();
            if (!permsArray.includes('other_contact_info')) $("li[onclick*='shopContactOpen']").hide();
        }
    }
}

applyRBAC();

/* =======       responsive.js        ========= */

function setupSidebarForSmallScreens() {
    if (window.innerWidth <= 999) {
        $(".navbar .bi-chevron-bar-right").off("click").on("click", () => {
            $(".sidebar").addClass("active");
            $(".main-container").css("paddingLeft", "5%");
        });

        $(".sidebar-header i").off("click").on("click", () => {
            $(".sidebar").removeClass("active");
            $(".main-container").css("paddingLeft", "5%");
        });
    }
}

// Initial check
setupSidebarForSmallScreens();

// Also run on resize
$(window).on("resize", setupSidebarForSmallScreens);



if (window.innerWidth <= 999) {

    if ($(".sidebar").hasClass("active")) {
        // $(".sidebar").removeClass("active");
    }

}



// new order alert notification


async function checkForNewOrder() {

    try {
        const response = await fetch(apiurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                type: 'checkForNewOrder'
            }),
        });

        const data = await response.json();
        // console.log(data.newOrder)
        return data.newOrder === true;
    } catch (error) {
        console.error('Error checking for new order:', error);
        return false;
    }
}
let alarmAudio = null;
function playAlarm() {
    if (!alarmAudio) {
        alarmAudio = new Audio('https://rasan.in/client/dbimartpro/dashboard/mp3/alarm.mp3'); // 🔁 Replace with your server file path
        alarmAudio.loop = true;
    }

    alarmAudio.play().catch(error => {
        console.error("Audio autoplay failed:", error);
    });
}

function stopAlarm() {
    if (alarmAudio) {
        alarmAudio.pause();
        alarmAudio.currentTime = 0;
    }
}

async function checkOrderAndNotify() {
    let role = localStorage.getItem('admin_role');
    let permissions = localStorage.getItem('admin_permissions') || '';
    
    // Check if user has permission to see orders
    let canSeeOrders = true;
    if (role === 'staff' && permissions !== 'all') {
        let permsArray = permissions.split(',');
        let orderPerms = ['orders_all', 'orders_pending', 'orders_confirmed', 'orders_packaging', 'orders_delivered', 'orders_returned', 'orders_canceled', 'pos_orders'];
        canSeeOrders = orderPerms.some(p => permsArray.includes(p));
    }

    if (!canSeeOrders) return; // Do not fetch or notify if they don't have access

    const hasNewOrder = await checkForNewOrder();
    if (hasNewOrder) {
        // playAlarm();

        Swal.fire({
            title: '🚨 New Order Received!',
            text: 'Click OK to view, or Cancel to dismiss.',
            icon: 'info',
            position: 'top-end',
            toast: true,
            showCancelButton: true,
            confirmButtonText: 'View Order',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'colored-toast',
                confirmButton: 'custom-confirm-button-class',
                cancelButton: 'custom-cancel-button-class'
            },
            didOpen: () => {
                playAlarm();  // ✅ This will now work properly
            }
        }).then((result) => {
            stopAlarm();
            if (result.isConfirmed) {
                window.location.href = 'order.html?type=pending';
            }
        });
    }
}

// Run every 1 minute
setInterval(checkOrderAndNotify, 60000);





// document.addEventListener("click", () => {
//     alarmAudio.play().then(() => {
//         alarmAudio.pause();
//         alarmAudio.currentTime = 0;
//     }).catch(() => { });
// }, { once: true });



// check page reload

const navEntries = performance.getEntriesByType("navigation");
const isReload = navEntries.length > 0 && navEntries[0].type === "reload";

if (isReload) {
    Swal.fire({
        title: "🔔 Click to enable order notification",
        text: "This popup appears because you reloaded the page.",
        icon: "info",
        confirmButtonText: "OK",
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        }
    });
}



const displayUserInfo = () => {
    const username = localStorage.getItem('admin_username') || 'Admin';
    const role = localStorage.getItem('admin_role') || 'Administrator';

    const userHtml = `
        <div class="navbar-user-info">
            <div class="user-details">
                <span class="user-name">${username}</span>
                <span class="user-role">${role}</span>
            </div>
            <div class="user-icon">
                <i class="bi bi-person-circle"></i>
            </div>
        </div>
    `;

    const navbar = $(".navbar");
    if (navbar.length) {
        const logoutBtn = navbar.find("button:contains('Logout')");
        if (logoutBtn.length) {
            let navbarRight = navbar.find(".navbar-right");
            if (navbarRight.length === 0) {
                let wrapTarget = logoutBtn;
                if (logoutBtn.parent().is('div') && !logoutBtn.parent().hasClass('navbar')) {
                    wrapTarget = logoutBtn.parent();
                }
                wrapTarget.wrap('<div class="navbar-right"></div>');
                navbarRight = navbar.find(".navbar-right");
            }

            if (navbar.find(".navbar-user-info").length === 0) {
                navbarRight.append(userHtml);
            }
        }
    }
}

$(document).ready(function() {
    displayUserInfo();
    
    // Improved logout handler
    $(document).on("click", ".navbar button:contains('Logout')", function() {
        showConfirmationDialog('Are you sure you want to logout?').then((result) => {
            if (result) {
                $.ajax({
                    url: apiurl,
                    type: 'POST',
                    data: { type: 'logout' },
                    success: function() {
                        localStorage.clear();
                        location.href = (window.location.pathname.includes('pages/')) ? '../index.html' : 'index.html';
                    }
                });
            }
        });
    });
});

// Remove the old global click listener to avoid conflicts
// $(".navbar button").off("click"); 
// Actually the old one is still there, but by adding it in document ready with on('click') it might double up.
// I'll replace the old one instead.
