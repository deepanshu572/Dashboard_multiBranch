
let currentPageSize = 10;


let userData = []; // Store all users data globally
let filteredData = []; // Store filtered data
let sortOrder = 'asc'; // 'asc' or 'desc'

const sortUsersByTotalOrders = () => {
    sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';

    filteredData.sort((a, b) => {
        let orderA = parseInt(a.total_orders) || 0;
        let orderB = parseInt(b.total_orders) || 0;

        return sortOrder === 'asc' ? orderA - orderB : orderB - orderA;
    });

    renderUserTable(1, currentPageSize);
};

const loadAllUser = async () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadAllUser' },
        success: function (response) {
            if (response !== 'error' && response !== null) {
                userData = JSON.parse(response);

                $(".user_count").html(userData.length); // Update user count
                // Store data in a global variable
                filteredData = userData; // Initially, filtered data is the same as all data
                renderUserTable(1, currentPageSize); // Render first page by default
            }
        }
    });
};

const renderUserTable = (page = 1, pageSize = 10) => {
    const totalItems = filteredData.length;
    const totalPages = Math.ceil(totalItems / pageSize);
    const paginatedData = filteredData.slice((page - 1) * pageSize, page * pageSize);

    let html = `
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Mobile Number</th>
                <th>Email</th>
                <th>Dor</th>
                <th>Refrel Code</th>
                <th onclick="sortUsersByTotalOrders()" style="cursor: pointer;">Total Orders <i class="bi bi-arrow-down-up"></i></th>
                <th>Wallet Balance</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
    `;

    paginatedData.forEach((item, index) => {

        const dateString = item.date;


        const date = new Date(dateString.replace(" ", "T"));

        // Format options
        const options = {
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        };

        const formattedDate = date.toLocaleString("en-US", options);

        html += `
            <tr>
                <td class="sl">${item.user_id}</td>
                <td>${item.full_name}</td>
                <td>${item.mobile}</td>
                <td>${item.email}</td>
                <td>${formattedDate}</td>
                <td>${item.refrel_code}</td>
                <td>${item.total_orders || 0}</td>
                <td>${item.wallet_balance}</td>
                <td>
                <label class="switch">
                    <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
                    onclick="handleCheckboxChange(this,'status','${item.user_id}')">
                    <span class="slider"></span>
                </label>
                </td>
                <td class="flex gap-10">
                    <button onclick="window.location.href='order.html?mobile=${item.mobile}'" class="view_order" title="View Orders"><i class="bi bi-eye"></i></button>
                    <button onclick="openModal('${item.user_id}' , '${item.email}')" class="edit_gmail"><i class="bi bi-pencil-square"></i></button>
                </td>
            </tr>
        `;
    });

    html += `</tbody>`;
    $("#result").html(html);

    // Generate pagination buttons
    let paginationHtml = `<div class="pagination">`;

    if (page > 1) {
        paginationHtml += `<button class="page-btn" onclick="renderUserTable(${page - 1}, ${pageSize})"><i class="bi bi-chevron-double-left"></i></button>`;
    }

    let startPage = Math.max(1, page - 1);
    let endPage = Math.min(totalPages, page + 1);

    if (startPage > 1) {
        paginationHtml += `<button class="page-btn" onclick="renderUserTable(1, ${pageSize})">1</button>`;
        if (startPage > 2) {
            paginationHtml += `<span class="ellipsis">..</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="renderUserTable(${i}, ${pageSize})">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<span class="ellipsis">..</span>`;
        }
        paginationHtml += `<button class="page-btn" onclick="renderUserTable(${totalPages}, ${pageSize})">${totalPages}</button>`;
    }

    if (page < totalPages) {
        paginationHtml += `<button class="page-btn" onclick="renderUserTable(${page + 1}, ${pageSize})"><i class="bi bi-chevron-double-right"></i></button>`;
    }

    paginationHtml += `</div>`;
    $("#pagination").html(paginationHtml);
};

// Search function to filter users based on input
// const searchUser = () => {
//     let searchText = $("#search-input").val().toLowerCase(); // Get the search text and convert it to lowercase
//     filteredData = userData.filter(user =>
//         user.full_name.toLowerCase().includes(searchText) ||
//         user.mobile.includes(searchText)
//     );
//     renderUserTable(1, currentPageSize); // Re-render the table with filtered data
// };

// Call loadAllUser once to fetch data and render the first page
// loadAllUser();

// Attach keyup event to the search input field
$(document).on("keyup", "#search-input", function () {
    searchUser();
});


// Call loadAllUser once to fetch data and render the first page
// loadAllUser();




const modal = document.getElementById('emailModal');
// const userEmail = document.getElementById('userEmail');
const newEmailInput = document.getElementById('newEmail');
const errorMsg = document.getElementById('errorMsg');

function openModal(userId, userEmail) {
    // newEmailInput.value = userEmail.textContent;
    errorMsg.textContent = '';
    modal.style.display = 'flex';
    newEmailInput.value = '';
    $('#newEmail').focus();
    $('#user_id').val(userId);
    $('#oldEmail').val(userEmail);
}

function closeModal() {
    modal.style.display = 'none';
}

function updateEmail() {
    const email = newEmailInput.value.trim();

    if (!validateEmail(email)) {
        // errorMsg.textContent = "Please enter a valid email address.";
        errorAlert("Please enter a valid email address.");
        return;
    }

    closeModal();



    $.ajax({
        url: apiurl,
        type: 'POST',
        data: {
            type: 'updateEmail',
            user_id: $('#user_id').val(),
            email: email
        },
        success: function (response) {

            if (response == 'success') {
                successAlert("Email updated successfully!");
            } else {
                errorAlert(response);
            }

            loadAllUser();
        },
        error: function (error) {
            errorMsg.textContent = "Error updating email.";
        }
    })
}

function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Close modal if clicked outside content
window.onclick = function (event) {
    if (event.target === modal) {
        closeModal();
    }
}




function handleCheckboxChange(checkbox, typeStatus, id) {
    const isChecked = checkbox.checked;
    console.log('type : ', typeStatus, id)
    Swal.fire({
        title: 'Are you sure?',
        text: isChecked
            ? "You want to active this user"
            : "You want to block this user",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        customClass: {
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let statusText = isChecked ? "true" : "false";
            $.ajax({
                url: apiurl,
                type: 'POST',
                data: { 'type': 'updateUserStatus', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
                success: function (response) {
                    if (response != 'error') {
                        Swal.fire({
                            title: 'Success!',
                            text: isChecked
                                ? `User  activated successfully`
                                : `User blocked successfully`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        checkbox.checked = !isChecked;
                    }
                }
            })

        } else {
            checkbox.checked = !isChecked;
        }
    });
}



function applyFilters() {

    let searchText = $("#search-input").val().toLowerCase();
    let dateFilter = $("#dateFilter").val();
    let statusFilter = $("#statusFilter").val();
    let fromDate = $("#fromDate").val();
    let toDate = $("#toDate").val();

    let today = new Date();
    today.setHours(0, 0, 0, 0);

    filteredData = userData.filter(user => {

        /* 🔍 SEARCH FILTER */
        if (
            searchText &&
            !user.full_name.toLowerCase().includes(searchText) &&
            !user.mobile.includes(searchText)
        ) {
            return false;
        }

        /* 🔘 STATUS FILTER */
        if (statusFilter !== "" && user.status !== statusFilter) {
            return false;
        }

        /* 📅 DATE FILTER */
        let userDate = new Date(user.date.replace(" ", "T"));
        userDate.setHours(0, 0, 0, 0);

        if (dateFilter === "today") {
            return userDate.getTime() === today.getTime();
        }

        if (dateFilter === "week") {
            let weekAgo = new Date(today);
            weekAgo.setDate(today.getDate() - 7);
            return userDate >= weekAgo && userDate <= today;
        }

        if (dateFilter === "month") {
            return (
                userDate.getMonth() === today.getMonth() &&
                userDate.getFullYear() === today.getFullYear()
            );
        }

        if (dateFilter === "custom" && fromDate && toDate) {
            let from = new Date(fromDate);
            let to = new Date(toDate);
            return userDate >= from && userDate <= to;
        }

        return true;
    });

    renderUserTable(1, currentPageSize);
}


function changePageSize() {
    currentPageSize = parseInt($("#pageSize").val());
    renderUserTable(1, currentPageSize);
}

const searchUser = () => {
    let searchText = $("#search-input").val().toLowerCase();
    filteredData = userData.filter(user =>
        user.full_name.toLowerCase().includes(searchText) ||
        user.mobile.includes(searchText)
    );

    applyFilters(); // 🔥 search ke baad filters bhi apply honge
};

$(document).ready(function () {

    $("#dateFilter").on("change", function () {

        if ($(this).val() === "custom") {
            $("#fromDate, #toDate").fadeIn(200);
        } else {
            $("#fromDate, #toDate").fadeOut(200);
            $("#fromDate, #toDate").val("");
        }

        applyFilters(); // filter bhi turant apply ho
    });

});

$(document).on("change", "#fromDate, #toDate", function () {
    applyFilters(); // date select hote hi filter chale
});