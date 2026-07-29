$('#coupon-form').submit(function (e) {
    e.preventDefault();
    const coupon_type = $('#coupon-type').val();
    const coupon_title = $('#coupon-title').val();
    const coupon_desc = $('#coupon-desc').val();
    const coupon_code = $('#coupon-code').val();
    const coupon_limit = $('#coupon-limit').val();
    const discount_type = $('#discount-type').val();
    const discount_amount = $('#discount-amount').val();
    const minimum_purchase = $('#minimum-purchase').val();
    const start_date = $('#start-date').val();
    const end_date = $('#end-date').val();


    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'addCoupon', coupon_type: coupon_type, coupon_title: coupon_title, coupon_desc: coupon_desc, coupon_code: coupon_code, coupon_limit: coupon_limit, discount_type: discount_type, discount_amount: discount_amount, minimum_purchase: minimum_purchase, start_date: start_date, end_date: end_date },
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully added');
                loadCoupon();

                // $('#coupon-type').val('');
                $('#coupon-title').val('');
                $('#coupon-desc').val('');
                $('#coupon-code').val('');
                // $('#coupon-limit').val('');
                // $('#discount-type').val('');
                $('#discount-amount').val('');
                $('#minimum-purchase').val('');
                $('#start-date').val('');
                $('#end-date').val('');
            }
        }
    })


});



// Reset form
$('#reset-button').click(function () {
    // $('#coupon-type').val('');
    $('#coupon-title').val('');
    $('#coupon-desc').val('');
    $('#coupon-code').val('');
    // $('#coupon-limit').val('');
    // $('#discount-type').val('');
    $('#discount-amount').val('');
    $('#minimum-purchase').val('');
    $('#start-date').val('');
    $('#end-date').val('');
});


let couponData = [];
const loadCoupon = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadCoupon' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                couponData = data;
                renderCoupon(data);
            }
        }
    })

}

const renderCoupon = (data) => {
    $(".tableCount").html(data.length);
    let html = `
    <thead>
            <tr>
                <th>SL</th>
                <th>Coupon</th>
                <th>Coupon type</th>
                <th>Discount type</th>
                <th>Duration</th>
                <th>User Limit</th>
                <th>Action</th>
            </tr>
        </thead>
    <tbody>
    `;
    data.forEach((item, index) => {

        let items = JSON.stringify(item).replace(/'/g, '`');
        html += `
       <tr>
            <td class="sl">${index + 1}</td>
            <td>Code : ${item.code} <br> ${item.title}</td>
            <td>${item.type}</td>
            <td>Discount in ${item.discount_type}</td>
            <td>${item.start_date} - ${item.end_date}</td>
            <td>${item.limit}</td>
           
            <td>
                <div class="flex">
                    <button class="view flex" onclick='viewCoupon(${items})'><i class="bi bi-eye"></i></button>
                    <button class="edit flex" onclick='editCoupon(${items})'><i class="bi bi-pencil"></i></button>
                    <button class="delete flex" onclick='deleteCoupon(${items})'><i class="bi bi-trash3"></i></i></button>
                 </div>
            </td>
        </tr>
        `;
    });
    html += `</tbody>`;
    $("#result").html(html);
}

const searchCoupon = () => {
    let searchText = $("#search-input").val().toLowerCase();
    filteredData = couponData.filter(item =>
        item.title.toLowerCase().includes(searchText));
    renderCoupon(filteredData);
};


const viewCoupon = (item) => {
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);
    console.log(item);
    $(".coupon-card").addClass('active');
    $(".wrapper-overlay").addClass('active');
    $('body').css("overflow", 'hidden');

    $(".coupon-title").html(item.title)
    $(".couponCode").html(item.code)
    $(".coupon-desc").html(item.description)
    $(".minPurchase").html(`Minimum Purchase : <b>₹ ${item.minimum_purchase}</b>`);
    $(".startDate").html(`Start Date :  ${item.start_date}`);
    $(".endDate").html(`Expire Date :  ${item.end_date}`);
    $(".coupon-amount").html(` <p> ₹ ${item.amount} <br><span>off</span></p>`);
}

const closeCoupon = () => {
    $(".coupon-card").removeClass('active');
    $(".wrapper-overlay").removeClass('active');
    $('body').css("overflow", 'auto');
}


const deleteCoupon = async (item) => {
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteCoupon', id: item.id },
            success: function (response) {
                if (response != 'error' && response != 'null') {
                    successAlert('Successfully Deleted');
                    loadCoupon();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })
    }
}


const editCoupon = (item) => {
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);

    $('html, body').animate({
        scrollTop: $('#coupon-id').offset().top
    }, 500); // 500 = duration in milliseconds

    console.log(item);
    $("#submit-button").hide();
    $("#update-button").show();

     $('#coupon-id').val(item.id);
     $('#coupon-type').val(item.type);
     $('#coupon-title').val(item.title);
     $('#coupon-desc').val(item.description);
     $('#coupon-code').val(item.code);
     $('#coupon-limit').val(item.limit);
     $('#discount-type').val(item.discount_type);
     $('#discount-amount').val(item.amount);
     $('#minimum-purchase').val(item.minimum_purchase);
     $('#start-date').val(item.start_date);
     $('#end-date').val(item.end_date);
}

$("#update-button").click((e)=>{

    e.preventDefault();
    const coupon_id = $('#coupon-id').val();
    const coupon_type = $('#coupon-type').val();
    const coupon_title = $('#coupon-title').val();
    const coupon_desc = $('#coupon-desc').val();
    const coupon_code = $('#coupon-code').val();
    const coupon_limit = $('#coupon-limit').val();
    const discount_type = $('#discount-type').val();
    const discount_amount = $('#discount-amount').val();
    const minimum_purchase = $('#minimum-purchase').val();
    const start_date = $('#start-date').val();
    const end_date = $('#end-date').val();

    const values = [
        coupon_type,
        coupon_title,
        coupon_desc,
        coupon_code,
        coupon_limit,
        discount_type,
        discount_amount,
        minimum_purchase,
        start_date,
        end_date
      ];
      
      const allFilled = values.every(val => val !== null && val !== '');


      if (!allFilled) {
        errorAlert('Please fill in all the required fields');
        return;
      }

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'updateCoupon', coupon_type: coupon_type, coupon_title: coupon_title, coupon_desc: coupon_desc, coupon_code: coupon_code, coupon_limit: coupon_limit, discount_type: discount_type, discount_amount: discount_amount, minimum_purchase: minimum_purchase, start_date: start_date, end_date: end_date ,id:coupon_id},
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully updated');
                loadCoupon();

                $("#submit-button").show();
                $("#update-button").hide();
            

                // $('#coupon-type').val('');
                $('#coupon-title').val('');
                $('#coupon-desc').val('');
                $('#coupon-code').val('');
                // $('#coupon-limit').val('');
                // $('#discount-type').val('');
                $('#discount-amount').val('');
                $('#minimum-purchase').val('');
                $('#start-date').val('');
                $('#end-date').val('');
            }
        }
    })

})