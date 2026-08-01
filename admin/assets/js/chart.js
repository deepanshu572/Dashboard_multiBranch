

let orderChart;

const ctx = document.getElementById('orderChart').getContext('2d');

// Initialize chart first with empty/default data
orderChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Orders',
            data: [],
            borderColor: '#005555',
            borderWidth: 2,
            fill: false,
            pointRadius: 3,
            pointBackgroundColor: '#005555'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// ✅ Using dor instead of date
function countOrdersThisYear(orders) {
    const monthlyCounts = Array(12).fill(0); // Jan to Dec
    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const month = new Date(dateStr).getMonth(); // 0 - 11
        monthlyCounts[month]++;
    });
    return monthlyCounts;
}

function countOrdersThisMonth(orders) {
    const dailyCounts = Array(31).fill(0);
    const now = new Date();
    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const orderDate = new Date(dateStr);
        if (orderDate.getFullYear() === now.getFullYear() && orderDate.getMonth() === now.getMonth()) {
            const day = orderDate.getDate(); // 1 - 31
            dailyCounts[day - 1]++;
        }
    });
    return dailyCounts;
}

function countOrdersThisWeek(orders) {
    const weeklyCounts = Array(7).fill(0);
    const now = new Date();
    const oneWeekAgo = new Date(now);
    oneWeekAgo.setDate(now.getDate() - 6);

    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const orderDate = new Date(dateStr);
        if (orderDate >= oneWeekAgo && orderDate <= now) {
            const dayIndex = orderDate.getDay(); // 0 (Sunday) to 6 (Saturday)
            weeklyCounts[dayIndex]++;
        }
    });

    // Rotate so week starts from Monday
    return [weeklyCounts[1], weeklyCounts[2], weeklyCounts[3], weeklyCounts[4], weeklyCounts[5], weeklyCounts[6], weeklyCounts[0]];
}

const buttons = document.querySelectorAll('.tabs button');

buttons.forEach(button => {
    button.addEventListener('click', () => {
        buttons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        const orders = window.allOrders || [];

        if (button.id === 'thisYear') {
            orderChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            orderChart.data.datasets[0].data = countOrdersThisYear(orders);
        } else if (button.id === 'thisMonth') {
            orderChart.data.labels = Array.from({ length: 31 }, (_, i) => (i + 1).toString());
            orderChart.data.datasets[0].data = countOrdersThisMonth(orders);
        } else if (button.id === 'thisWeek') {
            orderChart.data.labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            orderChart.data.datasets[0].data = countOrdersThisWeek(orders);
        }
        else if (button.id === 'today') {
            orderChart.data.labels = ['Today'];
            orderChart.data.datasets[0].data = countOrdersToday(orders);
        }


        orderChart.update();
    });
});





//  =============================================== salesChart ======================================================


const stx = document.getElementById('salesChart').getContext('2d');

const salesChart = new Chart(stx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Sales',
            data: [],
            borderColor: '#005555',
            borderWidth: 2,
            fill: false,
            pointRadius: 3,
            pointBackgroundColor: '#005555'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});



function countSalesThisYear(orders) {

    console.log("orders : ", orders);

    const monthlySales = Array(12).fill(0);
    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const date = new Date(dateStr);
        const amount = parseFloat(order.total || 0);
        monthlySales[date.getMonth()] += amount;
    });
    console.log("monthlySales : ", monthlySales);
    return monthlySales;

}

function countSalesThisMonth(orders) {
    const dailySales = Array(31).fill(0);
    const now = new Date();
    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const date = new Date(dateStr);
        if (date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()) {
            const amount = parseFloat(order.total || 0);
            dailySales[date.getDate() - 1] += amount;
        }
    });
    return dailySales;
}

function countSalesThisWeek(orders) {
    const weekSales = Array(7).fill(0);
    const now = new Date();
    const weekAgo = new Date();
    weekAgo.setDate(now.getDate() - 6);
    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const date = new Date(dateStr);
        if (date >= weekAgo && date <= now) {
            const day = date.getDay();
            const amount = parseFloat(order.total || 0);
            weekSales[day] += amount;
        }
    });
    return [weekSales[1], weekSales[2], weekSales[3], weekSales[4], weekSales[5], weekSales[6], weekSales[0]];
}

const buttons2 = document.querySelectorAll('.tabs2 button');

buttons2.forEach(button => {
    button.addEventListener('click', () => {
        buttons2.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        const deliveredOrders = (window.allOrders || []).filter(order =>
            (order.status || '').toLowerCase().includes("delivered")
        );

        if (button.id === 'sthisYear') {
            salesChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            salesChart.data.datasets[0].data = countSalesThisYear(deliveredOrders);
        } else if (button.id === 'sthisMonth') {
            salesChart.data.labels = Array.from({ length: 31 }, (_, i) => (i + 1).toString());
            salesChart.data.datasets[0].data = countSalesThisMonth(deliveredOrders);
        } else if (button.id === 'sthisWeek') {
            salesChart.data.labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            salesChart.data.datasets[0].data = countSalesThisWeek(deliveredOrders);
        }

        else if (button.id === 'stoday') {
            salesChart.data.labels = ['Today'];
            salesChart.data.datasets[0].data = countSalesToday(deliveredOrders);
        }

        salesChart.update();
    });
});







// ================================================== circle order chart =================================================


let donutChart; // Move this to global scope


const getOrderData = () => {
    let branchId = localStorage.getItem('role_id') || 0;

    const orderData = 
          branchId == 0 ? { type: 'loadOrder' } : { type: 'loadBranchOrder',  branchId };
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: orderData,
        success: function (response) {
            if (response !== 'error' && response !== null) {
                let orders = JSON.parse(response);

                // Save globally if needed later
                window.allOrders = orders;


                // sales chart data

                const deliveredOrders = window.allOrders.filter(order =>
                    (order.status || '').toLowerCase().includes("delivered")
                );

                console.log("deliveredOrders : ", deliveredOrders);

                salesChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                salesChart.data.datasets[0].data = countSalesThisYear(deliveredOrders);
                salesChart.update();






                // Set "This Year" by default
                const counts = countOrdersThisYear(orders);
                orderChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                orderChart.data.datasets[0].data = counts;
                orderChart.update();



                // Count status occurrences
                let statusCount = {
                    "Pending": 0,
                    "Ongoing": 0,
                    "Delivered": 0,
                    "Canceled": 0,
                    "Returned": 0,
                    "Failed": 0
                };

                orders.forEach(order => {
                    const status = (order.status || '').toLowerCase();
                    if (status.includes("pending")) statusCount.Pending++;
                    else if (status.includes("processing") || status.includes("ongoing")) statusCount.Ongoing++;
                    else if (status.includes("delivered")) statusCount.Delivered++;
                    else if (status.includes("cancel")) statusCount.Canceled++;
                    else if (status.includes("return")) statusCount.Returned++;
                    else if (status.includes("fail")) statusCount.Failed++;
                });

                // Update chart
                donutChart.data.datasets[0].data = [
                    statusCount.Pending,
                    statusCount.Ongoing,
                    statusCount.Delivered,
                    statusCount.Canceled,
                    statusCount.Returned,
                    statusCount.Failed
                ];
                donutChart.update();

                $(".c_pending").html(` <div class="legend-color" style="background-color: #6366F1;"></div> Pending (${statusCount.Pending})`);

                $(".c_delivered").html(`<div class="legend-color" style="background-color: #10B981;"></div> Delivered (${statusCount.Delivered}`);

                $(".c_ongoing").html(`<div class="legend-color" style="background-color: #3B82F6;"></div> Ongoing (${statusCount.Ongoing})`);

                $(".c_canceled").html(`<div class="legend-color" style="background-color: #EF4444;"></div> Canceled (${statusCount.Canceled})`);

                $(".c_returned").html(`<div class="legend-color" style="background-color: #8B5CF6;"></div> Returned (${statusCount.Returned})`);
            }
        }
    });
}

// Chart initialization (run only once)
const donutCtx = document.getElementById('donutChart').getContext('2d');

donutChart = new Chart(donutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Ongoing', 'Delivered', 'Canceled', 'Returned',],
        datasets: [{
            data: [0, 0, 0, 0, 0, 0], // Initial dummy data
            backgroundColor: [
                '#6366F1', '#3B82F6', '#10B981', '#EF4444', '#8B5CF6',
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        cutout: '70%'
    }
});

// Call data load




function countOrdersToday(orders) {
    let todayCount = 0;
    const now = new Date();

    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const date = new Date(dateStr);

        if (
            date.getDate() === now.getDate() &&
            date.getMonth() === now.getMonth() &&
            date.getFullYear() === now.getFullYear()
        ) {
            todayCount++;
        }
    });

    return [todayCount]; // single point
}


function countSalesToday(orders) {
    let todaySales = 0;
    const now = new Date();

    orders.forEach(order => {
        if (!order.dor) return;
        const dateStr = String(order.dor).replace(/-/g, 'https://indiantechsolution.com/');
        const date = new Date(dateStr);

        if (
            date.getDate() === now.getDate() &&
            date.getMonth() === now.getMonth() &&
            date.getFullYear() === now.getFullYear()
        ) {
            todaySales += parseFloat(order.total || 0);
        }
    });

    return [todaySales]; // single point
}
