$(document).ready(function () {

    // Image upload and preview
    $('#sub-category-image').change(function () {
        const file = this.files[0];
        if (file) {
            if (file.size > 100 * 1024) { // Validate file size (100KB max)
                alert('Image size should not exceed 100 KB');
                $('#sub-category-image').val('');
                $('#preview-img').attr('src', 'https://placehold.co/500x500');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Reset form
    $('#reset-button').click(function () {
        $('#sub-category-name').val('');
        $('#sub-category-image').val('');
        $('#preview-img').attr('src', 'https://placehold.co/500x500');
    });

    // Submit form
    $('#sub-category-form').submit(function (e) {
        e.preventDefault();
        const subCategoryName = $('#sub-category-name').val();
        const underCategory = $('#category').val();
        const middleCategory = $('#middleCategoryOption').val();
        const file = $('#sub-category-image')[0].files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const base64Image = e.target.result;
                const fileExtension = file.name.split('.').pop().toLowerCase();
                $.ajax({
                    url: apiurl,
                    type: 'POST',
                    data: {
                        type: 'addSubCategory',
                        underCategory: underCategory,
                        middleCategory: middleCategory,
                        subCategoryName: subCategoryName,
                        subCategoryImage: base64Image,
                        imageExtension: fileExtension,
                    },
                    success: function (response) {
                        if (response == 'success') {
                            successAlert('Successfully added');
                            loadSubCategory();
                        } else {
                            errorAlert('Error');
                        }

                    },
                    error: function () {
                        alert('Error submitting data.');
                    },
                });
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please upload an image.');
        }
    });


});


let categoryListData = []
const loadCategoryList = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadCategory' },
        success: function (response) {
            let categoryList = `<option selected disable hidden value="">Select Category</option>`;
            let categoryList2 = `<option value="all">Select Category</option>`;
            if (response != null || response != 'error') {
                let data = JSON.parse(response);
                categoryListData = data;
                data.forEach((category, index) => {
                    categoryList += `
                    <option value="${category.id}">${category.name}</option>
                    `;
                    categoryList2 += `
                    <option value="${category.id}">${category.name}</option>
                    `;
                })
                $("#category").html(categoryList);
                $("#selected_category").html(categoryList2);
            }
        }
    })
}

let subCategoryData = [];
const loadSubCategory = () => {


    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadSubCategory' },
        success: function (response) {
            if (response != null && response != 'error') {
                let data = JSON.parse(response);

                subCategoryData = data;

                renderSubCategory(data);


            } else {
                $("#result").html("no data found");
            }
        }
    })
}


const renderSubCategory = (data) => {


    const selectedCategory = $("#selected_category").val();
    const selectedStock = $("#selected_stock").val();
    const searchText = $("#search-input").val().toLowerCase();
    const titleText = $("#selected_title").val().toLowerCase();

    data = data.filter(item => {
        let categoryMatch = selectedCategory === "all" || item.under_category == selectedCategory;
        let nameMatch = item.name.toLowerCase().includes(searchText);
        return categoryMatch && nameMatch;
    });

    // 🔸 Sort / Filter by Product Count
    if (selectedStock === "ltoh") {
        data.sort((a, b) => parseInt(a.product_count) - parseInt(b.product_count));
    } else if (selectedStock === "htol") {
        data.sort((a, b) => parseInt(b.product_count) - parseInt(a.product_count));
    } else if (selectedStock === "oos") {
        data = data.filter(item => parseInt(item.product_count) === 0);
    }

    if (titleText != "all") {
        title = "title" + titleText;
        data = data.filter(item => item[title] == 'true');
    }


    $("#totalData").html(data.length);
    let html = `
    <thead>
            <tr>
                <th>SN</th>
                <th>Image</th>
                <th>Main <br> Category</th>
                <th>Middle <br> Category</th>
                <th>Name</th>
                <th>Product Count</th>
                <th>Status</th>
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
            <td><img src="${imgurl + item.image_path}" alt="${item.name}"></td>
            <td>${item.category_name}</td>
                        <td>${item.middle_category_name}</td>

            <td>${item.name}</td>
            <td>${item.product_count}</td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
                    onclick="handleCheckboxChange(this,'status','${item.id}')">
                    <span class="slider"></span>
                </label>
            </td>
         
           
            <td>
                <button class="edit flex" onclick='editSubCategory(${items})'><i class="bi bi-pencil"></i></button>
            </td>
        </tr>
        `;
    });
    html += `</tbody>`;
    $("#result").html(html);
}

function handleCheckboxChange(checkbox, typeStatus, id) {
    const isChecked = checkbox.checked;
    console.log('type : ', typeStatus, id)
    Swal.fire({
        title: 'Are you sure?',
        text: isChecked
            ? "You want to activate this category"
            : "You want to deactivate this category",
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
                data: { 'type': 'updateSubCategory', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
                success: function (response) {
                    if (response != 'error') {
                        Swal.fire({
                            title: 'Success!',
                            text: isChecked
                                ? `Subcategory  activated successfully`
                                : `Subcategory  deactivated successfully`,
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

function handleMainCatgeory(e) {
    e.preventDefault();
    $("#mainCategory").val(e.target.value);
    getmiddleCategory1(e?.target?.value);
}
function handleChangeMainCatgeory(e) {
        e.preventDefault();
        console.log("e.target.value");
        console.log(e.target.value);
        console.log("e.target.value");
    $("#middle-category-select").val(e.target.value);
    getEditmiddleCategory2(e?.target?.value);
}

let middleCategory = [];
function getmiddleCategory1(categoryId) {
   return $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getmiddleCategory",
            categoryId
        },
        success: function (response) {
            let html = '<option value="0">Select Middle category</option>';
            if (response.status == "success") {
                $("#middleCat").css("display", "block");
                let data = response.data;
                middleCategory = data;
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#middleCategoryOption").html(html);
            } else {
                console.log(response.message);
                $("#middleCat").css("display", "none");

                $("#middleCategoryOption").html(html);

            }
        }
    });

}
function getEditmiddleCategory2(categoryId) {
   return $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getmiddleCategory",
            categoryId
        },
        success: function (response) {
            let html = '<option value="0">Select Middle category</option>';
            if (response.status == "success") {
                $("#middleCat2").css("display", "block");
                let data = response.data;
                middleCategory = data;
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#middle-category-select").html(html);
            } else {
                console.log(response.message);
                $("#middleCat2").css("display", "none");

                $("#middle-category-select").html(html);

            }
        }
    });

}

function handleEditMainCatgeory(categoryId){
    $.ajax({
        url: apiurl,
        method: "POST",
        dataType: "JSON",
        data: {
            type: "getmiddleCategory",
            categoryId
        },
        success: function (response) {
            let html = '<option value="0">Select Middle category</option>';
            if (response.status == "success") {
                // $("#middleCat").css("display", "block");
                let data = response.data;
                middleCategory = data;
                data.forEach((item) => {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                $("#middle-category-select").html(html);
            } else {
                console.log(response.message);
                // $("#middleCat").css("display", "none");

                $("#middle-category-select").html(html);

            }
        }
    });

}





const editSubCategory = async (item) => {

    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);

    $("#subcat_id").val(item.id)
    document.getElementById('categoryName').value = item.name;
    document.getElementById('imagePreview').src = imgurl + item.image_path;
    $(".edit-modal").addClass('active');
    $(".wrapper-overlay").addClass('active');
    $('body').css("overflow", 'hidden');

     await getmiddleCategory1(item.under_category);

    let categoryList1 = `<option selected disable hidden value="">Select Category</option>`;

    categoryListData.forEach((category, index) => {
        // Check if the category.id matches under_category
        const isSelected = (category.id === item.under_category) ? 'selected' : '';
        categoryList1 += `
        <option value="${category.id}" ${isSelected}>${category.name}</option>
        `;
    });
    $("#category-select").html(categoryList1);



      let categoryList2 = `<option selected disable hidden value="">Select Category</option>`;

    middleCategory.forEach((category, index) => {
        // Check if the category.id matches under_category
        const isSelected = (category.id === item.middle_category) ? 'selected' : '';
        categoryList2 += `
        <option value="${category.id}" ${isSelected}>${category.name}</option>
        `;
    });
    $("#middle-category-select").html(categoryList2);


    console.log(middleCategory);





    


}


function closeModal() {
    $(".edit-modal").removeClass('active');
    $(".wrapper-overlay").removeClass('active');
    $('body').css("overflow", 'auto');
}

function previewImage(event) {
    const file = event.target.files[0];
    if (file && file.size > 100000) { // 100KB limit
        document.getElementById('imageError').style.display = 'block';
        event.target.value = "";
        return;
    } else {
        document.getElementById('imageError').style.display = 'none';
    }

    const reader = new FileReader();
    reader.onload = function () {
        document.getElementById('imagePreview').src = reader.result;
    };
    reader.readAsDataURL(file);
}

const saveChanges = () => {
    const cat_id = $("#subcat_id").val();
    const under_category = $("#category-select").val();
    const middle_category = $("#middle-category-select").val();
    const name = document.getElementById('categoryName').value;
    // const imageSrc = document.getElementById('imageUpload');
    const file = $('#imageUpload')[0].files[0];
    // console.log('Updated Category:', name, imageSrc);

    if (name == '') {
        warningAlert('Enter valid name');
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
                data: {
                    type: 'editSubCategoryWithImage', cat_id: cat_id,
                    categoryName: name, categoryImage: base64Image,
                    imageExtension: fileExtension, under_category: under_category,
                    middle_category
                },
                success: function (response) {
                    if (response == 'success') {
                        successAlert('Successfully updated');
                        loadSubCategory();
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
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: {
                type: 'editSubCategoryName', cat_id: cat_id,
                categoryName: name,
                under_category: under_category,
                middle_category
            },
            success: function (response) {
                if (response == 'success') {
                    successAlert('Successfully updated');
                    loadSubCategory();
                } else {
                    errorAlert('something went wrong');
                }
            },
            error: function () {
                alert('Error submitting data.');
            },
        });
    }

    closeModal();
}



const searchSubCategory = () => {
    let searchText = $("#search-input").val().toLowerCase();
    filteredData = subCategoryData.filter(item =>
        item.name.toLowerCase().includes(searchText)
    );
    renderSubCategory(filteredData);
};


$(document).on("keyup", "#search-input", function () {
    searchSubCategory();
});



const filterSubcategory = () => {
    const selectedCategory = $("#selected_category").val();
    const selectedStock = $("#selected_stock").val();
    const searchText = $("#search-input").val().toLowerCase();
    const titleText = $("#selected_title").val().toLowerCase();

    filteredSubCategory = subCategoryData.filter(item => {
        let categoryMatch = selectedCategory === "all" || item.under_category == selectedCategory;
        let nameMatch = item.name.toLowerCase().includes(searchText);
        return categoryMatch && nameMatch;
    });

    // 🔸 Sort / Filter by Product Count
    if (selectedStock === "ltoh") {
        filteredSubCategory.sort((a, b) => parseInt(a.product_count) - parseInt(b.product_count));
    } else if (selectedStock === "htol") {
        filteredSubCategory.sort((a, b) => parseInt(b.product_count) - parseInt(a.product_count));
    } else if (selectedStock === "oos") {
        filteredSubCategory = filteredSubCategory.filter(item => parseInt(item.product_count) === 0);
    }

    if (titleText != "all") {
        title = "title" + titleText;
        filteredSubCategory = filteredSubCategory.filter(item => item[title] == 'true');
    }

    renderSubCategory(filteredSubCategory);
};






