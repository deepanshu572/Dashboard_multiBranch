
let products = []; let subcategories = [];

const loadProduct = async () => {
    let branchId = localStorage.getItem("role_id")|| 0;
    
const productData =
    branchId > 0
        ? {
            type: "loadPosBranchproduct",
            branchId
        }
        : {
            type: "loadPosProduct"
        };

    await $.ajax({
        url: apiurl,
        type: 'POST',
        data: productData,
        dataType:"JSON",
        success: function (response) {
            
            if (response.status == "success") {
                products = response.data;

                console.log(products,response.data);

                //  renderProducts();
                filterProducts();
            }
        }
    })
}


const loadSubCategoryList = async () => {

    await $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadSubCategory' },
        success: function (response) {
            if (response != null && response != 'error') {
                let data = JSON.parse(response);
                subcategories = data;
                const subcategoryFilter = document.getElementById("subcategory-filter");
                subcategoryFilter.innerHTML = '<option value="all">All Subcategories</option>';
                data.forEach(subcategory => {
                    subcategoryFilter.innerHTML += `<option value="${subcategory.under_category}">${subcategory.name}</option>`;
                });
            } else {

            }
        }
    })
}

const itemsPerPage = 10;
let currentPage = 1;
let filteredProducts = [...products];

function renderProducts() {
    console.log(products,filteredProducts);
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const visibleProducts = filteredProducts.slice(start, end);

    const productContainer = document.getElementById("product-list");
    productContainer.innerHTML = "";

    const cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    visibleProducts.forEach(product => {
        let items = JSON.stringify(product).replace(/'/g, '`');
        
        // Check if item is in cart (assuming vid 0 for basic products)
        const inCart = cartData.find(p => p.p_id == product.p_id && p.v_id == 0);
        const quantityInCart = inCart ? inCart.nop : 1;
        
        const addButtonStyle = inCart ? 'display:none;' : 'display:block;';
        const sectionStyle = inCart ? 'display:flex;' : 'display:none;';

        let btnSection = `
        <button class="add-to-cart add-to-cart${product.p_id}" style="${addButtonStyle}" onclick='addToCart(${items})'><i class="bi bi-cart4"></i> Add </button>
                <div class="btn-section btn-section${product.p_id}" style="${sectionStyle}">
                    <i class="bi bi-dash-lg" onclick='cartDec(${items})'></i>
                    <p class="nop${product.p_id}">${quantityInCart} </p>
                    <i class="bi bi-plus-lg" onclick='cartInc(${items})'></i>
                 </div>
        `;

        if (product.isvarient == 'true') {
            btnSection = `
            <button class="add-to-cart add-to-cart${product.p_id}" onclick='addToCartHasOption(${items})'><i class="bi bi-cart4"></i> Option </button>
            `;
        }


        productContainer.innerHTML += `
            <div class="product">
                <img src="${imgurl + product.image_path}" alt="${product.name}">
                <h4>${product.name}</h4>
                <p style="margin-bottom:10px;">${product.quantity} ${product.unit}</p>
                <p>Rs.${product.selling_price} /-</p>
                ${btnSection}
            </div>
        `;
    });
}

function renderPagination() {
    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
    const paginationContainer = document.getElementById("pagination");
    paginationContainer.innerHTML = "";

    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (currentPage > 1) {
        paginationContainer.innerHTML += `
            <button class="nav-button" onclick="goToPage(currentPage - 1)">Previous</button>
        `;
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationContainer.innerHTML += `
            <button class="${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>
        `;
    }

    if (currentPage < totalPages) {
        paginationContainer.innerHTML += `
            <button class="nav-button" onclick="goToPage(currentPage + 1)">Next</button>
        `;
    }
}

function goToPage(page) {
    if (page < 1 || page > Math.ceil(filteredProducts.length / itemsPerPage)) return;
    currentPage = page;
    renderProducts();
    renderPagination();
}



function filterProducts() {
    const category = document.getElementById("category-filter").value;
    const subcategory = document.getElementById("subcategory-filter").value;
    const searchValue = document.getElementById("search-box").value.toLowerCase();

    filteredProducts = products.filter(product => {
        return (
            (category === "all" || product.under_category === category) &&
            (subcategory === "all" || product.under_subcategory === subcategory) &&
            (product.name.toLowerCase().includes(searchValue) || product.sku_number.toLowerCase().includes(searchValue))
        );
    });

    currentPage = 1;
    renderProducts();
    renderPagination();
}

document.getElementById("category-filter").addEventListener("change", (e) => {
    const categoryId = e.target.value;

    renderSubcategories(categoryId);
    // filterProducts()

    filterProducts();
});

document.getElementById("subcategory-filter").addEventListener("change", filterProducts);
document.getElementById("search-box").addEventListener("input", filterProducts);



const loadCategoryList = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadCategory' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                let categoryList = ' <option value="all">All Categories</option>';
                data.forEach((item, index) => {
                    categoryList += `
                        <option value="${item.id}">${item.name}</option>
                        `;
                })
                $("#category-filter").html(categoryList);
            }
        }
    })

}


function renderSubcategories(categoryId) {

    const subcategoryFilter = document.getElementById("subcategory-filter");
    subcategoryFilter.innerHTML = '<option value="all">All Subcategories</option>';

    const filteredSubcategories = subcategories.filter(
        subcategory => categoryId === "all" || subcategory.under_category === categoryId
    );

    filteredSubcategories.forEach(subcategory => {
        subcategoryFilter.innerHTML += `<option value="${subcategory.id}">${subcategory.name}</option>`;
    });
}


// load all user 

const loadAllUserList = () => {
    let branchId = localStorage.getItem("role_id");
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadAllPosUser',branchId },
        success: function (response) {
            if (response != 'error' && response != null) {

                let data = JSON.parse(response);

                let userlist = ' <option value="0">Walk In Customer</option>';

                data.map((item, index) => {

                    userlist += `
                            <option value="${item.mobile_number}">${item.mobile_number} (${item.username})</option>
                                `;
                })
                $("#customer-select").html(userlist);
            }
        }
    })
}



const addNewCustomer = () => {

    $(".wrapper-overlay").addClass('active');
    $(".modal-container").addClass('active');
    $('body').css('overflow', 'hidden');
}

$(".close-btn").click(() => {
    $(".wrapper-overlay").removeClass('active');
    $(".modal-container").removeClass('active');
    $('body').css('overflow', 'auto');
})

$(".edit-btn").click(() => {
    $(".wrapper-overlay").addClass('active');
    $(".d-modal").addClass('active');
    $('body').css('overflow', 'hidden');
})

$(".cancel-discount-modal").click(() => {
    $(".wrapper-overlay").removeClass('active');
    $(".d-modal").removeClass('active');
    $('body').css('overflow', 'auto');
})




function addToCart(item) {
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);

    let p_id = item.p_id;
    const product = products.filter(p => p.p_id == p_id)[0]; // Get the product details
    console.log(item);

    // Retrieve cartData from localStorage
    let nop = $(`.nop${p_id}`).html();
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    // Check if the product already exists in the cart
    const existingProduct = cartData.find(p => p.p_id === p_id);

    if (existingProduct) {
        // If it exists, increase the quantity
        existingProduct.nop++;

        if (item.stock < existingProduct.nop) {
            errorAlert('Out of stock');
            return;
        }

        $(`.nop${p_id}`).html(existingProduct.nop);
    } else {
        // If it doesn't exist, add a new product object
        cartData.push({
            v_id: 0,
            p_id: product.p_id,
            name: product.name,
            image_path: product.image_path,
            selling_price: product.selling_price,
            purchase_price: product.purchase_price,
            mrp: product.mrp,
            unit: product.unit,
            qty: product.quantity,
            nop: 1 // Initialize quantity
        });
        $(`.nop${p_id}`).html('1');

        if (item.stock < 2) {
            errorAlert('Out of stock');
            return;
        }
    }

    // Save the updated cartData back to localStorage
    localStorage.setItem('cartData', JSON.stringify(cartData));

    $(`.add-to-cart${p_id}`).hide();
    $(`.btn-section${p_id}`).css('display', 'flex');


    renderCartProduct();

}

const cartInc = (item) => {
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);
    let p_id = item.p_id;

    let vid = item.vid || 0;

    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    const existingProduct = cartData.find(p => p.p_id == p_id && p.v_id == vid);

    existingProduct.nop++;

    if (item.stock < existingProduct.nop) {
        errorAlert('Out of stock');
        return;
    }

    $(`.nop${p_id}`).html(existingProduct.nop);

    localStorage.setItem('cartData', JSON.stringify(cartData));

    renderCartProduct();
}

const cartDec = (item) => {
    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);
    let p_id = item.p_id;
    let vid = item.vid || 0;
    let nop = $(`.nop${p_id}`).html();
    nop = parseInt(nop);
    nop--;
    // $(`.nop${p_id}`).html(nop);

    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    const existingProduct = cartData.find(p => p.p_id == p_id && p.v_id == vid);

    if (nop <= 0) {

        cartData = cartData.filter(
            p => !(p.p_id == p_id && p.v_id == vid)
        );
        $(`.add-to-cart${p_id}`).show();
        $(`.btn-section${p_id}`).hide();
        $(`.nop${p_id}`).html('1');

    } else {

        existingProduct.nop--;

        $(`.nop${p_id}`).html(existingProduct.nop);

    }

    localStorage.setItem('cartData', JSON.stringify(cartData));

    renderCartProduct();
}

const deleteCartData = (p_id, v_id) => {
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    // const existingProduct = cartData.find(p => p.p_id === p_id);
    cartData = cartData.filter(
        p => !(p.p_id == p_id && p.v_id == v_id)
    );

    // console.log(cartData ,cartDatatemp);
    // exit;

    $(`.add-to-cart${p_id}`).show();
    $(`.btn-section${p_id}`).hide();
    $(`.nop${p_id}`).html('1');

    localStorage.setItem('cartData', JSON.stringify(cartData));

    renderCartProduct();
}




function updateCartQuantity(p_id, newQuantity, v_id) {
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
    const existingProduct = cartData.find(p => p.p_id == p_id && p.v_id == v_id);

    if (existingProduct) {
        newQuantity = parseInt(newQuantity);
        if (newQuantity <= 0) {
            // Remove product if quantity is 0 or less
            cartData = cartData.filter(p => p.p_id != p_id);
            $(`.add-to-cart${p_id}`).show();
            $(`.btn-section${p_id}`).hide();
            $(`.nop${p_id}vid${v_id}`).html('1');
        } else {
            // Update quantity
            existingProduct.nop = newQuantity;
        }
        localStorage.setItem('cartData', JSON.stringify(cartData));
        renderCartProduct();

        $(`.quantity${p_id}vid${v_id}`).focus();
    }
}

const toNumber = (value) => {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : 0;
};

const getCartTotals = (cartData, discountValue, isValueDiscountType, deliveryCharge) => {
    let totalSellingPrice = 0;
    let totalMRP = 0;

    cartData.forEach((item) => {
        const qty = toNumber(item.nop);
        totalSellingPrice += toNumber(item.selling_price) * qty;
        totalMRP += toNumber(item.mrp) * qty;
    });

    const productDiscount = Math.max(0, totalMRP - totalSellingPrice);

    let extraDiscountAmount = 0;
    if (isValueDiscountType) {
        extraDiscountAmount = discountValue;
    } else {
        extraDiscountAmount = totalSellingPrice * (discountValue / 100);
    }

    extraDiscountAmount = Math.min(extraDiscountAmount, totalSellingPrice);
    const subtotal = Math.max(0, totalSellingPrice - extraDiscountAmount);
    const grandTotal = subtotal + deliveryCharge;

    return {
        totalSellingPrice,
        totalMRP,
        productDiscount,
        extraDiscountAmount,
        subtotal,
        grandTotal
    };
};


const renderCartProduct = () => {
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
    let cartItems = '';

    const delCharge = toNumber($(".delCharge").html());
    const discountPrice = toNumber($(".discountPrice").html());
    cartData.map((item, index) => {
        const lineTotal = toNumber(item.selling_price) * toNumber(item.nop);

        cartItems += `
                 <tr>
                        <td class="item">
                            <img src="${imgurl + item.image_path}" alt="Cabbage">
                            <span>${item.name}</span>
                        </td>
                        <td>
                           <input type="number" value="${item.nop}" class="quantity quantity${item.p_id}vid${item.v_id}" min="1" onchange="updateCartQuantity(${item.p_id}, this.value , ${item.v_id})">
                        </td>
                        <td class="price">₹${lineTotal.toFixed(2)}</td>
                        <td>
                            <button class="delete-btn flex" onclick="deleteCartData(${item.p_id} , ${item.v_id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                 </tr>
            `
    });
    $(".cartGrid").html(cartItems);
    const totals = getCartTotals(
        cartData,
        discountPrice,
        $(".valueDiscountType").is(':visible'),
        delCharge
    );

    $(".cartSubTotal").html(`₹ ${totals.totalSellingPrice.toFixed(2)}`);
    $(".cartProductDiscount").html(`₹ ${totals.productDiscount.toFixed(2)}`);
    $(".totalCatAmount").html(`₹ ${totals.grandTotal.toFixed(2)}`);
}

const setDiscount = () => {

    // e.preventDefault(); 
    let discount = $("#discount").val();
    let type = $("#type").val();

    if (type == 'amount') {
        $(".valueDiscountType").show();
        $(".percentageDiscountType").hide();

    } else {
        $(".percentageDiscountType").html('%');
        $(".percentageDiscountType").show();
        $(".valueDiscountType").hide();
    }
    $(".discountPrice").html(discount)
    renderCartProduct();

    $(".wrapper-overlay").removeClass('active');
    $(".d-modal").removeClass('active');
    $('body').css('overflow', 'auto');

}


// place order 

const placeOrder = () => {

    let customerId = $("#customer-select").val();

    if (customerId == 0) {
        errorAlert('please select customer');
        return;
    }

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

    // console.log(customer); exit;

    $(".wrapper-overlay").addClass('active');
    $(".bill-modal").addClass('active');
    $('body').css('overflow', 'hidden');

    let currentDate = new Date();
    let formattedDate = currentDate.toLocaleString(); // Local date and time format
    $(".billOrderDate").html(formattedDate);

    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
    let cartItems = '';

    const delCharge = toNumber($(".delCharge").html());
    const discountPrice = toNumber($(".discountPrice").html());
    cartData.map((item, index) => {
        const qty = toNumber(item.nop);
        const unitMrp = toNumber(item.mrp);
        const unitSelling = toNumber(item.selling_price);
        const linePrice = unitSelling * qty;
        const lineDiscount = Math.max(0, (unitMrp - unitSelling) * qty);


        cartItems += `
                     <tr>
                        <td>${item.qty} ${item.unit}</td>
                        <td>${item.name}<br>Unit price: ₹${unitSelling.toFixed(2)}<br>Qty: ${qty}<br>Discount: ₹${lineDiscount.toFixed(2)}</td>
                        <td class="bprice">₹${linePrice.toFixed(2)}</td>
                    </tr>
                 
            `
    });
    $(".billResult").html(cartItems);
    const totals = getCartTotals(
        cartData,
        discountPrice,
        $(".valueDiscountType").is(':visible'),
        delCharge
    );

    $(".extraDiscount").html(`₹ ${totals.extraDiscountAmount.toFixed(2)}`);
    $(".totalSellingPrice").html(`₹ ${totals.totalSellingPrice.toFixed(2)}`);
    $(".billProductDiscount").html(`₹ ${totals.productDiscount.toFixed(2)}`);
    $(".billSubtotal").html(`₹ ${totals.subtotal.toFixed(2)}`);
    $(".billDelCharge").html(`₹ ${delCharge.toFixed(2)}`);
    $(".billGrandTotal").html(`₹ ${totals.grandTotal.toFixed(2)}`);


    setTimeout(() => {
        addProductInCart(customerId);
    }, 100);


}

const addProductInCart = async (customerId) => {
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
    console.log(customerId, cartData);

    let cartSubTotal = $(".cartSubTotal").html().replace('₹', '').trim();
    let totalCartSaving = $(".cartProductDiscount").html().replace('₹', '').trim();
    let discountPrice = $(".discountPrice").html().trim();
    let percentageDiscountType = $(".percentageDiscountType").is(':visible') ? 'true' : 'false';
    let delCharge = $(".delCharge").html().trim();
    let totalCatAmount = $(".totalCatAmount").html().replace('₹', '').trim();
    let orderType = $(".o1.active").closest(".ots").find("p").text().trim();
    let paymentMethode = $(".payment-btn.active").html().trim();
    let branchId = localStorage.getItem("role_id");

    console.log(customerId, cartData, cartSubTotal, totalCartSaving, discountPrice, percentageDiscountType, delCharge, totalCatAmount ,orderType ,paymentMethode);
    // exit;



    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'addToCart',branchId, customerId: customerId, cartData: JSON.stringify(cartData) , cartSubTotal: cartSubTotal, totalCartSaving: totalCartSaving, discountPrice: discountPrice, percentageDiscountType: percentageDiscountType, delCharge: delCharge, totalCatAmount: totalCatAmount ,orderType ,paymentMethode},
        success: function (response) {
            if (response != 'error' && response != null) {
                let data = JSON.parse(response);
                console.log(data);
                if(data.status == 'success'){
                    successAlert('Order placed successfully');
                    clearCart();
                }
            } else {
                errorAlert('Something went wrong');
            }
                    
        }
    })


}



const clearCart = () => {
    localStorage.removeItem('cartData');
    renderCartProduct();
    renderProducts();
    
    // Reset totals display to zero
    $(".discountPrice").html('0');
    $(".delCharge").html('0');
    $(".totalCatAmount").html('₹ 0.00');
    $(".cartSubTotal").html('₹ 0.00');
    $(".cartProductDiscount").html('₹ 0.00');
}

$(document).ready(function() {
    $(".cancel-order-btn").click(() => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will clear your current cart!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                clearCart();
                successAlert('Cart cleared successfully');
            }
        })
    });
});

const closeBillModel = () => {
    $(".wrapper-overlay").removeClass('active');
    $(".bill-modal").removeClass('active');
    $('body').css('overflow', 'auto');
}

const printBill = () => {
    // const billContent = document.getElementById("binvoice").innerHTML; // Get the content of the bill div
    // const newWindow = window.open('', '', 'height=500,width=800'); // Open a new window
    // newWindow.document.write('<html><head><title>Print Bill</title>');
    // newWindow.document.write('</head><body>');
    // newWindow.document.write(billContent); // Write the bill content to the new window
    // newWindow.document.write('</body></html>');
    // newWindow.document.close(); // Close the document
    // newWindow.print();
    window.print();
}



// add


$(".del-edit-btn").click(() => {
    $(".wrapper-overlay").addClass('active');
    $(".dd-modal").addClass('active');
    $('body').css('overflow', 'hidden');
})

$(".cancel-discount-modal").click(() => {
    $(".wrapper-overlay").removeClass('active');
    $(".dd-modal").removeClass('active');
    $('body').css('overflow', 'auto');
})



const resetForm = () => {
    $("#discount").val('');
    $("#type").val('amount');
    $("#setdelcharge").val('');
}

const setDeliveryCharge = () => {
    let setdelcharge = $("#setdelcharge").val();

    $(".delCharge").html(setdelcharge);
    $(".wrapper-overlay").removeClass('active');
    $(".dd-modal").removeClass('active');
    $('body').css('overflow', 'auto');

    renderCartProduct();
}




// add to cart option

const addToCartHasOption = async (items) => {
    items = JSON.stringify(items).replace(/`/g, "'");
    items = JSON.parse(items);
    let p_id = items.p_id;
    let name = items.name;
    let image_path = items.image_path;

    localStorage.setItem("varintData", JSON.stringify(items));

    await $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'getProductVarient', p_id: p_id },
        success: function (response) {
            if (response != 'error' && response != null) {
                let data = JSON.parse(response);
                let varientGrid = '';
                data.push(
                    {
                        product_id: p_id,
                        v_mrp: items.mrp,
                        v_purchase_price: items.purchase_price,
                        v_quantity: items.quantity,
                        v_seliing_price: items.selling_price,
                        v_stock: items.stock,
                        v_unit: items.unit,
                        vid: 0
                    }
                )
                data = data.sort((a, b) => a.v_seliing_price - b.v_seliing_price);
                data.map((item, index) => {
                    let items = JSON.stringify(item);
                    let percentage = ((item.v_mrp - item.v_seliing_price) / item.v_mrp) * 100;
                    percentage = percentage.toFixed(0);
                    let hideOffer = parseInt(item.v_mrp) <= parseInt(item.v_seliing_price) ? 'hideOffer' : '';

                    varientGrid += `
                      <div class="varient-card flex space-between gap-10">
                <div class="vc-img-box">
                    <img src="${imgurl + image_path}" loading="lazy" alt="img">
                    <div class="vc-offer ${hideOffer}">
                        <p>${percentage}% Off</p>
                    </div>
                </div>
                <div class="vc-info">
                    <p>${name}</p>
                    <div class="flex flex-start gap-5">
                        <h3>₹${item.v_seliing_price}</h3>
                        <span> <del>₹${item.v_mrp}</del></span>
                    </div>
                </div>
                <div class="vc-qty">
                    <h3>${item.v_quantity}${item.v_unit}</h3>
                </div>
                <div class="vc-btn vc-btn${item.vid}">
                    <button class="addtoCartVarient${item.vid}" onclick='addtoCartVarient(${items})'>Add to Cart</button>
                    <div class="vc-btn-section vc-btn-section${item.vid}">
                        <i class="bi bi-dash-lg" onclick='deleteVarient(${items})'></i>
                        <p class="vnop${item.vid}">1</p>
                        <i class="bi bi-plus-lg" onclick='addVarientInc(${items})'></i>
                    </div>
                </div>
            </div>
                    `;
                })
                $(".product-varient-grid").html(varientGrid);
            } else {
                $(".product-varient-grid").html('');
            }
            $(".wrapper-overlay").addClass('active');
            $(".product-popup").addClass('active');
            $('body').css('overflow', 'hidden');
        }
    })
}

$(".confirm-btn button").click(() => {
    $(".wrapper-overlay").removeClass('active');
    $(".product-popup").removeClass('active');
    $('body').css('overflow', 'auto');
})


function addtoCartVarient(item) {
    let p_id = item.product_id;
    let vid = item.vid;
    const product = products.filter(p => p.p_id == p_id)[0]; // Get the product details
    console.log(item);

    // Retrieve cartData from localStorage
    let nop = $(`.vnop${vid}`).html();
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    // Check if the product already exists in the cart
    const existingProduct = cartData.find(p => p.p_id === p_id && p.v_id === vid);

    if (existingProduct) {
        // If it exists, increase the quantity
        existingProduct.nop++;
        if (item.v_stock < existingProduct.nop) {
            errorAlert('Out of stock');
            return;
        }
        $(`.vnop${vid}`).html(existingProduct.nop);
    } else {
        // If it doesn't exist, add a new product object
        cartData.push({
            v_id: vid,
            p_id: product.p_id,
            name: product.name,
            image_path: product.image_path,
            selling_price: item.v_seliing_price,
            purchase_price: item.v_purchase_price,
            mrp: item.v_mrp,
            unit: item.v_unit,
            qty: item.v_quantity,
            nop: 1 // Initialize quantity
        });
        $(`.vnop${vid}`).html('1');

        if (item.v_stock < 2) {
            errorAlert('Out of stock');
            return;
        }
    }

    // Save the updated cartData back to localStorage
    localStorage.setItem('cartData', JSON.stringify(cartData));

    $(`.addtoCartVarient${vid}`).hide();
    $(`.vc-btn-section${vid}`).css("display", "flex");


    renderCartProduct();

}


const addVarientInc = (item) => {

    let p_id = item.product_id;

    let vid = item.vid || 0;

    // console.log(item ,vid);
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    const existingProduct = cartData.find(p => p.p_id == p_id && p.v_id == vid);

    console.log(existingProduct);
    existingProduct;
    existingProduct.nop++;

    if (item.v_stock < existingProduct.nop) {
        errorAlert('Out of stock');
        return;
    }
    $(`.vnop${vid}`).html(existingProduct.nop);

    localStorage.setItem('cartData', JSON.stringify(cartData));

    renderCartProduct();

}

const deleteVarient = (item) => {


    let p_id = item.product_id;
    let vid = item.vid || 0;
    let nop = $(`.vnop${vid}`).html();
    nop = parseInt(nop);
    nop--;
    // $(`.nop${p_id}`).html(nop);

    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];

    const existingProduct = cartData.find(p => p.p_id == p_id && p.v_id == vid);

    if (nop <= 0) {

        cartData = cartData.filter(
            p => !(p.p_id == p_id && p.v_id == vid)
        );
        $(`.addtoCartVarient${vid}`).show();
        $(`.vc-btn-section${vid}`).hide();

        $(`.vnop${vid}`).html('1');

    } else {

        existingProduct.nop--;
        $(`.vnop${vid}`).html(existingProduct.nop);

    }

    localStorage.setItem('cartData', JSON.stringify(cartData));

    renderCartProduct();

}





//  new line added 

document.querySelector('.customer-form').addEventListener('submit', function (e) {
    e.preventDefault(); // Form ko reload hone se rokta hai

    // 🔍 Input values
    const full_name = document.getElementById('full_name').value.trim();
 
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();

   

    // const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phonePattern = /^[0-9]{10}$/; // Adjust as needed

    // if (!emailPattern.test(email)) {
    //     errorAlert('Please enter a valid email address.');
    //     return;
    // }

    if (!phonePattern.test(phone)) {
        errorAlert('Please enter a valid 10-digit phone number.');
        return;
    }

    let branch_id = localStorage.getItem("role_id");


    $.ajax({
        url:apiurl,
        type:'POST',
        data:{type:'addNewCustomer',full_name:full_name  ,email:email ,phone:phone,role_id:branch_id},
        success:function(response){
            if(response=='success'){
                successAlert('successfully added');

                $("#full_name").val('')
                $("#email").val('')
                $("#phone").val('')

                loadAllUserList();
            }else{
                errorAlert('Something went wrong');
            }
        }
    })

  
});




// pos scanner added


let barcodeBuffer = "";
let barcodeTimer = null;

document.addEventListener("keydown", function (e) {

    // Ignore if user manually typing in input/textarea
    if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
        if (e.target.id !== "barcode-input") return;
    }

    // ENTER = barcode complete
    if (e.key === "Enter") {
        e.preventDefault();

        if (barcodeBuffer.length > 2) {
            handleBarcodeScan(barcodeBuffer);
        }

        barcodeBuffer = "";
        return;
    }

    // Scanner sends very fast keystrokes
    if (e.key.length === 1) {
        barcodeBuffer += e.key;

        clearTimeout(barcodeTimer);
        barcodeTimer = setTimeout(() => {
            barcodeBuffer = "";
        }, 100);
    }
});


function handleBarcodeScan(barcode) {

    barcode = barcode.trim();
    console.log("Scanned:", barcode);

    // 🔍 Product search by SKU / barcode
    const product = products.find(p =>
        p.sku_number == barcode || p.barcode == barcode
    );

    if (!product) {
        errorAlert("Product not found");
        return;
    }

    // Variant product
    if (product.isvarient === 'true') {
        addToCartHasOption(product);
    } else {
        addToCart(product);
    }
}

// Set interval removed to allow other inputs to work

