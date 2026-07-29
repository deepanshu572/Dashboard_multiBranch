// Image upload and preview

let categoryData = []; let subCategoryData = [];

let singleImageName = '';
let imageExtension = '';
$('#product-image').change(function () {
    const file = this.files[0];
    if (file) {
        if (file.size > 100 * 1024) { // Validate file size (100KB max)
            alert('Image size should not exceed 100 KB');
            $('#product-image').val('');
            $('#preview-img').attr('src', 'https://placehold.co/500x500');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#preview-img').attr('src', e.target.result);
            singleImageName = e.target.result;
            imageExtension = file.name.split('.').pop().toLowerCase();
        };
        reader.readAsDataURL(file);
    }
});


// product multiple images 

const fileInput = document.getElementById('product-images');
const imagePreviewContainer = document.getElementById('multiple-image-preview');
let imageFiles = []; // Array to hold file data

fileInput.addEventListener('change', (event) => {
    const files = Array.from(event.target.files);

    files.forEach((file) => {

        if (file.size > 100 * 1024) {
            return;
        }

        const reader = new FileReader();

        reader.onload = (e) => {
            const base64 = e.target.result;

            // Add file data to imageFiles array
            imageFiles.push({ name: file.name, data: base64, imgExtentsion: file.name.split('.').pop().toLowerCase() });

            const imageElement = document.createElement('div');
            imageElement.classList.add('mip');
            imageElement.innerHTML = `
                <div class="mip">
                <img src="${base64}" alt="Preview">
                <button class="delete-icon" title="Delete">&times;</button>
                </div>
                `;

            imagePreviewContainer.appendChild(imageElement);

            const deleteButton = imageElement.querySelector('.delete-icon');
            deleteButton.addEventListener('click', () => {

                imageElement.remove();
                imageFiles = imageFiles.filter((img) => img.data !== base64);
                console.log('Updated Files:', imageFiles);
            });

            console.log('Files:', imageFiles);
        };

        reader.readAsDataURL(file);
    });

    fileInput.value = '';
});

//  add more multiple images
const fileInput2 = document.getElementById('add-product-images');
const imagePreviewContainer2 = document.getElementById('add-multiple-image-preview');
let imageFiles2 = []; // Array to hold file data

fileInput2.addEventListener('change', (event) => {

    if (!fileInput2 || !imagePreviewContainer2) {
        console.error('File input or image preview container not found!');
        return;
    } else {
        console.log('File input or image preview container found')
    }
    const files = Array.from(event.target.files);

    files.forEach((file) => {

        if (file.size > 100 * 1024) {
            return;
        }

        const reader = new FileReader();

        reader.onload = (e) => {
            const base64 = e.target.result;

            // Add file data to imageFiles array
            imageFiles2.push({ name: file.name, data: base64, imgExtentsion: file.name.split('.').pop().toLowerCase() });

            const imageElement = document.createElement('div');
            imageElement.classList.add('mip');
            imageElement.innerHTML = `
                    <div class="mip">
                    <img src="${base64}" alt="Preview">
                    <button class="delete-icon" title="Delete">&times;</button>
                    </div>
                    `;

            imagePreviewContainer2.appendChild(imageElement);

            const deleteButton = imageElement.querySelector('.delete-icon');
            deleteButton.addEventListener('click', () => {

                imageElement.remove();
                imageFiles2 = imageFiles2.filter((img) => img.data !== base64);
                console.log('Updated Files:', imageFiles2);
            });

            console.log('Files:', imageFiles2);
        };

        reader.readAsDataURL(file);
    });

    fileInput2.value = '';
});






// let varientData = [];
// let vid = 1;
// $("#addmore").click(() => {
//     const vquantity = $("#vquantity").val();
//     const vunit = $("#vunit").val();
//     const vmrp = $("#vmrp").val();
//     const vsellingPrice = $("#vselling-price").val();
//     const vpurchasePrice = $("#vpurchase-price").val();
//     const vstock = $("#vstock").val();
//     const vlimit = $("#vlimit").val();

//     varientData.push(
//         {
//             vid: vid,
//             quantity: vquantity,
//             unit: vunit,
//             mrp: vmrp,
//             sellingPrice: vsellingPrice, purchasePrice: vpurchasePrice,
//             stock: vstock,
//             limit: vlimit
//         }
//     )

//     console.log(varientData);

//     $("#vareint-result").append(`
//         <div class="vdata" data-id="${vid}">
//           <p> Quantity: ${vquantity}</p>
//           <p> Unit: ${vunit}</p>
//           <p> MRP: ${vmrp}</p>
//           <p> Selling Price: ${vsellingPrice}</p>
//           <p> Purchase Price: ${vpurchasePrice}</p>
//           <p> Stock: ${vstock}</p>
//           <p> Limit: ${vlimit}</p>
//           <div class="vcancel flex" onclick="vareintCancel(${vid})">X</div>
//         </div>
//       `);


//     $("#vquantity").val("");
//     $("#vunit").val("");
//     $("#vmrp").val("");
//     $("#vselling-price").val("");
//     $("#vpurchase-price").val("");
//     $("#vstock").val("");
//     $("#vlimit").val("");

//     vid++;
//     $("#vquantity").focus();
// })

// function vareintCancel(vid) {
//     if (confirm("Are you sure you want to delete this variant?")) {
//         $(`.vdata[data-id="${vid}"]`).remove();

//         varientData = varientData.filter(variant => variant.vid !== vid);

//         console.log(`Variant with ID ${vid} has been removed.`);
//         console.log("Updated variantData:", varientData);
//     }
// }

//  add more variant data

let addvarientData = [];
let addvid = 1;

$("#addmorevarient").click(() => {
    const vquantity = $("#addvquantity").val();
    const vunit = $("#addvunit").val();
    const vmrp = $("#addvmrp").val();
    const vsellingPrice = $("#addvselling-price").val();
    const vpurchasePrice = $("#addvpurchase-price").val();
    const vstock = $("#addvstock").val();
    const vlimit = $("#addvlimit").val();

    addvarientData.push(
        {
            vid: addvid,
            quantity: vquantity,
            unit: vunit,
            mrp: vmrp,
            sellingPrice: vsellingPrice, purchasePrice: vpurchasePrice,
            stock: vstock,
            limit: vlimit
        }
    )

    console.log(addvarientData);

    $("#addvareint-result").append(`
        <div class="addvdata" data-id="${addvid}">
          <p> Quantity: ${vquantity}</p>
          <p> Unit: ${vunit}</p>
          <p> MRP: ${vmrp}</p>
          <p> Selling Price: ${vsellingPrice}</p>
          <p> Purchase Price: ${vpurchasePrice}</p>
          <p> Stock: ${vstock}</p>
         <p> Limit: ${vlimit}</p>
          <div class="vcancel flex" onclick="addVareintCancel(${addvid})">X</div>
        </div>
      `);


    $("#addvquantity").val("");
    $("#addvunit").val("");
    $("#addvmrp").val("");
    $("#addvselling-price").val("");
    $("#addvpurchase-price").val("");
    $("#addvstock").val("");

    addvid++;
    $("#addvquantity").focus();
})


const addMoreVarient = () => {
    let pid = localStorage.getItem('forAddMoreVarient_p_id');
    console.log('add more variant', pid);

    if (addvarientData.length == 0) {
        errorAlert('Please add variant data');
        return;
    }

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'addMoreVarient', p_id: pid, addvarientData: JSON.stringify(addvarientData) },
        success: function (response) {

            if (response == 'success') {
                successAlert('Successfully added');
                seeVarient(pid);
                addvarientData = [];
            } else {
                errorAlert(response);
            }
        }
    });


}



function addVareintCancel(vid) {
    // if (confirm("Are you sure you want to delete this variant?")) {
    $(`.addvdata[data-id="${vid}"]`).remove();

    addvarientData = addvarientData.filter(variant => variant.vid !== vid);

    console.log(`Variant with ID ${vid} has been removed.`);
    console.log("Updated addvarientData:", addvarientData);
    // }
}


let highlightData = [];
let hid = 1;
$("#highLightSubmit").click(() => {
    let htitle = $("#htitle").val();
    let hdescription = $("#hdescription").val();
    highlightData.push(
        {
            hid: hid,
            htitle: htitle,
            hdescription: hdescription
        }
    );


    $(".highlight-result").append(`
        <div class="flex flex-start hdata flex-wrap gap-30" data-id="${hid}">
        <p>Title : ${htitle} </p>
        <p>Description : ${hdescription} </p>
         <div class="vcancel flex" onclick="highlightCancel(${hid})">X</div>
        </div>
        `)

    $("#htitle").val("");
    $("#hdescription").val("");
    $("#htitle").focus();
    hid++;
})


function highlightCancel(hid) {
    if (confirm("Are you sure you want to delete this variant?")) {
        $(`.hdata[data-id="${hid}"]`).remove();

        highlightData = highlightData.filter(variant => variant.hid !== hid);

        console.log(`Variant with ID ${vid} has been removed.`);
        console.log("Updated highlightData:", highlightData);
    }
}



let informationData = [];
let iid = 1;
$("#informationSubmit").click(() => {
    let ititle = $("#ititle").val();
    let idescription = $("#idescription").val();
    informationData.push(
        {
            iid: iid,
            ititle: ititle,
            idescription: idescription
        }
    );


    $(".information-result").append(`
        <div class="flex flex-start idata flex-wrap gap-30" data-id="${iid}">
        <p>Title : ${ititle} </p>
        <p>Description : ${idescription} </p>
         <div class="vcancel flex" onclick="informationCancel(${iid})">X</div>
        </div>
        `)

    $("#ititle").val("");
    $("#idescription").val("");
    $("#ititle").focus();
    iid++;
})


function informationCancel(iid) {
    if (confirm("Are you sure you want to delete this variant?")) {
        $(`.idata[data-id="${iid}"]`).remove();

        informationData = informationData.filter(variant => variant.iid !== iid);

        console.log(`Variant with ID ${iid} has been removed.`);
        console.log("Updated informationData:", informationData);
    }
}

// add product 



$('#product-form').submit(function (e) {
    e.preventDefault();

    // Collect form data
    const category = $('#category').val();
    const middleCategory = $('#middle-category').val();
    const subCategory = $('#sub-category').val();
    const brandName = $('#brand_name').val();
    const productName = $('#product-name').val();
    const mrp = $('#mrp').val();
    const sellingPrice = $('#selling-price').val();
    const purchasePrice = $('#purchase-price').val();
    const stock = $('#stock').val();
    const quantity = $('#quantity').val();
    const unit = $('#unit').val();
    const review = $('#review').val();
    const reviewNop = $('#review-nop').val();
    const skuNumber = $('#sku-number').val();
    const product_limit = $('#product_limit').val();
    const product_keyword = $('#product_keyword').val();



    // Validate required fields
    if (!productName || !mrp || !sellingPrice || !category) {
        alert('Please fill out all required fields.');
        return;
    }

    const file = $('#product-image')[0].files[0];




    // Prepare FormData object
    const formData = new FormData();
    formData.append('type', 'addProduct');
    formData.append('category', category);
    formData.append('middleCategory', middleCategory);
    formData.append('subCategory', subCategory);
    formData.append('brandName', brandName);
    formData.append('productName', productName);
    formData.append('mrp', mrp);
    formData.append('sellingPrice', sellingPrice);
    formData.append('purchasePrice', purchasePrice);
    formData.append('stock', stock);
    formData.append('quantity', quantity);
    formData.append('unit', unit);
    formData.append('review', review);
    formData.append('reviewNop', reviewNop);
    formData.append('skuNumber', skuNumber);
    formData.append('product_limit', product_limit);
    formData.append('product_keyword', product_keyword);

    formData.append('base64Image', singleImageName);
    formData.append('fileExtension', imageExtension);
    // let isvarint = varientData.length > 0 ? 'true' : 'false';
    formData.append('isvarint', true);

    // Add array data
    formData.append('informationData', JSON.stringify(informationData));
    formData.append('highlightData', JSON.stringify(highlightData));
    // formData.append('variantData', JSON.stringify(varientData));
    formData.append('imageFiles', JSON.stringify(imageFiles));

    $("#submit-button").prop("disabled", true)
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            successAlert('Successfully added');
            $("#submit-button").prop("disabled", false);
            loadProduct();
        },
        error: function (xhr, status, error) {
            alert('Error submitting data: ' + error);
            $("#submit-button").prop("disabled", false);
        },
    });



});


let categoryListData = [];
const loadCategoryList = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadCategory' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                categoryListData = data;
                categoryData = data;
                let categoryList = ' <option value="all" selected disabled hidden>Select Category</option>';
                let categoryList2 = ' <option value="all">Select Category</option>';
                data.forEach((item, index) => {
                    categoryList += `
                        <option value="${item.id}">${item.name}</option>
                        `;
                    categoryList2 += `
                        <option value="${item.id}">${item.name}</option>
                        `;
                })
                $("#category").html(categoryList);
                $("#selected_category").html(categoryList2);
            }
        }
    })

}

let subCategoryListData = [];
const loadAllSubCategoryList = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadAllSubCategoryList' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                subCategoryListData = data;
                subCategoryData = data;
                let subCategoryList = ' <option value="all">Select Subcategory</option>';
                data.forEach((item, index) => {
                    subCategoryList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                })
                $("#selected_sub_category").html(subCategoryList);
            }
        }
    })

}

const handleCategoryList = () => {
    let categoryid = $("#category").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadMiddleCategoryList', categoryid: categoryid },
        success: function (response) {
            console.log(response);
            if (response != null && response != 'error') {
                let data = JSON.parse(response);

                let subCategoryList = ' <option value="" selected disabled hidden>Select Middle Catgeory</option>';
                data.forEach((item, index) => {
                    subCategoryList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                })
                $("#middle-category").html(subCategoryList);
                // $(".selected_sub_category").html(subCategoryList);
                $("#middle-category").prop("required", true);
            } else {
                $("#middle-category").html(`
                    <option value="" selected disabled hidden>no data found</option>
                    `);
                $("#middle-category").prop("required", false);
            }
        }
    });
    loadBrandList(categoryid, 'brand_name')
}
let middleCategoryListData = [];
const getMiddleCategory = () =>{
     return $.ajax({
        url: apiurl,
        type: 'POST',
        dataType:"JSON",
        data: { type: 'getAllMiddleCategory' },
        success: function (response) {
            if(response.status == "success"){
            let data = response.data;
                middleCategoryListData=data;
            }
            else{
                console.log("something wents wrong in getMiddleCategory !");
            }
        }
    })
}
const handleMiddleCategoryList = () => {
    let categoryid = $("#middle-category").val();
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadSubCategoryList', categoryid: categoryid },
        success: function (response) {
            console.log(response);
            if (response != null && response != 'error') {
                let data = JSON.parse(response);

                let subCategoryList = ' <option value="" selected disabled hidden>Select Subcategory</option>';
                data.forEach((item, index) => {
                    subCategoryList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                })
                $("#sub-category").html(subCategoryList);
                $(".selected_sub_category").html(subCategoryList);
                $("#sub-category").prop("required", true);
            } else {
                $("#sub-category").html(`
                    <option value="" selected disabled hidden>no data found</option>
                    `);
                $("#sub-category").prop("required", false);
            }
        }
    })
}


let brandListData = [];
const loadBrandList = (categoryId = '', brandName=undefined) => {
    return $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadBrands' },
        success: function (response) {
            if (response != null && response != 'error' && response != 'null') {
                let data = JSON.parse(response);
                brandListData = data;
                    let filteredData = data.filter((item) => item.categoryId == categoryId);

                    let brandList = ' <option value="all" selected disabled hidden>Select Brand</option>';
                    filteredData.forEach((item, index) => {
                        brandList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                    });
                    console.log("ZEENAT......", brandName);
                    $(`#${brandName}`).html(brandList);
                
                console.log("ZEENAT NOT......", brandName,);
                console.log("ZEENAT NOT......", categoryId);
                console.log("ZEENAT NOT......", brandListData);

            } else {
                $(`#${brandName}`).html(`
                    <option value="" selected disabled hidden>no data found</option>
                    `);
            }
        }
    })
}



//  fetch all product 

let productData = [];
let filteredData = [];
let pageNumber = 1;
let pageSize = 10;

const loadProduct = (pageNumber = 1, pageSize = 10) => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadProduct' },
        success: function (response) {
            if (response != null && response != 'error') {
                let data = JSON.parse(response);
                productData = data;

                if (localStorage.getItem('admin_role') === 'admin') {
                    $("#filter-created-by").show();
                    let creators = [...new Set(data.map(item => item.added_by || 'admin'))];
                    let options = `<option value="all">All Creators</option>`;
                    creators.forEach(c => {
                        options += `<option value="${c}">@${c}</option>`;
                    });
                    $("#filter-created-by").html(options);
                }

                filteredData = data;

                renderProduct(pageNumber, pageSize)

            }
        }
    })
}



const renderProduct = (page, pageSize) => {

    console.log('Rendering page:', page, 'with page size:', pageSize);

    // 🔹 Get selected filter values
    const selectedCategory = $("#selected_category").val();
    const selectedSubCategory = $("#selected_sub_category").val();
    const selectedStock = $("#selected_stock").val();
    const select_title = $("#select_title").val().toLowerCase();
    const select_best_title = $("#select_best_title").val().toLowerCase();
    const select_find_title = $("#select_find_title").val().toLowerCase();
    const createdByText = $("#filter-created-by").val();

    let searchText = $("#search-input").val().toLowerCase(); // Get the search text and convert it to lowercase

    // 🔹 Apply filters on productData
    filteredData = productData.filter(item => {
        let categoryMatch = selectedCategory === "all" || item.under_category == selectedCategory;
        let subCategoryMatch = selectedSubCategory === "all" || item.under_subcategory == selectedSubCategory;
        let createdByMatch = true;

        if (createdByText && createdByText !== 'all') {
            let addedBy = item.added_by || 'admin';
            if (addedBy !== createdByText) {
                createdByMatch = false;
            }
        }

        return categoryMatch && subCategoryMatch && createdByMatch;
    });

    // 🔹 Stock Sorting / Filtering
    if (selectedStock === "ltoh") {
        filteredData = filteredData.sort((a, b) => parseInt(a.stock) - parseInt(b.stock)); // Low → High
    } else if (selectedStock === "htol") {
        filteredData = filteredData.sort((a, b) => parseInt(b.stock) - parseInt(a.stock)); // High → Low
    } else if (selectedStock === "oos") {
        filteredData = filteredData.filter(item => parseInt(item.stock) <= 0); // Only Out of Stock
    }


    if (select_title != "all") {
        title = "title" + select_title;

        filteredData = filteredData.filter(item => item[title] == 'true');

    }
    if (select_best_title != "all") {
        title = "btitle" + select_best_title;
        filteredData = filteredData.filter(item => item[title] == 'true');
    }

    if (select_find_title != "all") {
        title = "dtitle" + select_find_title;
        filteredData = filteredData.filter(item => item[title] == 'true');
    }


    if (searchText) {
        filteredData = filteredData.filter(product =>
            product.name.toLowerCase().includes(searchText) ||
            product.sku_number.includes(searchText)
        );
    }



    page = parseInt(page);

    const totalItems = filteredData.length;
    const totalPages = Math.ceil(totalItems / pageSize);
    const paginatedData = filteredData.slice((page - 1) * pageSize, page * pageSize);



    $("#totalData").html(filteredData.length);
    let html = `
    <thead>
            <tr>
                <th>SL</th>
                <th>Image</th>
                <th>Name</th>
                <th>MRP</th>
                <th>Selling <br> price</th>
                <th>Purchase <br> price</th>
                <th>Stock <br> Limit</th>
                <th>Quantity</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Select <br> Bestseller</th>
                <th>Select <br> Title</th>
                <th>Select <br> New Finds</th>
                <th>Other</th>
                <th>Action</th>
            </tr>
        </thead>
    <tbody>
    `;
    paginatedData.forEach((item, index) => {

        let items = JSON.stringify(item).replace(/'/g, '`');
        html += `
       <tr>
            <td class="sl">${(page - 1) * pageSize + index + 1}</td>
            <td><img src="${imgurl + item.image_path}" alt="${item.name}"></td>
            <td>${item.name}</td>
            <td>${item.mrp}</td>
            <td>${item.selling_price}</td>
            <td>${item.purchase_price}</td>
            <td>S - ${item.stock} <br> L - ${item.p_limit}</td>
            <td>${item.quantity} ${item.unit}</td>
            <td style="color:#4F46E5; font-size:12px;"><b>@${item.added_by || 'admin'}</b></td>
            <td>
            <label class="switch">
            <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
            onclick="handleCheckboxChange(this,'status','${item.p_id}')">
            <span class="slider"></span>
            </label>
            </td>
            <td>
            <div class="custom-select custom-select2" onclick="openCustomSelect2(${item.p_id})" id="csbb${item.p_id}">
            <div class="custom-select-box flex gap-5" >
                <span class="selected-option">Select Title</span>
                <div class="arrow"><i class="bi bi-caret-down-fill"></i></div>
            </div>
            <ul class="custom-select-options">
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.btitle1 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'btitle1', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 1
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.btitle2 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'btitle2', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 2
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.btitle3 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'btitle3', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 3
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.btitle4 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'btitle4', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 4
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.btitle5 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'btitle5', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 5
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.btitle6 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'btitle6', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 6
                </li>
               
            </ul>
        </div>
            </td>
    
            <td>
    <div class="custom-select " onclick="openCustomSelect(${item.p_id})" id="csb${item.p_id}">
            <div class="custom-select-box flex gap-5" >
                <span class="selected-option">Select Title</span>
                <div class="arrow"><i class="bi bi-caret-down-fill"></i></div>
            </div>
            <ul class="custom-select-options">
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.title1 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'title1', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 1
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.title2 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'title2', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 2
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.title3 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'title3', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 3
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.title4 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'title4', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 4
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.title5 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'title5', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 5
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.title6 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'title6', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 6
                </li>
               
            </ul>
        </div>
            </td>

            <!--new finder start-->

                        <td>
            <div class="custom-select custom-select2" onclick="openCustomSelect3(${item.p_id})" id="nsbb${item.p_id}">
            <div class="custom-select-box flex gap-5" >
                <span class="selected-option">Select Title</span>
                <div class="arrow"><i class="bi bi-caret-down-fill"></i></div>
            </div>
            <ul class="custom-select-options">
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.dtitle1 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'dtitle1', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 1
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.dtitle2 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'dtitle2', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 2
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.dtitle3 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'dtitle3', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 3
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.dtitle4 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'dtitle4', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 4
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.dtitle5 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'dtitle5', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 5
                </li>
                <li>
                    <label class="switch">
                        <input type="checkbox" ${item.dtitle6 == 'true' ? "checked" : ""} onclick="handleCheckboxChange(this, 'dtitle6', '${item.p_id}')">
                        <span class="slider"></span>
                    </label>
                    Title 6
                </li>
               
            </ul>
        </div>
            </td>

            <!--new finder end-->

                    <td >
            <div class="flex gap-20 pbtn">
                <button class="flex" onclick="seeAllInfo(${item.p_id})"><i class="bi bi-eye"></i></button>
                <button class="flex" onclick="seeImages(${item.p_id})"><i class="bi bi-images"></i></button>
                <button class="flex" onclick="seeVarient('${item.p_id}' ,'${item.image_path}')"><i class="bi bi-info"></i></button>
                </div>
            </td>
            <td>
            <div class="flex gap-20">
                <button class="edit flex" onclick='editProduct(${items} ,${page} ,${pageSize})'><i class="bi bi-pencil"></i></button>
                <button class="delete flex" onclick="deleteProduct(${item.p_id})"><i class="bi bi-trash3"></i></button>
                </div>
            </td>
        </tr>
        `;
    });
    html += `</tbody>`;
    $("#result").html(html);

    // Generate pagination buttons with Previous and Next
    // Generate pagination buttons
    let paginationHtml = `<div class="pagination">`;

    if (page > 1) {
        paginationHtml += `<button class="page-btn" onclick="renderProduct(${page - 1}, ${pageSize})"><i class="bi bi-chevron-double-left"></i></button>`;
    }

    let startPage = Math.max(1, page - 1);
    let endPage = Math.min(totalPages, page + 1);

    if (startPage > 1) {
        paginationHtml += `<button class="page-btn" onclick="renderProduct(1, ${pageSize})">1</button>`;
        if (startPage > 2) {
            paginationHtml += `<span class="ellipsis">..</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="renderProduct(${i}, ${pageSize})">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<span class="ellipsis">..</span>`;
        }
        paginationHtml += `<button class="page-btn" onclick="renderProduct(${totalPages}, ${pageSize})">${totalPages}</button>`;
    }

    if (page < totalPages) {
        paginationHtml += `<button class="page-btn" onclick="renderProduct(${page + 1}, ${pageSize})"><i class="bi bi-chevron-double-right"></i></button>`;
    }

    paginationHtml += `</div>`;
    $("#pagination").html(paginationHtml);
}


//  seacr product

const searchProduct = () => {
    let searchText = $("#search-input").val().toLowerCase(); // Get the search text and convert it to lowercase
    filteredData = productData.filter(product =>
        product.name.toLowerCase().includes(searchText) ||
        product.sku_number.includes(searchText)
    );
    renderProduct(pageNumber, pageSize); // Re-render the table with filtered data
};

// Call loadAllUser once to fetch data and render the first page
// loadAllUser();

// Attach keyup event to the search input field
$(document).on("keyup", "#search-input", function () {
    searchProduct();
});

$(document).on("change", "#filter-created-by", function () {
    pageNumber = 1;
    renderProduct(pageNumber, pageSize);
});








const seeImages = (p_id) => {
    console.log(p_id);
    localStorage.setItem('foraddproduct_p_id', p_id);
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'seeImages', p_id: p_id },
        success: function (response) {
            if (response != null && response != 'error') {
                let data = JSON.parse(response);
                console.log(data);
                data = data.sort((a, b) => a.img_id - b.img_id);
                data.shift();
                let imgList = '';
                data.forEach((item, index) => {
                    imgList += `
                    <div class="imgList">
                    <img src="${imgurl + item.image_path}" />
                    <button class="flex" onclick="deleteVarientImage(${item.img_id},${p_id})"><i class="bi bi-x-lg"></i></button>
                    </div>
                    `;
                })

                $(".img-model-result").html(imgList);
                $("#product-id").val(p_id);
                $(".wrapper-overlay").addClass('active');
                $(".img-model").addClass('active');
                $('body').css('overflow', 'hidden');

            }
        }
    })
}

const seeAllInfo = async (p_id) => {
    console.log(p_id);

    await $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'seeAllInfo', p_id: p_id },
        success: function (response) {
            if (response != null && response != 'error') {
                let data = JSON.parse(response);
                console.log(data);
                let product = '';
                data.forEach((item) => {

                    let editItem = JSON.stringify(item).replace(/'/g, '`');
                    let highlight = item.highlight;
                    let information = item.information;
                    highlight = highlight.slice(1, -1);
                    information = information.slice(1, -1);
                    // Step 1: Remove backslashes
                    highlight = highlight.replace(/\\/g, '');

                    // Step 2: Remove single quotes
                    highlight = highlight.replace(/'/g, '`');


                    // Step 1: Remove backslashes
                    information = information.replace(/\\/g, '');

                    // Step 2: Remove single quotes
                    information = information.replace(/'/g, '`');


                    console.log(highlight, information);

                    highlight = JSON.parse(highlight);
                    information = JSON.parse(information);

                    console.log(highlight);

                    let highlightGrid = '';
                    highlight.forEach((items) => {
                        highlightGrid += `<div class="flex headertitle flex-start gap-10">
                            <h5>${items.htitle} : </h5>
                            <p>${items.hdescription}</p>
                        </div>`;
                    });

                    let informationGrid = '';
                    information.forEach((items) => {
                        informationGrid += `<div class="flex headertitle flex-start gap-10">
                            <h5>${items.ititle} : </h5>
                            <p>${items.idescription}</p>
                        </div>`;
                    });

                    product += `
                    <div class="modal-content">
                        <div class="product-container">
                            <div class="image-section">
                                <div class="main-image">
                                    <img src="${imgurl + item.image_path}" alt="Main Image" id="mainImage">
                                </div>
                            </div>
                
                            <!-- Product Details Section -->
                            <div class="details-section">
                            <div class="flex space-between gap-10">
                                <h2>${item.name}</h2>
                                <button class="close-button" onclick='editDescription(${editItem})'>Edit</button>
                                </div>
                                <p><span class="mrp">MRP : ₹<del>${item.mrp}</del></span></p>
                                <p class="price">Selling Price : ₹<span>${item.selling_price}</span></p>
                                <p class="discount">Purchased Price : ${item.purchase_price}</p>
                                <div>
                                    <h3>Highlight</h3>
                                    <div class="highlightGrid">
                                        ${highlightGrid}
                                    </div>
                                </div>
                                <div>
                                    <h3>Information</h3>
                                    <div class="informationGrid">
                                       ${informationGrid}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });

                $(".model-result").html(product);
                $(".wrapper-overlay").addClass('active');
                $(".model").addClass('active');
                $('body').css('overflow', 'hidden');

            }
        }
    })
}

const seeVarient = async (p_id, image_path) => {

    localStorage.setItem('forAddMoreVarient_p_id', p_id);

    await $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'seeVarient', p_id: p_id },
        success: function (response) {
            if (response != 'error' && response != 'null') {
                let data = JSON.parse(response);
                console.log(data);
                let varient = '';
                const units = [
                    // Grocery & General Food Items
                    "gm", "kg", "ml", "liter", "packet", "bottle", "can", "jar", "tin", "sachet", "bar", "box", "tray",

                    // Fruits & Vegetables
                    "pieces", "bunch", "dozen", "bundle", "pair",

                    // Clothes
                    "piece", "set", "pack", "s", "m", "l", "xl", "xxl",

                    // Electronics
                    "unit", "full plate", "half plate"
                ];
                data.forEach((item) => {


                    let unitList = ' <option value="" selected disabled hidden>Select Unit</option>';
                    units.forEach((unit, index) => {
                        const isSelected = (unit === item.v_unit) ? 'selected' : '';
                        unitList += `
                       <option ${isSelected} value="${unit}">${unit}</option>
                          `;
                    })

                    varient += `
                    <div class="edit-vdata" data-id="${item.vid}">
                        <img  src="${imgurl + image_path}" alt="${item.name}">
                        <div>
                        <p> Quantity</p>
                        <input type="text" value="${item.v_quantity}" id="v_quantity${item.vid}" placeholder="Quantity" readonly>
                        </div>
                        <div>
                        <p>Unit</p>
                        <select id="v_unit${item.vid}" disabled>
                        ${unitList}
                        </select>
                        </div>
                        <div>
                        <p>MRP</p>
                        <input type="number" value="${item.v_mrp}" id="v_mrp${item.vid}" placeholder="Mrp" readonly>
                        </div>
                        <div>
                        <p>Selling Price</p>
                        <input type="number" value="${item.v_seliing_price}" id="v_seliing_price${item.vid}" placeholder="Mrp" readonly>
                        </div>
                        <div>
                        <p>Purchase Price</p>
                        <input type="number" value="${item.v_purchase_price}" id="v_purchase_price${item.vid}" placeholder="Mrp" readonly>
                        </div>
                        <div>
                        <p>Stock</p>
                        <input type="number" value="${item.v_stock}" id="v_stock${item.vid}" placeholder="Mrp" readonly>
                        </div>
                        <div>
                        <p>Limit</p>
                        <input type="number" value="${item.v_p_limit}" id="v_p_limit${item.vid}" placeholder="Limit" readonly>
                        </div>

                        <div class="veditdelete"> 
                            <button class="flex edit" onclick="editVarient(${item.vid})"><i class="bi bi-pencil"></i></button>
                            <button class="flex update" onclick="updateVarient(${item.vid})"><i class="bi bi-save"></i></button>
                            <button class="flex delete" onclick="deleteVarient(${item.vid})"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    `;
                });

                $(".v-model-result").html('');
                $(".v-model-result").html(varient);
                $(".wrapper-overlay").addClass('active');
                $(".v-model").addClass('active');
                $('body').css('overflow', 'hidden');
            } else {
                $(".v-model-result").html("no data found");
                $(".wrapper-overlay").addClass('active');
                $(".v-model").addClass('active');
                $('body').css('overflow', 'hidden');
            }
        }
    })
}

const editVarient = (vid) => {

    $(`.edit-vdata[data-id="${vid}"] input`).removeAttr('readonly');
    $(`.edit-vdata[data-id="${vid}"] input`).css('border', '1px solid #000');
    $(`.edit-vdata[data-id="${vid}"] select`).removeAttr('disabled');
    $(`.edit-vdata[data-id="${vid}"] select`).css('border', '1px solid #000');
    $(`.edit-vdata[data-id="${vid}"] .update`).show();
    $(`.edit-vdata[data-id="${vid}"] .edit`).hide();

}

const updateVarient = (vid) => {
    const vquantity = $(`#v_quantity${vid}`).val();
    const vunit = $(`#v_unit${vid}`).val();
    const vmrp = $(`#v_mrp${vid}`).val();
    const vsellingPrice = $(`#v_seliing_price${vid}`).val();
    const vpurchasePrice = $(`#v_purchase_price${vid}`).val();
    const vstock = $(`#v_stock${vid}`).val();
    const vlimit = $(`#v_p_limit${vid}`).val();



    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'updateVarient', vid: vid, vquantity: vquantity, vunit: vunit, vmrp: vmrp, vsellingPrice: vsellingPrice, vpurchasePrice: vpurchasePrice, vstock: vstock, vlimit: vlimit },
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully updated');
                $(`.edit-vdata[data-id="${vid}"] input`).attr('readonly', true);
                $(`.edit-vdata[data-id="${vid}"] input`).css('border', 'none');
                $(`.edit-vdata[data-id="${vid}"] select`).attr('disabled', true);
                $(`.edit-vdata[data-id="${vid}"] select`).css('border', 'none');
                $(`.edit-vdata[data-id="${vid}"] .update`).hide();
                $(`.edit-vdata[data-id="${vid}"] .edit`).show();
                $(`#v_quantity${vid}`).val(vquantity);
                $(`#v_unit${vid}`).val(vunit);
                $(`#v_mrp${vid}`).val(vmrp);
                $(`#v_seliing_price${vid}`).val(vsellingPrice);
                $(`#v_purchase_price${vid}`).val(vpurchasePrice);
                $(`#v_stock${vid}`).val(vstock);
                $(`#v_p_limit${vid}`).val(vlimit);
            } else {
                errorAlert('Something went wrong');
            }
        }
    })

}

const deleteVarient = async (vid) => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'deleteVarient', vid: vid },
        success: function (response) {
            if (response == 'success') {
                successAlert('successfully deleted');
                $(`.edit-vdata[data-id="${vid}"]`).remove();
            } else {
                errorAlert('Something went wrong');
            }
        }
    })
}



$(".wrapper-overlay").click(() => {
    $(".wrapper-overlay").removeClass('active');
    $(".description-model").removeClass('active');
    $(".model").removeClass('active');
    $(".img-model").removeClass('active');
    $(".v-model").removeClass('active');
    $('body').css('overflow', 'auto');

    localStorage.removeItem('foraddproduct_p_id');
    isediting = false;
})


const addMoreImages = () => {

    let p_id = localStorage.getItem('foraddproduct_p_id');

    const formData = new FormData();
    formData.append('type', 'addMoreImages');
    formData.append('p_id', p_id);
    formData.append('imageFiles', JSON.stringify(imageFiles2));

    console.log(p_id, imageFiles2);

    if (imageFiles2.length == 0) {
        errorAlert('Please select images');
        return;
    }

    $(".addMoreBtn").prop("disabled", true)
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {

            successAlert('Successfully added');
            $(".addMoreBtn").prop("disabled", false);
            seeImages(p_id);

        },
        error: function (xhr, status, error) {
            errorAlert('Error submitting data: ' + error);
            $(".addMoreBtn").prop("disabled", false);
        },
    });

}


//  delete varient images 

const deleteVarientImage = async (img_id, p_id) => {
    console.log(img_id);
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteVarientImage', img_id: img_id },
            success: function (response) {
                if (response != 'error' && response != 'null') {
                    successAlert('Successfully Deleted');
                    seeImages(p_id);
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })
    }
}

const deleteProduct = async (p_id) => {
    console.log(p_id);
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteProduct', p_id: p_id },
            success: function (response) {
                if (response != 'error' && response != 'null') {
                    successAlert('Successfully Deleted');
                    loadProduct();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })
    }
}





const openCustomSelect = (p_id) => {
    console.log(p_id);

    if ($(`#csb${p_id}`).hasClass('open')) {
        $(`#csb${p_id}`).removeClass('open');
        // $(`#csb${p_id}`).addClass('csb');
    } else {
        $(".csb").removeClass('open');
        $(`#csb${p_id}`).addClass('csb');
        $(`#csb${p_id}`).addClass('open');
    }
}

const openCustomSelect2 = (p_id) => {
    console.log(p_id);

    if ($(`#csbb${p_id}`).hasClass('open')) {
        $(`#csbb${p_id}`).removeClass('open');
        // $(`#csb${p_id}`).addClass('csb');
    } else {
        $(".csbb").removeClass('open');
        $(`#csbb${p_id}`).addClass('csbb');
        $(`#csbb${p_id}`).addClass('open');
    }
}

const openCustomSelect3 = (p_id) => {
    console.log(p_id);

    if ($(`#nsbb${p_id}`).hasClass('open')) {
        $(`#nsbb${p_id}`).removeClass('open');
        // $(`#csb${p_id}`).addClass('csb');
    } else {
        $(".nsbb").removeClass('open');
        $(`#nsbb${p_id}`).addClass('csbb');
        $(`#nsbb${p_id}`).addClass('open');
    }
}





function handleCheckboxChange(checkbox, typeStatus, id) {
    const isChecked = checkbox.checked;
    console.log('type : ', typeStatus, id)
    Swal.fire({
        title: 'Are you sure?',
        text: isChecked
            ? "You want to activate this "
            : "You want to deactivate this ",
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
                data: { 'type': 'updateProductTitle', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
                success: function (response) {
                    if (response != 'error') {
                        Swal.fire({
                            title: 'Success!',
                            text: isChecked
                                ? ` activated successfully`
                                : ` deactivated successfully`,
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



let isediting = false;

const editProduct = async (item, pageNumber, pageSize) => {
    await loadBrandList(item.under_category);
    await getMiddleCategory();
    console.log(item);

    isediting = true;
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);
    $(".wrapper-overlay").addClass('active');
    $(".model").addClass('active');
    $('body').css('overflow', 'hidden');

    let categoryList = `<option selected disable hidden value="">Select Category</option>`;

    categoryListData.forEach((category, index) => {
        // Check if the category.id matches under_category
        const isSelected = (category.id === item.under_category) ? 'selected' : '';
        categoryList += `
        <option value="${category.id}" ${isSelected}>${category.name}</option>
        `;
    });



    let subcategoryList = ` <option selected disable hidden value="">Select Sub-Category</option>`;

    let subcategoryFilterData = subCategoryListData.filter((sub) => sub.under_category == item.under_category);
    subcategoryFilterData.forEach((subcategory, index) => {
        // Check if the category.id matches under_category
        const isSelected = (subcategory.id === item.under_subcategory) ? 'selected' : '';
        subcategoryList += `
        <option value="${subcategory.id}" ${isSelected}>${subcategory.name}</option>
        `;
    });


    let middleCategory = `<option selected disable hidden value="">Select Middle Category</option>`;
    let middlecategoryFilterData = middleCategoryListData.filter((sub) => sub.category_id == item.under_category);
    middlecategoryFilterData.forEach((middleCat, index) => {
        // Check if the category.id matches under_category
        const isSelected = (middleCat.id === item.under_middle_category) ? 'selected' : '';
        middleCategory += `
        <option value="${middleCat.id}" ${isSelected}>${middleCat.name}</option>
        `;
    });

    let brandList = ' <option value="" selected disabled hidden>Select Brand</option>';
    let filteredData = brandListData.filter((brditem) => brditem.categoryId == item.under_category);
    // console.log(filteredData,brandListData)
    if(filteredData.length>0){
      filteredData.forEach((brand, index) => {
        const isSelected = (brand.id === item.brand_name) ? 'selected' : '';
        brandList += `
        <option ${isSelected} value="${brand.id}">${brand.name}</option>
        `;
      });
    }
    else{
        brandList+=` <option value="" selected disabled hidden>no data found</option>`;
    }

    const units = [
        // Grocery & General Food Items
        "gm", "kg", "ml", "liter", "packet", "bottle", "can", "jar", "tin", "sachet", "bar", "box", "tray",

        // Fruits & Vegetables
        "pieces", "bunch", "dozen", "bundle", "pair",

        // Clothes
        "piece", "set", "pack", "s", "m", "l", "xl", "xxl",

        // Electronics
        "unit", "full plate", "half plate"
    ];

    let unitList = ' <option value="" selected disabled hidden>Select Unit</option>';
    units.forEach((unit, index) => {
        const isSelected = (unit === item.unit) ? 'selected' : '';
        unitList += `
        <option ${isSelected} value="${unit}">${unit}</option>
        `;
    })


    $(".model-result").html(`
        <input type="text" id="pageNumber" hidden value="${pageNumber}">
        <h1 style="text-align:center; margin-bottom:10px; width:100%;"> Edit Product </h1>
        <div class="edit-field"> 
             <div class="flex space-between gap-20">
                        <div class="form-group">
                            <label for="edit-category">Category</label>
                            <select id="edit-category" name="category" required onchange="handleCategoryList2()">
                                ${categoryList}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-main-category">Category</label>
                            <select id="edit-main-category" name="category" required onchange="handleMainCategoryList2()">
                                ${middleCategory}
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-sub-category">Sub-Category</label>
                            <select id="edit-sub-category" name="sub-category" >
                               ${subcategoryList}
                            </select>
                        </div>
                    </div>
        </div>
        <div class="edit-field"> 
             <div class="flex space-between gap-20">
                         <div class="form-group">
                            <label for="edit_brand_name">Brand Name</label>
                            <select id="edit_brand_name" name="brand-name" >
                               ${brandList}
                            </select>
                        </div>
                         <div class="form-group">
                            <label for="product-name">Product Name</label>
                            <input type="text" id="edit-product-name" name="product-name" placeholder="Product Name"
                                value="${item.name}">
                        </div>
                </div>
        </div>
        <div class="edit-field"> 
          <div class="flex space-between gap-20">
                <div class="form-group">
                            <label for="edit-mrp">MRP</label>
                            <input type="number" id="edit-mrp" name="mrp" placeholder="MRP" value="${item.mrp}" required>
                </div>

                <div class="form-group">
                            <label for="edit-selling-price">Selling Price</label>
                            <input type="number" id="edit-selling-price" name="selling-price" placeholder="Selling Price"
                            value="${item.selling_price}" required>
                </div>
            </div>
        </div>
        <div class="edit-field"> 
          <div class="flex space-between gap-20">
                 <div class="form-group">
                            <label for="edit-purchase-price">Purchase Price</label>
                            <input type="number" id="edit-purchase-price" name="purchase-price" placeholder="Purchase Price"
                               value="${item.purchase_price}" required>
                </div>
                <div class="form-group">
                            <label for="edit-stock">Stock</label>
                            <input type="number" id="edit-stock" name="stock" placeholder="Stock" value="${item.stock}" required>
                </div>
            </div>
        </div>

        <div class="edit-field"> 
          <div class="flex space-between gap-20">
                 <div class="form-group">
                            <label for="edit-quantity">Quantity</label>
                            <input type="text" id="edit-quantity" name="quantity" placeholder="Quantity" value="${item.quantity}" required>
                        </div>

                        <div class="form-group">
                            <label for="edit-unit">Select Unit</label>
                            <select id="edit-unit" name="unit" required>
                            ${unitList}
                            </select>
                </div>
            </div>
        </div>

        <div class="edit-field"> 
         <div class="flex space-between gap-20">
                <div class="form-group">
                            <label for="edit-review">Review Value</label>
                            <input type="number" id="edit-review"  max="5" 
                            maxlength="1" 
                            oninput="validateInput2(this)"  placeholder="Review Value (0-5)" value="${item.review_val}" required>
                </div>

                <div class="form-group">
                            <label for="edit-review-nop">Number of People (Review)</label>
                            <input type="number" id="edit-review-nop" name="selling-price"
                                placeholder="Number of People (Review)" value="${item.review_nop}" required>
                 </div>
            </div>
        </div>
        <div class="edit-field"> 
            <div class="flex space-between gap-20">
                            <div class="form-group">
                                <label for="edit-sku-number">SKU Number </label>
                                <input type="text" id="edit-sku-number" placeholder="SKU Number (Optional)" value="${item.sku_number}">
                            </div>
                            <div class="form-group">
                                <label for="edit-product-limit">Product Limit</label>
                                <input type="text" id="edit-product-limit" placeholder="Product Limit" value="${item.p_limit}">
                            </div>
                           
             </div>
             <div class="flex space-between gap-20">
                            
                            <div class="form-group ">
                            <label for="edit_product_keyword">Product Keyword </label>
                            <textarea class="product_keyword" id="edit_product_keyword" placeholder="Product Keyword (seprate by , ) ex : maggi , nuddle , chowmine , yappe ">${item.keyword}</textarea>
                        </div>
                           
             </div>
             <div class="flex space-between gap-20">
                           
                            <div class="form-group">
                                <label for="edit-product-image">Product Image <span>(Ratio 1:1)</span></label>
                                <input type="file" id="edit-product-image" accept="image/*" required>
                            </div>
                            <div class="image-preview flex gap-30">
                                <img id="edit-preview-img" src="${imgurl + item.image_path}" alt="Preview">
                            </div>
             </div>
        </div>
        
         <div class="edit-btn-section flex"> 
            <button  onclick="updateProduct(${item.p_id})">Update Product</button>
        </div>
        `);


}


const handleMainCategoryList2 = () => {
    let categoryid = $("#edit-main-category").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadSubCategoryList', categoryid: categoryid },
        success: function (response) {
            console.log(response);
            if (response != null && response != 'error') {
                let data = JSON.parse(response);
                let subCategoryList = ' <option value="" selected disabled hidden>Select Subcategory</option>';
                data.forEach((item, index) => {
                    subCategoryList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                })
                $("#edit-sub-category").html(subCategoryList);
                $("#edit-sub-category").prop("required", true);
            } else {
                $("#edit-sub-category").html(`
                    <option value="" selected disabled hidden>no data found</option>
                    `);
                $("#edit-sub-category").prop("required", false);
            }
        }
    })
}

const handleCategoryList2 = () =>{
     let categoryid = $("#edit-category").val();

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadMiddleCategoryList', categoryid: categoryid },
        success: function (response) {
            console.log(response);
            if (response != null && response != 'error') {
                let data = JSON.parse(response);
                let subCategoryList = ' <option value="" selected disabled hidden>Select Subcategory</option>';
                data.forEach((item, index) => {
                    subCategoryList += `
                    <option value="${item.id}">${item.name}</option>
                    `;
                })
                $("#edit-main-category").html(subCategoryList);
                $("#edit-main-category").prop("required", true);
            } else {
                $("#edit-main-category").html(`
                    <option value="" selected disabled hidden>no data found</option>
                    `);
                $("#edit-main-category").prop("required", false);
            }
        }
    })
}

const validateInput2 = (input) => {
    if (input.value > 5) {
        input.value = 5;
    }

    // Ensure value is a single digit
    if (input.value.length > 1) {
        input.value = input.value.slice(0, 1);
    }
}





//  update product data

function updateProduct(p_id) {

    isediting = false;
    // Collect form data
    const pageNumber = $('#pageNumber').val();
    const category = $('#edit-category').val();
    const mainCategory = $("#edit-main-category").val();
    const subCategory = $('#edit-sub-category').val();
    const brandName = $('#edit_brand_name').val();
    const productName = $('#edit-product-name').val();
    const mrp = $('#edit-mrp').val();
    const sellingPrice = $('#edit-selling-price').val();
    const purchasePrice = $('#edit-purchase-price').val();
    const stock = $('#edit-stock').val();
    const quantity = $('#edit-quantity').val();
    const unit = $('#edit-unit').val();
    const review = $('#edit-review').val();
    const reviewNop = $('#edit-review-nop').val();
    const skuNumber = $('#edit-sku-number').val();
    const productLimit = $('#edit-product-limit').val();
    const edit_product_keyword = $('#edit_product_keyword').val();
    const file = $('#edit-product-image')[0].files[0];

    // Validate required fields

    console.log(category, subCategory, brandName, productName, mrp, sellingPrice, purchasePrice, stock, quantity, unit, review, reviewNop, skuNumber);

    if (!productName || !mrp || !sellingPrice || !category || !purchasePrice || !stock || !quantity || !unit || !review || !reviewNop) {
        warningAlert('Please fill out all required fields.');
        return;
    }

    let singleImageName = '';
    let imageExtension = '';

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            singleImageName = e.target.result;
            imageExtension = file.name.split('.').pop().toLowerCase();
            sendUpdateRequest();
        };
        reader.readAsDataURL(file);
    } else {
        sendUpdateRequest();
    }

    function sendUpdateRequest() {
        // Prepare FormData object
        const formData = new FormData();
        formData.append('type', 'updateProduct');
        formData.append('p_id', p_id);
        formData.append('category', category);
        formData.append('mainCategory', mainCategory);
        formData.append('subCategory', subCategory);
        formData.append('brandName', brandName);
        formData.append('productName', productName);
        formData.append('mrp', mrp);
        formData.append('sellingPrice', sellingPrice);
        formData.append('purchasePrice', purchasePrice);
        formData.append('stock', stock);
        formData.append('quantity', quantity);
        formData.append('unit', unit);
        formData.append('review', review);
        formData.append('reviewNop', reviewNop);
        formData.append('skuNumber', skuNumber);
        formData.append('productLimit', productLimit);
        formData.append('edit_product_keyword', edit_product_keyword);

        if (singleImageName) {
            formData.append('base64Image', singleImageName);
            formData.append('fileExtension', imageExtension);
        }

        $.ajax({
            url: apiurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response !== 'error') {
                    successAlert('Successfully updated');
                    $(".wrapper-overlay").removeClass('active');
                    $(".model").removeClass('active');
                    $('body').css('overflow', 'auto');
                    loadProduct(pageNumber, pageSize);
                } else {
                    errorAlert('Error updating product');
                }
            },
            error: function (xhr, status, error) {
                errorAlert('Error submitting data: ' + error);
            },
        });
    }
}





const renderList = (listId, data) => {
    const container = document.getElementById(listId);
    container.innerHTML = "";
    data.forEach((item, index) => {
        const row = document.createElement("div");
        row.className = "item-row";
        row.innerHTML = `
      <input type="text" value="${item.htitle || item.ititle}" data-index="${index}">
      <input type="text" value="${item.hdescription || item.idescription}" data-index="${index}">
      <button data-index="${index}">X</button>
    `;
        // delete
        row.querySelector("button").addEventListener("click", () => {
            data.splice(index, 1);
            renderList(listId, data);
        });
        container.appendChild(row);
    });
};

let edit_highlight = [];
let edit_information = [];
let edit_product_id = 0;

const editDescription = (item) => {

    edit_product_id = 0;
    edit_product_id = item.p_id;

    $(".description-model").addClass('active');
    $(".model").removeClass('active');


    edit_highlight = JSON.parse(item.highlight.replace(/\\/g, '').replace(/'/g, '`').slice(1, -1));
    edit_information = JSON.parse(item.information.replace(/\\/g, '').replace(/'/g, '`').slice(1, -1));

    renderList("highlight-list", edit_highlight);
    renderList("information-list", edit_information);

    // Add buttons
    document.getElementById("add-highlight").onclick = () => {
        edit_highlight.push({ htitle: "", hdescription: "" });
        renderList("highlight-list", edit_highlight);
    };

    document.getElementById("add-information").onclick = () => {
        edit_information.push({ ititle: "", idescription: "" });
        renderList("information-list", edit_information);
    };

    // Final submit
    document.getElementById("final-submit").onclick = () => {
        // get updated values
        let finalHighlight = [];
        document.querySelectorAll("#highlight-list .item-row").forEach(row => {
            const inputs = row.querySelectorAll("input");
            finalHighlight.push({ htitle: inputs[0].value, hdescription: inputs[1].value });
        });

        let finalInformation = [];
        document.querySelectorAll("#information-list .item-row").forEach(row => {
            const inputs = row.querySelectorAll("input");
            finalInformation.push({ ititle: inputs[0].value, idescription: inputs[1].value });
        });

        console.log("✅ Final Highlights:", finalHighlight);
        console.log("✅ Final Information:", finalInformation);
        console.log("product id:", edit_product_id);

        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'updateProductDescription', p_id: edit_product_id, highlight: JSON.stringify(finalHighlight), information: JSON.stringify(finalInformation) },
            success: function (response) {
                if (response != 'error') {
                    successAlert('Successfully updated');
                    loadProduct();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })

        $(".wrapper-overlay").removeClass('active');
        $(".description-model").removeClass('active');
        $('body').css('overflow', 'auto');
    };
};






const filterProduct = () => {
    // 🔹 Get selected filter values
    const selectedCategory = $("#selected_category").val();
    const selectedSubCategory = $("#selected_sub_category").val();
    const selectedStock = $("#selected_stock").val();
    const select_title = $("#select_title").val().toLowerCase();
    const select_best_title = $("#select_best_title").val().toLowerCase();
    const select_find_title = $("#select_find_title").val().toLowerCase();

    // 🔹 Apply filters on productData
    filteredData = productData.filter(item => {
        let categoryMatch = selectedCategory === "all" || item.under_category == selectedCategory;
        let subCategoryMatch = selectedSubCategory === "all" || item.under_subcategory
            == selectedSubCategory;

        return categoryMatch && subCategoryMatch;
    });

    // 🔹 Stock Sorting / Filtering
    if (selectedStock === "ltoh") {
        filteredData.sort((a, b) => parseInt(a.stock) - parseInt(b.stock)); // Low → High
    } else if (selectedStock === "htol") {
        filteredData.sort((a, b) => parseInt(b.stock) - parseInt(a.stock)); // High → Low
    } else if (selectedStock === "oos") {
        filteredData = filteredData.filter(item => parseInt(item.stock) <= 0); // Only Out of Stock
    }

    if (select_title != "all") {
        title = "title" + select_title;
        console.log(title);
        filteredData = filteredData.filter(item => item[title] == 'true');

    }

    // 🔹 Finally render the filtered result
    renderProduct(pageNumber, pageSize);
};






const downloadExcel = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'downloadStockCSV' },
        xhrFields: {
            responseType: 'blob'
        },
        success: function (response) {

            let blob = new Blob([response], {
                type: 'application/vnd.ms-excel'
            });

            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "bulk_stock.html";
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    });
};



document.getElementById("uploadForm").onsubmit = function (e) {
    e.preventDefault();

    let fd = new FormData(this);
    fd.append("type", "uploadStockCSV");

    fetch(apiurl, {
        method: "POST",
        body: fd
    })
        .then(res => res.text())
        .then(data => {
            successAlert(data);
            this.reset();
        });
}


function openBulkStockModal() {
    document.getElementById("bulk_stock_modal").style.display = "flex";
}

function closeBulkStockModal() {
    document.getElementById("bulk_stock_modal").style.display = "none";
}

// Close modal when clicking outside
window.onclick = function (e) {
    let modal = document.getElementById("bulk_stock_modal");
    if (e.target === modal) {
        closeBulkStockModal();
    }
};



$(document).on("change", "#rowsPerPage", function () {
    currentPageSize = parseInt($(this).val());// reset to first page
    pageSize = currentPageSize;
    renderProduct(pageNumber, currentPageSize);
});



// product scanner 


let scanBuffer = "";
let scanTimer = null;

document.addEventListener("keydown", function (e) {

    // Ignore normal typing in inputs EXCEPT sku fields
    const allowedInputs = ["sku-number", "edit-sku-number"];

    if (
        e.target.tagName === "INPUT" &&
        !allowedInputs.includes(e.target.id)
    ) {
        return;
    }

    // ENTER = scan complete
    if (e.key === "Enter") {
        e.preventDefault();

        if (scanBuffer.length >= 3) {
            fillSkuFromScan(scanBuffer);
        }

        scanBuffer = "";
        return;
    }

    // Collect characters (scanner speed detection)
    if (e.key.length === 1) {
        scanBuffer += e.key;

        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => {
            scanBuffer = "";
        }, 100);
    }
});


function fillSkuFromScan(barcode) {
    barcode = barcode.trim();

    console.log("📦 Barcode Scanned:", barcode);

    const exists = productData.find(p => p.sku_number === barcode);

    // 🔁 SKU already exists → filter product table
    if (exists) {

        warningAlert("SKU already exists! Product highlighted.");

        // 🔍 Search input me SKU daal do
        if (document.getElementById("search-input")) {
            $("#search-input").val(barcode);
        }

        // 🔄 Filter product table
        filteredData = productData.filter(product =>
            product.sku_number === barcode
        );

        // ⏮ Page reset
        pageNumber = 1;

        // 📋 Re-render table
        renderProduct(pageNumber, pageSize);

        // ✨ Optional: highlight row
        setTimeout(() => {
            $(`#row-${exists.p_id}`).addClass("highlight-row");
        }, 200);

        return;
    }

    // ➕ ADD PRODUCT PAGE
    if (!isediting) {
        if (document.getElementById("sku-number")) {
            $("#sku-number").val(barcode).focus();
            successAlert("Barcode scanned & added");
            return;
        }
    }
    // ✏️ EDIT PRODUCT PAGE
    else {
        if (document.getElementById("edit-sku-number")) {
            $("#edit-sku-number").val(barcode).focus();
            successAlert("Barcode scanned & updated");
            return;
        }
    }

    console.warn("SKU field not found");
}



