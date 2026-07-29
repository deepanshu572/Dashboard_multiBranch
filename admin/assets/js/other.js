

const setDelvieryCharge = () => {
    let deliveryCharge = $("#deliveryCharge").val();
    let minAmount = $("#minAmount").val();
    let branchId = $("#branchId").val();

    $.ajax({
        url: apiurl,
        type: "POST",
        data: { type: 'setDelvieryCharge', deliveryCharge: deliveryCharge, branchId, minAmount: minAmount },
        success: function (response) {
            if (response == 'success') {
                successAlert("Delivery Charge Set Successfully");
                loadDeliveryCharge();
                $("#deliveryCharge").val('');
                $("#minAmount").val('');
                $("#branch").val(0);

            }
        }
    })
}

const loadDeliveryCharge = () => {

    $.ajax({
        url: apiurl,
        type: "POST",
        data: { type: 'loadDeliveryCharge' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                data = data.sort((a, b) => a.amount - b.amount);
                let html = `
                <thead>
                        <tr>
                            <th>SL</th>
                            <th>Branch</th>
                            <th>Delivery Charge</th>
                            <th>Minimum Amount</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${index + 1}</td>
                        <td>${item.branch_name}</td>
                       
                        <td>${item.amount}</td>
                        <td>${item.min_amount}</td>
                        <td style="color:#4F46E5; font-size:12px;"><b>@${item.added_by || 'admin'}</b></td>
                        <td>
                            <button class="delete flex" onclick='deleteDelCharge(${items})'><i class="bi bi-trash3"></i></button>
                        </td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            }
        }
    })
}

const deleteDelCharge = async (item) => {
    console.log(item);
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteDelCharge', id: item.id },
            success: function (response) {
                if (response != 'error' && response != 'null') {
                    successAlert('Successfully Deleted');
                    loadDeliveryCharge();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })
    }
}

const delChargeForm = () => {

    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Delivery Charge for your store</h4>
                            </div>
                            <div class="input-field">
                                <label for="deliveryCharge">Enter Delivery Charge</label>
                                <input type="number" id="deliveryCharge" class="input-box"
                                    placeholder="Enter Delivery Charge">
                            </div>
                            <div class="input-field">
                                <label for="minAmount">Enter Minimume Amount</label>
                                <input type="number" id="minAmount" class="input-box"
                                    placeholder="Enter Minimume Amount">
                            </div>
                            <button class="set-btn" id="setCharge" onclick="setDelvieryCharge()">Set Charge</button>
        </div>
        `);
    $(".otherResultHeader").html(`Delivery Fees`);
    loadDeliveryCharge();
}

const handlingChargeForm = () => {

    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Handling Charge for your store</h4>
                            </div>
                            <div class="input-field">
                                <label for="deliveryCharge">Enter Handling Charge</label>
                                <input type="number" id="handlingCharge" class="input-box"
                                    placeholder="Enter Handling Charge">
                            </div>
                           
                            <button class="set-btn" id="setCharge" onclick="setHandlingCharge()">Set Charge</button>
        </div>
        `);
    $(".otherResultHeader").html(`Handling Charges`);
    loadHandlingCharge();
}




const setHandlingCharge = () => {
    let handlingCharge = $("#handlingCharge").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'setHandlingCharge', handlingCharge: handlingCharge },
        success: function (response) {

            if (response == 'success') {
                successAlert('successfully updated');
                $("#handlingCharge").val('');
                loadHandlingCharge();
            }

        }
    })
}



const loadHandlingCharge = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadHandlingCharge' },
        success: function (response) {

            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                data = data.sort((a, b) => a.amount - b.amount);
                let html = `
                <thead>
                        <tr>
                            <th>SL</th>
                            <th>Handling Charge</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${index + 1}</td>
                        <td>${item.min_amount}</td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            }

        }
    })
}





// 


const addGiftProductForm = () => {

    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Gift Product for your store</h4>
                            </div>
                            <div class="input-field">
                                <label for="productName">Enter Product Name</label>
                                <input type="text" id="productName" class="input-box"
                                    placeholder="Enter Product Name">
                            </div>
                            <div class="input-field">
                            <div class="giftimg">
                                 <div>
                            <label for="productName">Gift Product Photo</label>
                                <input type="file" id="productPhoto" class="input-box"
                                    placeholder="Product Photo">
                                     </div>
                                  <!--  <div class="image-preview flex gap-30">
                                        <img id="preview-img" src="https://placehold.co/500x500" alt="Preview">
                                    </div> -->
                                </div>
                            </div>
                              <div class="input-field">
                                <label for="giftProductQty">Gift Product Quantity</label>
                                <input type="number" id="giftProductQty" class="input-box"
                                    placeholder="Gift Product Quantity">
                            </div>
                            <div class="input-field">
                                <label for="unit">Select Unit</label>
                                 <select id="unit" name="unit" required>
                                    <option value="">Select Unit</option>
                                  <!-- Grocery & General Food Items -->
                                    <option value="gm">gm</option>
                                    <option value="kg">kg</option>
                                    <option value="ml">ml</option>
                                    <option value="liter">liter</option>
                                    <option value="packet">Packet</option>
                                    <option value="bottle">Bottle</option>
                                    <option value="can">Can</option>
                                    <option value="jar">Jar</option>
                                    <option value="tin">Tin</option>
                                    <option value="sachet">Sachet</option>
                                    <option value="bar">Bar</option>
                                    <option value="box">Box</option>
                                    <option value="tray">Tray</option>

                                    <!-- Fruits & Vegetables -->
                                    <option value="pieces">Pieces</option>
                                    <option value="bunch">Bunch</option>
                                    <option value="dozen">Dozen</option>
                                    <option value="bundle">Bundle</option>
                                    <option value="pair">Pair</option>

                                    <!-- Clothes -->
                                    <option value="piece">Piece</option>
                                    <option value="set">Set</option>
                                    <option value="pair">Pair</option>
                                    <option value="pack">Pack</option>
                                    <option value="s">S</option>
                                    <option value="m">M</option>
                                    <option value="l">L</option>
                                    <option value="xl">XL</option>
                                    <option value="xxl">XXL</option>

                                    <!-- Electronics -->
                                    <option value="unit">Unit</option>
                                    <option value="half plate">Half Plate</option>
                                    <option value="full plate">Full Plate</option>
                                    <option value="glass">glass</option>
                                    <option value="cup">cup</option>
                                    <option value="plate">plate</option>
                                </select>
                            </div>

                            <button class="set-btn" id="setCharge" onclick="addGiftProduct()">Add Gift Product</button>
        </div>
        `);
    $(".otherResultHeader").html(`Gift Product`);
    loadGiftProduct();
}


const loadGiftProduct = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadGiftProduct' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                let html = `
                <thead>
                        <tr>
                            <th>SL</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${index + 1}</td>
                       
                        <td>${item.name}</td>
                        <td><img src="${imgurl + item.image_path}" /></td>
                        <td>${item.quantity}${item.unit}</td>
                        <td>
                            <button class="delete flex" onclick='deleteGiftProduct(${items})'><i class="bi bi-trash3"></i></button>
                        </td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            }
        }
    })
}




// $(document).ready(function () {
//     $('#productPhoto').change(function () {
//         alert('hello world');
//         const file = this.files[0];
//         if (file) {
//             if (file.size > 100 * 1024) { // Validate file size (100KB max)
//                 alert('Image size should not exceed 100 KB');
//                 $('#productPhoto').val('');
//                 $('#preview-img').attr('src', 'https://placehold.co/500x500');
//                 return;
//             }

//             const reader = new FileReader();
//             reader.onload = function (e) {
//                 $('#preview-img').attr('src', e.target.result);
//                 singleImageName = e.target.result;
//                 imageExtension = file.name.split('.').pop().toLowerCase();
//             };
//             reader.readAsDataURL(file);
//         }
//     });
// });



const addGiftProduct = () => {

    let productName = $("#productName").val();
    let giftProductQty = $("#giftProductQty").val();
    let unit = $("#unit").val();

    const file = $('#productPhoto')[0].files[0];


    if (productName == '') {
        warningAlert('Enter valid name');
        return;
    }
    if (giftProductQty == '') {
        warningAlert('Enter valid quantity');
        return;
    }
    if (unit == '') {
        warningAlert('Select Valid Unit');
        return;
    }

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const base64Image = e.target.result;
            const fileExtension = file.name.split('.').pop().toLowerCase();
            $.ajax({
                url: apiurl,
                type: 'POST',
                data: { type: 'addGiftProduct', productName: productName, unit: unit, giftProductQty: giftProductQty, categoryImage: base64Image, imageExtension: fileExtension },
                success: function (response) {
                    if (response == 'success') {
                        successAlert('Successfully updated');
                        loadGiftProduct();
                    } else {
                        errorAlert('something went wrong');
                    }
                },
                error: function () {
                    alert('Error submitting data.');
                },
            });
        };
        reader.readAsDataURL(file);
    } else {
        errorAlert('please select img file');
    }
}


const deleteGiftProduct = async (item) => {
    console.log(item);
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteGiftProduct', id: item.id },
            success: function (response) {
                if (response == 'success') {
                    successAlert('successfully deleted');
                    loadGiftProduct();
                }
            }
        })
    }
}



const minOrderValueForm = () => {

    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Minimume Order Value for your store</h4>
                            </div>
                            <div class="input-field">
                                <label for="minOrderValue">Enter Handling Charge</label>
                                <input type="number" id="minOrderValue" class="input-box"
                                    placeholder="Enter Minimume Order Value">
                            </div>
                           
                            <button class="set-btn" id="setCharge" onclick="setMinOrderValue()">Set Charge</button>
        </div>
        `);
    $(".otherResultHeader").html(`Minimume Order Value`);
    loadMinOrderValue();
}




const loadMinOrderValue = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadMinOrderValue' },
        success: function (response) {

            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                data = data.sort((a, b) => a.amount - b.amount);
                let html = `
                <thead>
                        <tr>
                            <th>SL</th>
                            <th>Minimume Order Value</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${index + 1}</td>
                        <td>${item.min_amount}</td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            }

        }
    })
}


const setMinOrderValue = () => {
    let minOrderValue = $("#minOrderValue").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'setMinOrderValue', minOrderValue: minOrderValue },
        success: function (response) {

            if (response == 'success') {
                successAlert('successfully updated');
                $("#minOrderValue").val('');
                loadMinOrderValue();
            }

        }
    })
}


















//  new line added


const setBillingDetailsForm = () => {
    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Billing Deatils</h4>
                            </div>
                            <div class="input-field">
                                <label for="billing_addresss">Enter Billing Address</label>
                                <input type="text" id="billing_addresss" class="input-box"
                                    placeholder="Enter billing address">

                                <label style="margin-top:10px;" for="billing_number">Enter Phone Number</label>
                                <input type="number" id="billing_number" class="input-box"
                                    placeholder="Enter Phone Number">

                               <!-- <label style="margin-top:10px;" for="gst_number">Enter Phone Number</label>
                                <input type="number" id="gst_number" class="input-box"
                                    placeholder="Enter Gst Number"> -->
                            </div>
                           
                            <button class="set-btn" id="setCharge" onclick="setBillingAddress()">Set Address</button>
        </div>
        `);
    $(".otherResultHeader").html(`Billing Deatils`);
    loadBillingDetails();
}

const loadBillingDetails = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadBillingDetails' },
        success: function (response) {

            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                let html = `
                <thead>
                        <tr>
                            <th>Address</th>
                            <th>Phone Number</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${item.address}</td>
                        <td>${item.phone_number}</td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            } else {
                $("#otherResult").html("no data found");
            }

        }
    })
}


const setBillingAddress = () => {


    let billing_addresss = $("#billing_addresss").val();
    let billing_number = $("#billing_number").val();
    // let gst_number = $("#gst_number").val();
    let gst_number = null;

    if (billing_addresss == null || billing_addresss == '') {
        errorAlert('Plese enter the valid address');
        return;
    }
    if (billing_number == null || billing_number == '') {
        errorAlert('Plese enter the valid number');
        return;
    }
    // if (gst_number == null || gst_number == '') {
    //     errorAlert('Plese enter the valid gst number');
    //     return;
    // }

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'setBillingAddress', billing_addresss: billing_addresss, billing_number: billing_number, gst_number: gst_number },
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully updated');
                loadBillingDetails();
            } else {
                errorAlert('something went wrong');
            }
        }
    })

}



//  set brands of the day 

const setBrandsOfTheForm = async () => {

    let brandList = '';
    await $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadBrands' },
        success: function (response) {
            if (response != null && response != 'error') {
                let data = JSON.parse(response);

                let brandList = ' <option value="" selected disabled hidden>Select Brand</option>';
                data.forEach((item, index) => {
                    brandList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                })

                $(".left-section").html(`
                 <div class="select_field">
                            <div>
                                <h4>Brand Name</h4>
                            </div>
                           
                            <select id="brand_name" name="brand-name">
                               ${brandList}
                            </select>
                            <button class="set-btn" id="setCharge" onclick="setBrandsOfTheWay()">Set Brands of the way</button>
                  </div>
        `);
                $(".otherResultHeader").html(`Brands Of The Way`);

            } else {
                brandList = `<option value="" selected disabled hidden>no data found</option>`;
            }
        }
    })


    loadBrandsOfTheDay();

}


const setBrandsOfTheWay = () => {

    let brand_id = $("#brand_name").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'setBrandsOfTheWay', id: brand_id },
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully updated');
                loadBrandsOfTheDay();
            } else {
                errorAlert('something went wrong');
            }
        }
    })
}

const loadBrandsOfTheDay = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'getBrandsOfTheDay' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                let html = `
                <thead>
                        <tr>
                            <th>id</th>
                            <th>Name</th>
                            <th>Logo</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${item.id}</td>
                        <td>${item.name}</td>
                        <td> <img src="${imgurl + item.logo_path}" width="50" /></td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            } else {
                $("#otherResult").html("no data found");
            }
        }
    })
}



// password open modal

function openModal() {
    document.getElementById('passwordModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

function savePassword() {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;

    if (!current || !newPass || !confirm) {
        errorAlert('Please fill out all fields.');
        return;
    }

    if (newPass !== confirm) {
        errorAlert('New passwords do not match.');
        return;
    }

    // Add real password change logic here (e.g., send to backend)
    //   alert('Password changed successfully!');

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'changePassword', current: current, newPass: newPass },
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully updated');
            } else {
                errorAlert(response);
            }
        }
    })

    closeModal();
}




const handleTimeSlot = () => {
    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Time Slot</h4>
                            </div>
                            <div class="time_slot_field">
                            <div class="input-field">
                                <label for="start-time">Start Time:</label>
                                <input type="time" id="start-time" name="start-time">
                            </div>

                            <div class="input-field">
                                <label for="end-time">End Time:</label>
                                <input type="time" id="end-time" name="end-time">
                            </div>
                            </div>
                          
                            <button class="set-btn" id="setCharge" onclick="setTimeSlot()">Set Time Slot</button>
        </div>
        `);
    $(".otherResultHeader").html(`Time Slot`);
    loadTimeSlot();
}


function formatTime(timeStr) {
    const [hour, minute] = timeStr.split(':');
    const h = parseInt(hour);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hr12 = h % 12 === 0 ? 12 : h % 12;
    return `${hr12}:${minute} ${ampm}`;
}

function setTimeSlot() {
    const start = document.getElementById('start-time').value;
    const end = document.getElementById('end-time').value;

    if (start && end) {
        const slot = `${formatTime(start)} - ${formatTime(end)}`;
        //   document.getElementById('time-slot').innerText = slot;
        console.log(`Time Slot: ${slot}`);
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'setTimeSlot', slot: slot },
            success: function (response) {
                if (response == 'success') {
                    successAlert('Time Slot Set Successfully');
                    loadTimeSlot();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        });
    } else {
        alert("Please select both start and end time.");
    }
}


const loadTimeSlot = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadTimeSlot' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                let html = `
                <thead>
                        <tr>
                            <th>id</th>
                            <th>Time Slot</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${item.id}</td>
                        <td>${item.slot}</td>
                        <td><button class="delete_btn" onclick="deleteTimeSlot(${item.id})">Delete</button></td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            } else {
                $("#otherResult").html("no data found");
            }
        }
    })
}



const deleteTimeSlot = async (id) => {
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteTimeSlot', id: id },
            success: function (response) {
                if (response == 'success') {
                    successAlert('Time Slot Deleted Successfully');
                    loadTimeSlot();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        });
    }
}


const handleTitle = () => {

    $(".left-section").html(`
        <div class="input-field">
            <label for="headline">Select Headline</label>
            <select id="headline" name="title">
                <option value="best_selling">Best Selling</option>
                <option value="top_product">Top Product</option>
                <option value="top_subcategory">Top Subcategory</option>
                <option value="new_finds">New Finders</option>
            </select>
        </div>
        <div class="input-field">
            <label for="title">Title:</label>
           <select id="title" name="title">
               <option value="title1">Title 1</option>
               <option value="title2">Title 2</option>
               <option value="title3">Title 3</option>
               <option value="title4">Title 4</option>
               <option value="title5">Title 5</option>
               <option value="title6">Title 6</option>
           </select>
        </div>
        <div class="input-field">
            <label for="main-title">Enter Title:</label>
            <input type="text" class"input-box" id="header_title" name="main-title" placeholder="Enter  Title">
        </div>
        <div>
            <button class="set-btn" onclick="saveTitle()">Save Title</button>
        </div>
    `);
    $(".otherResultHeader").html(`Header Title`);
    loadTitle();
}

const loadTitle = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadTitle' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                let html = `
      <table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; margin-top:10px;">
        <thead style="background:#005555; color:white;">
          <tr>
            <th>Title Type</th>
            <th>Title1</th>
            <th>Title2</th>
            <th>Title3</th>
            <th>Title4</th>
            <th>Title5</th>
            <th>Title6</th>
          </tr>
        </thead>
        <tbody>
    `;

                data.forEach(item => {
                    html += `
        <tr>
          <td>${item.title_type}</td>
          <td>${item.title1 || ""}</td>
          <td>${item.title2 || ""}</td>
          <td>${item.title3 || ""}</td>
          <td>${item.title4 || ""}</td>
          <td>${item.title5 || ""}</td>
          <td>${item.title6 || ""}</td>
        </tr>
      `;
                });

                html += `</tbody></table>`;
                document.getElementById("otherResult").innerHTML = html;

            } else {
                $("#otherResult").html("no data found");
            }
        }
    })
}


const saveTitle = () => {
    let headline = $("#headline").val();
    let title = $("#title").val();
    let header_title = $("#header_title").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'saveTitle', headline: headline, title: title, header_title: header_title },
        success: function (response) {
            if (response == 'success') {
                successAlert('Title Saved Successfully');
                loadTitle();
            } else {
                errorAlert('Something went wrong');
            }
        }
    });
}


// hero banner option

const selectCategoryChanged = () => {
    // $("#hero_sub_category").val('');
    // console.log("hrlooo")
    getmiddleCategory()
}

const selectSubCategoryChanged = () => {
    // $("#hero_category").val('');
    getSubCategory();
}

function getSubCategory() {
    let categoryId = $("#hero_middle_category").val();
    $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getSubCategory",
            categoryId
        },
        success: function (response) {
            let html = '';
            if (response.status == "success") {
                $("#subCat").css("display", "block");
                let data = response.data;
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#hero_sub_category").html(html);
            } else {
                console.log(response.message);
                $("#subCat").css("display", "none");

                $("#hero_sub_category").html(html);

            }
        }
    })
}

// Load Hero Banner Form
const handleHeroBanner = async () => {
    $(".left-section").html(`
        <div class="input-field">
            <label for="hero_category">Select Category</label>
            <select id="hero_category" name="hero_category" onchange="selectCategoryChanged()" >
                <option value="">Loading...</option>
            </select>
        </div>
        <div class="input-field" id="middleCat" style="display:none" >
            <label for="hero_middle_category">Select Middle Category</label>
            <select id="hero_middle_category" name="hero_middle_category" onchange="selectSubCategoryChanged()">
                
            </select>
        </div>

          <div class="input-field" id="subCat"  style="display:none">
            <label for="hero_sub_category">Select Subcategory</label>
            <select id="hero_sub_category" name="hero_sub_category" >
                
            </select>
        </div>
        

        <div class="input-field">
            <label for="bannerImage">Choose Image</label>
            <input type="file" id="bannerImage" name="bannerImage" accept="image/*">
            <div id="imagePreview" style="margin-top:10px;"></div>
        </div>

        <div>
            <button class="set-btn" onclick="saveHeroBanner()">Add Hero Banner</button>
        </div>
    `);

    $(".otherResultHeader").html(`Hero Banner Settings`);

    // 1. Load Categories Dynamically
    await $.ajax({
        url: apiurl,
        method: "POST",
        data: { type: "loadCategory" },
        success: function (res) {
            try {
                let data = JSON.parse(res);
                if (Array.isArray(data) && data.length > 0) {
                    let options = `<option value="">Select Category</option>`;
                    data.forEach(cat => {
                        options += `<option value="${cat.id}">${cat.name || cat.category_name}</option>`;
                    });
                    $("#hero_category").html(options);
                } else {
                    $("#hero_category").html(`<option value="">No Categories Found</option>`);
                }
            } catch (e) {
                console.error("Category parse error:", e);
                $("#hero_category").html(`<option value="">Error Loading Categories</option>`);
            }
        },
        error: function (err) {
            console.error("API Error:", err);
            $("#hero_category").html(`<option value="">Error Loading Categories</option>`);
        }
    });


    // 2. Image Preview + Validation
    $("#bannerImage").on("change", function (event) {
        const file = event.target.files[0];
        const previewDiv = $("#imagePreview");

        if (file) {
            // Size check
            if (file.size > 200 * 1024) {
                Swal.fire({
                    icon: "error",
                    title: "File too large!",
                    text: "Image must be less than 200 KB."
                });
                $(this).val(""); // reset input
                previewDiv.html("");
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const fullBase64 = e.target.result; // data:image/png;base64,...
                const onlyBase64 = fullBase64.split(",")[1]; // pure base64
                const ext = file.name.split(".").pop().toLowerCase();

                // Preview
                previewDiv.html(
                    `<img src="${fullBase64}" alt="Preview" style="max-width:200px; border:1px solid #ccc; padding:5px; border-radius:8px;">`
                );

                // Save in input data
                $("#bannerImage").data("base64", onlyBase64);
                $("#bannerImage").data("fullbase64", fullBase64);
                $("#bannerImage").data("ext", ext);
            };
            reader.readAsDataURL(file);
        } else {
            previewDiv.html("");
        }
    });

    loadHeroBanner();
};


function getmiddleCategory() {
    let categoryId = $("#hero_category").val();
    $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getmiddleCategory",
            categoryId
        },
        success: function (response) {
            let html = '';
            if (response.status == "success") {
                $("#middleCat").css("display", "block");
                let data = response.data;
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#hero_middle_category").html(html);
            } else {
                console.log(response.message);
                $("#middleCat").css("display", "none");

                $("#hero_middle_category").html(html);

            }
        }
    });

}

// 3. Save Hero Banner
const saveHeroBanner = () => {
    const categoryId = $("#hero_category").val();
    const middleCategoryId = $("#hero_middle_category").val() || "";
    const subCategoryId = $("#hero_sub_category").val() || "";
    const base64Img = $("#bannerImage").data("base64");
    const fullBase64 = $("#bannerImage").data("fullbase64");
    const imgExt = $("#bannerImage").data("ext");

    if (!categoryId && !subCategoryId) {
        Swal.fire({ icon: "warning", title: "Please select a category or sub-category" });
        return;
    }

    if (!base64Img) {
        Swal.fire({ icon: "warning", title: "Please select an image" });
        return;
    }

    $.ajax({
        url: apiurl,
        method: "POST",
        data: {
            type: "saveHeroBanner",
            category_id: categoryId,
            image_base64: fullBase64,   // only encoded part
            image_ext: imgExt,
            subCategoryId: subCategoryId,
            middleCatId:middleCategoryId
            // full base64 string (optional)
        },
        success: function (res) {
            if (res.trim() === 'success') {
                Swal.fire({ icon: "success", title: "Hero Banner Added!" });
                $("#hero_category").val("");
                $("#hero_middle_category").val("");
                $("#middleCat").css("display","none");
                $("#subCat").css("display","none");
                $("#hero_sub_category").val("");
                $("#bannerImage").val("");
                $("#imagePreview").html("");
                loadHeroBanner();
            } else {
                Swal.fire({ icon: "error", title: "Failed!", text: res || "Something went wrong" });
            }
        },
        error: function (err) {
            console.error("Save API Error:", err);
            Swal.fire({ icon: "error", title: "API Error", text: "Could not save banner" });
        }
    });
};



const loadHeroBanner = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadHeroBanner' },
        success: function (response) {
            if (response && response !== 'error') {
                let data = JSON.parse(response);
                let html = `
      <table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; margin-top:10px;">
        <thead style="background:#005555; color:white;">
          <tr>
            <th>Category</th>
            <th>Image</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
    `;

                data.forEach(item => {
                    html += `
        <tr>
          <td>${item.link_name}</td>
          <td>
            <img src="${imgurl + item.image_path}" alt="Banner" style="width:160px; height:auto; border:1px solid #ccc; border-radius:6px;">
          </td>
          <td>
            <button onclick="deleteHeroBanner('${item.id}')" style="background:#e74c3c; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">
              Delete
            </button>
          </td>
        </tr>
      `;
                });

                html += `</tbody></table>`;
                document.getElementById("otherResult").innerHTML = html;

            } else {
                $("#otherResult").html("No banners found");
            }
        },
        error: function (err) {
            console.error("API Error:", err);
            $("#otherResult").html("Error loading hero banners");
        }
    });
};


const deleteHeroBanner = (id) => {
    Swal.fire({
        title: "Are you sure?",
        text: "This will delete the hero banner permanently!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: apiurl,
                type: "POST",
                data: { type: "deleteHeroBanner", id: id },
                success: function (res) {

                    if (res.trim() === 'success') {
                        Swal.fire("Deleted!", "Hero banner has been deleted.", "success");
                        loadHeroBanner(); // Refresh list
                    } else {
                        Swal.fire("Error!", res || "Could not delete", "error");
                    }
                },
                error: function () {
                    Swal.fire("Error!", "API request failed.", "error");
                }
            });
        }
    });
};



const handleMainTitle = () => {
    $(".left-section").html(`
        <div class="input-field">
            <label for="header">Select Header</label>
            <select id="header" name="header">
                <option value="timer">Timer</option>
                <option value="best_selling">Best Selling</option>
                <option value="suggestion">Suggestion</option>
                <option value="brand">Brand</option>
            </select>
        </div>
        <div class="input-field">
            <label for="header_title">Enter Header</label>
            <input type="text" class="input-box" id="main_header" name="header_title" placeholder="Enter Title">
        </div>
        <div>
            <button class="set-btn" onclick="saveMainHeader()">Save Header</button>
        </div>
    `);

    $(".otherResultHeader").html(`Header Title`);
    loadMainTitle();
}


const loadMainTitle = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadMainTitle' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                let html = `
      <table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; margin-top:10px;">
        <thead style="background:#005555; color:white;">
          <tr>
            <th>Header Type</th>
            <th>Header</th>
          </tr>
        </thead>
        <tbody>
    `;

                data.forEach(item => {
                    html += `
        <tr>
          <td>${item.header_type}</td>
          <td>${item.title}</td>
          
        </tr>
      `;
                });

                html += `</tbody></table>`;
                document.getElementById("otherResult").innerHTML = html;

            } else {
                $("#otherResult").html("no data found");
            }
        }
    })
}


const saveMainHeader = () => {
    let header = $("#header").val();
    let main_header = $("#main_header").val();
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'saveMainHeader', header: header, main_header: main_header },
        success: function (response) {
            if (response.trim() === 'success') {
                Swal.fire("Saved!", "Header has been saved.", "success");
                loadMainTitle(); // Refresh list
            } else {
                Swal.fire("Error!", response || "Could not save", "error");
            }
        },
        error: function () {
            Swal.fire("Error!", "API request failed.", "error");
        }
    });
}


const hideSection = () => {
    $(".options-dropdown").toggle();
}




// set main banner 


const handleMainBanner = () => {
    $(".left-section").html(`
        <div class="input-field">
           <label for="hero_bg">Add Hero Category</label>
           <input type="text" id="categoryIdBanner" hidden />
           <select name="heroCategory" id="heroCategory" onchange="handleCategory(event)">
          
           </select>
        </div>
        <div class="input-field">
           <label for="hero_bg">Add Hero Banner</label>
            <input type="file" class="input-box" id="hero_bg" >
        </div>  
         <div class="input-field">
           <label for="hero_bg">Add Hero Banner</label>
            <input type="color" class="input-box hero_box" id="hero_color" >
        </div>
        <div class="input-field">
            <label for="hero_bg_child">Add Hero Child banner</label>
            <input type="file" class="input-box" id="hero_bg_child" >
        </div>
        <div>
            <button class="set-btn" onclick="saveMainBanner()">Save Main Bannner</button>
        </div>
    `);

    $(".otherResultHeader").html(`Main Banner`);
    loadMainBanner();
    getMainCategory();
}

function handleCategory(e) {
    $("#categoryIdBanner").val(e.target.value);
}

function getMainCategory() {
    $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getMainCategory",
        },
        success: function (response) {
            if (response.status == "success") {
                console.log(response.data);
                let data = response.data;
                let html = ' <option value="0">Select Category</option>';
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#heroCategory").html(html);
            } else {
                console.log(response.message);
            }
        }
    })
}


const loadMainBanner = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadMainBanner' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                let html = `
      <table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; margin-top:10px;">
        <thead style="background:#005555; color:white;">
          <tr>
            <th>Type</th>
            <th>Image</th>
            <th>child</th>
          </tr>
        </thead>
        <tbody>
    `;

                data.forEach(item => {
                    html += `
        <tr>
          <td>${item.category_name}</td>
          <td><img class="main_banner" src="${imgurl + item.img_path}" /></td>
          <td><img class="main_banner" src="${imgurl + item.child_img}" /></td>
          
        </tr>
      `;
                });

                html += `</tbody></table>`;
                document.getElementById("otherResult").innerHTML = html;

            } else {
                $("#otherResult").html("no data found");
            }
        }
    })

}


function saveMainBanner() {
    const hero = document.getElementById("hero_bg").files[0];
    const child = document.getElementById("hero_bg_child").files[0];
    const color = $("#hero_color").val();
    const categoryId = $("#categoryIdBanner").val();

    if (!hero && !child && !color) {
        Swal.fire({
            icon: "warning",
            title: "No Image Selected",
            text: "Please select at least one image!"
        });
        return;
    }

    if (!categoryId) {
        Swal.fire({
            icon: "warning",
            title: "Category Required",
            text: "Please select a category."
        });
        return;
    }

    if (hero && hero.size > 1000 * 1024) {
        Swal.fire({
            icon: "error",
            title: "File Too Large",
            text: "Hero Banner must be less than 1000 KB"
        });
        return;
    }

    if (child && child.size > 1000 * 1024) {
        Swal.fire({
            icon: "error",
            title: "File Too Large",
            text: "Hero Child Banner must be less than 1000 KB"
        });
        return;
    }

    const formData = new FormData();
    formData.append("type", "saveMainBanner");
    formData.append("categoryId", categoryId);
    formData.append("color", color);
    

    const convertToBase64 = (file) => {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.readAsDataURL(file);
        });
    };

    const tasks = [];

    if (hero) {
        tasks.push(
            convertToBase64(hero).then(base64 => {
                formData.append("hero_bg_base64", base64);
                formData.append("hero_bg_ext", hero.name.split(".").pop());
            })
        );
    }

    if (child) {
        tasks.push(
            convertToBase64(child).then(base64 => {
                formData.append("hero_bg_child_base64", base64);
                formData.append("hero_bg_child_ext", child.name.split(".").pop());
            })
        );
    }
    console.log(hero, child)

    Promise.all(tasks).then(() => {

        $.ajax({
            url: apiurl,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                if (response.trim() === "success") {

                    Swal.fire({
                        icon: "success",
                        title: "Saved Successfully!",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $("#hero_bg").val("");
                    $("#hero_bg_child").val("");

                    loadMainBanner();

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response
                    });

                }

            },

            error: function () {

                Swal.fire({
                    icon: "error",
                    title: "Upload Failed",
                    text: "Something went wrong."
                });

            }

        });

    });

}


const handleArea = () => {
    $(".left-section").html(`
     
        <div class="input-field">
            <label for="pin_code">Enter Pincode:</label>
            <input type="text" class"input-box" id="pin_code" placeholder="Enter  Pincode">
        </div>
        <div>
            <button class="set-btn" onclick="saveArea()">Save Area</button>
        </div>
    `);
    $(".otherResultHeader").html(`All Area`);
    loadAllArea();
}

const loadAllArea = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadArea' },
        success: function (response) {
            console.log(response);

            if (
                response &&
                response !== 'error' &&
                response !== '[]' &&
                response !== 'null'
            ) {
                let data = JSON.parse(response);

                if (data.length === 0) {
                    $("#otherResult").html("No area found");
                    return;
                }

                let html = `
                <table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse; margin-top:10px;">
                    <thead style="background:#005555; color:white;">
                        <tr>
                            <th>#</th>
                            <th>Pincode</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                data.forEach(item => {
                    html += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.pin_code}</td>
                        <td><button class="delete_area" onclick="deleteArea(${item.id})">Delete</button></td>
                    </tr>
                    `;
                });

                html += `</tbody></table>`;
                document.getElementById("otherResult").innerHTML = html;

            } else {
                $("#otherResult").html("no data found");
            }
        }
    });
}


const saveArea = () => {
    let pin_code = $("#pin_code").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'saveArea', pin_code: pin_code },
        success: function (response) {
            if (response === 'success') {
                Swal.fire({
                    icon: "success",
                    title: "Area Saved Successfully!",
                    timer: 2000,
                    showConfirmButton: false
                });
                loadAllArea();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error Saving Area",
                    text: "Something went wrong. Try again."
                });
            }
        }
    });
}


const deleteArea = async (id) => {
    const result = await showConfirmationDialog('Do you want to delete this area?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteArea', id: id },
            success: function (response) {
                if (response === 'success') {
                    Swal.fire({
                        icon: "success",
                        title: "Area Deleted Successfully!",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadAllArea();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Deleting Area",
                        text: "Something went wrong. Try again."
                    });
                }
            }
        });
    }
}



const setGiftPrice = () => {

    $(".left-section").html(`
         <div class="del-charge-field">
                            <div>
                                <h4>Set Gift Order Value for your store</h4>
                            </div>
                            <div class="input-field">
                                <label for="giftOrderValue">Enter Gift Order Value</label>
                                <input type="number" id="giftOrderValue" class="input-box"
                                    placeholder="Enter Gift Order Value">
                            </div>

                            <button class="set-btn" id="setCharge" onclick="setGiftOrderValue()">Set Price</button>
        </div>
        `);
    $(".otherResultHeader").html(`Gift Order Value`);
    loadGiftOrderValue();
}



const loadGiftOrderValue = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadGiftOrderValue' },
        success: function (response) {

            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                data = data.sort((a, b) => a.amount - b.amount);
                let html = `
                <thead>
                        <tr>
                            <th>SL</th>
                            <th>Gift Order Value</th>
                        </tr>
                    </thead>
                <tbody>
                `;
                data.forEach((item, index) => {

                    let items = JSON.stringify(item);
                    html += `
                   <tr>
                        <td class="sl">${index + 1}</td>
                        <td>${item.min_amount}</td>
                    </tr>
                    `;
                });
                html += `</tbody>`;
                $("#otherResult").html(html);
            }

        }
    })
}


const setGiftOrderValue = () => {
    let giftOrderValue = $("#giftOrderValue").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'setGiftOrderValue', giftOrderValue: giftOrderValue },
        success: function (response) {

            if (response == 'success') {
                successAlert('successfully updated');
                $("#giftOrderValue").val('');
                loadGiftOrderValue();
            }

        }
    })
}




function shopModelOpen() {
    document.getElementById("shopModelModal").style.display = "flex";

    fetch(apiurl, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "type=getShopStatus"
    })
        .then(response => response.json())
        .then(data => {

            // set status radio
            document.querySelector(
                `input[name="shop_status"][value="${data.status}"]`
            ).checked = true;

            // set message
            document.getElementById("shop_model_message").value = data.message || "";

        })
        .catch(error => {
            console.error("Error fetching shop status:", error);
        });
}


function shopModelClose() {
    document.getElementById("shopModelModal").style.display = "none";
}

// function shopModelSave() {
//   const status = document.querySelector(
//     'input[name="shop_status"]:checked'
//   ).value;

//   const message = document.getElementById("shop_model_message").value;

//   alert(
//     "Shop Status: " + status.toUpperCase() +
//     "\nMessage: " + message
//   );

//   // yahin se AJAX / API call laga sakte ho
//   shopModelClose();
// }

function shopModelSave() {
    const status = document.querySelector(
        'input[name="shop_status"]:checked'
    ).value;

    const message = document.getElementById("shop_model_message").value;

    fetch(apiurl, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body:
            "type=setShopStatus" +
            "&status=" + encodeURIComponent(status) +
            "&message=" + encodeURIComponent(message)
    })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                successAlert("Shop status updated successfully");
                shopModelClose();
            } else {
                successAlert("Something went wrong");
            }
        });
}

const shopAdvancedSettings = () => {
    location.href = 'setting.html';
}

const loadAllSettings = () => {
    /* load all settings */
    fetch(apiurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "type=getAllOptions"
    })
        .then(res => res.json())
        .then(data => {
            let html = "";

            data.forEach(item => {
                html += `
      <div class="shop_model_row">
        <div class="shop_model_label">${item.type.replaceAll("_", " ")}</div>
        <label class="shop_model_switch">
          <input type="checkbox"
            ${item.details === "true" ? "checked" : ""}
            onchange="updateOption('${item.type}', this.checked)">
          <span class="shop_model_slider"></span>
        </label>
      </div>
    `;
            });

            document.getElementById("shop_model_list").innerHTML = html;
        });
}



/* update single option */
function updateOption(type, value) {
    fetch(apiurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "type=setOptionStatus" +
            "&option=" + type +
            "&value=" + (value ? "true" : "false")
    });
}








/* open modal + load data */
function shopContactOpen() {
    document.getElementById("shopContactModal").style.display = "flex";

    fetch(apiurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "type=getContactInfo"
    })
        .then(res => res.json())
        .then(data => {
            document.getElementById("contact_phone").value = data.phone_no || "";
            document.getElementById("contact_whatsapp").value = data.whatsapp_no || "";
            document.getElementById("contact_gmail").value = data.gmail || "";
            document.getElementById("contact_instagram").value = data.instagram || "";
        });
}

/* close modal */
function shopContactClose() {
    document.getElementById("shopContactModal").style.display = "none";
}

/* save contact info */
function saveContactInfo() {

    const phone = document.getElementById("contact_phone").value;
    const whatsapp = document.getElementById("contact_whatsapp").value;
    const gmail = document.getElementById("contact_gmail").value;
    const instagram = document.getElementById("contact_instagram").value;
    const facebook = document.getElementById("contact_facebook").value;

    fetch(apiurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "type=setContactInfo" +
            "&phone_no=" + encodeURIComponent(phone) +
            "&whatsapp_no=" + encodeURIComponent(whatsapp) +
            "&gmail=" + encodeURIComponent(gmail) +
            "&instagram=" + encodeURIComponent(instagram) +
            "&facebook=" + encodeURIComponent(facebook)
    })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                successAlert("Contact info updated");
                shopContactClose();
            } else {
                successAlert("Error updating contact info");
            }
        });
}


function getAllBranch() {
    $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getAllBranch",
        },
        success: function (response) {
            if (response.status == "success") {
                console.log(response.data);
                let data = response.data;
                let html = '<option value="0">Select Branch</option>';
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#branch").html(html);
            } else {
                console.log(response.message);
            }
        }
    })
}

