let editBannerId = null;

$(document).ready(function () {

    // Image upload and preview
    $('#banner-image').change(function () {
        const file = this.files[0];
        if (file) {
            if (file.size > 1000 * 1024) { // Validate file size (100KB max)
                alert('Image size should not exceed 1000 KB');
                $('#banner-image').val('');
                $('#preview-img').attr('src', 'https://placehold.co/600x200');
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
        $('#banner_type').val('');
        $('#category').val('');
        $('#sub-category').val('');
        $('#banner-image').val('');
        $("#middle-category").val('');
        $('#preview-img').attr('src', 'https://placehold.co/600x200');
        $("#flash-sale-manage-link").hide();
        
        editBannerId = null;
        $('#submit-button').text('Submit');
    });

    // Submit form
    $('#banner-form').submit(function (e) {
        e.preventDefault();

        // Fetch input values
        const bannerType = $('#banner_type').val();
        const category = $('#category').val();
        const middleCategory = $('#middle-category').val();
        // const subCategory = $('#sub-category').val();
        const file = $('#banner-image')[0].files[0];

      

        let data = {}



        if (!file && !editBannerId) {
            errorAlert('Please upload a banner image.');
            return;
        }

        let processAjax = function(base64Image = null, fileExtension = null) {
            let actionType = editBannerId ? 'editBanner' : 'addBanner';
            
            if ( bannerType == "bannerSec1"|| bannerType =="bannerSec2" || bannerType == "bannerSec3" || bannerType == 'footer') {
                data = {
                    type: actionType,
                    bannerType: bannerType,
                    middleCategory:middleCategory,
                    // subCategory: subCategory,
                    category: category,
                    device: 'mobile',
                };
            } else {
                data = {
                    type: actionType,
                    bannerType: bannerType,                    
                    category: category,
                    middleCategory:middleCategory,
                    device: 'mobile',
                };
            }
            
            if (editBannerId) {
                data.b_id = editBannerId;
                data.category = category; // Pass both for clean updates
                data.middleCategory = middleCategory||'';
                // data.subCategory = subCategory;
            }
            
            if (base64Image) {
                data.base64Image = base64Image;
                data.fileExtension = fileExtension;
            }

            console.log("data================");
            console.log(data);
            console.log("=================data");
            $.ajax({
                url: apiurl,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response === 'success') {
                        successAlert(editBannerId ? 'Successfully updated' : 'Successfully added');
                        $('#reset-button').click(); // Reset form
                         $("#banner-image").prop("required", true); 
                        loadBanner(); 
                    } else {
                        errorAlert('Error occurred.');
                    }
                },
                error: function () {
                    errorAlert('Error submitting data.');
                },
            });
        };

        if (file) {
            // Convert files to base64
            const reader1 = new FileReader();
            reader1.onload = function (e1) {
                const base64Img = e1.target.result;
                const ext = file.name.split('.').pop().toLowerCase();
                processAjax(base64Img, ext);
            };
            reader1.readAsDataURL(file);
        } else if (editBannerId) {
            // Edit without changing image
            processAjax();
        }
    });
});

function handleCheckboxChange(checkbox, typeStatus, id) {
    const isChecked = checkbox.checked;
    console.log('type : ', typeStatus, id)
    Swal.fire({
        title: 'Are you sure?',
        text: isChecked
            ? "You want to activate this Banner"
            : "You want to deactivate this Banner",
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
                data: { 'type': 'updateBanner', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
                success: function (response) {
                    if (response != 'error') {
                        Swal.fire({
                            title: 'Success!',
                            text: isChecked
                                ? `Banner  activated successfully`
                                : `Banner deactivated successfully`,
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

const bannerType = () => {

    const bannerType = $("#banner_type").val();
    console.log(bannerType);
    $("#flash-sale-manage-link").toggle(bannerType === 'flash_sale_banner');
    if (bannerType == 'main' || bannerType == 'topLeft' || bannerType == "topRight" || bannerType == "bannerSec1"|| bannerType =="bannerSec2" || bannerType == "bannerSec3" || bannerType == "footer" || bannerType == 'website_bannner' || bannerType=='category_banner') {
        // $(".banner-subcategory").show();
        $(".banner-category").show();
        $(".banner-middle-category").show();
    }
    else {
        // $(".banner-subcategory").hide();
        $(".banner-category").hide();
        $(".banner-middle-category").hide();
    }
}




const loadCategoryList = () => {

    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { 'type': 'loadCategory' },
        success: function (response) {
            if (response != null || response != 'error') {
                let data = JSON.parse(response);

                let categoryList = ' <option value="" selected disabled hidden>Select Category</option>';
                data.forEach((item, index) => {
                    categoryList += `
                            <option value="${item.id}">${item.name}</option>
                            `;
                })
                $("#category").html(categoryList);
            }
        }
    })

}


const loadMiddleCategoryList = (categoryId) => {

    return $.ajax({
        url: apiurl,
        type: 'POST',
        dataType:"JSON",
        data: { type: 'getmiddleCategory',categoryId },
        success: function (response) {
            console.log(response)
            if (response.status == "success") { 

                let data = response?.data;
                console.log(data);
                let subCategoryList = ' <option value="" selected disabled hidden>Select Middle category</option>';
                data?.forEach((item, index) => {
                    subCategoryList += `
                        <option value="${item.id}">${item.name}</option>
                        `;
                });
                $("#middle-category").html(subCategoryList);
            } else {
                $("#middle-category").html(`
                        <option value="" selected disabled hidden>no data found</option>
                 `);
            }
        }
    })
}

// const loadSubCategoryList = (categoryid) => {

//     $.ajax({
//         url: apiurl,
//         type: 'POST',
//         data: { type: 'loadSubCategoryList',categoryid },
//         success: function (response) {
//             console.log(response);
//             if (response != null && response != 'error') {
//                 let data = JSON.parse(response);
//                 let subCategoryList = ' <option value="" selected disabled hidden>Select Subcategory</option>';
//                 data.forEach((item, index) => {
//                     subCategoryList += `
//                         <option value="${item.id}">${item.name}</option>
//                         `;
//                 })
//                 $("#sub-category").html(subCategoryList);
//             } else {
//                 $("#sub-category").html(`
//                         <option value="" selected disabled hidden>no data found</option>
//                         `);

//             }
//         }
//     })
// }


const deleteBanber = async (b_id) => {
    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteBanber', b_id: b_id },
            success: function (response) {
                if (response != 'error' && response != 'null') {
                    successAlert('Successfully Deleted');
                    loadBanner();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })
    }
}

const editBannerBtn = async (item) => {
    editBannerId = item.id || item.b_id;
    
    // Smooth scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });

    $('#banner_type').val(item.type).trigger('change');
    await loadMiddleCategoryList(item.under_category);

    $("#middle-category").val(item.under_middle_category);
    $('#category').val(item.under_category);

    
    // if (item.under_category) {
    //     $('#category').val(item.under_category);

    // } else if (item.uder_subcategory) {
    //     $('#category').val('');
    //     // Subcategories are dynamically loaded, so we might need a timeout if they aren't loaded yet
    //     setTimeout(() => {
    //         $('#sub-category').val(item.uder_subcategory);
    //     }, 500);
    // } else {
    //     $('#category').val('');
    //     $('#sub-category').val('');
    // }
    
    if (item.img_path) {
        $('#preview-img').attr('src', imgurl + item?.img_path);
       $("#banner-image").prop("required", false);  
      }
    
    $('#submit-button').text('Update Banner');
};

let bannerData = [];
let filteredBannerData = [];

const loadBanner = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadBanner' },
        success: function (response) {
            if (response != 'error' && response != null) {
                let data = JSON.parse(response);
                bannerData = data;

                if (localStorage.getItem('admin_role') === 'admin') {
                    $("#filter-created-by").show();
                    let creators = [...new Set(data.map(item => item.added_by || 'admin'))];
                    let options = `<option value="all">All Creators</option>`;
                    creators.forEach(c => {
                        options += `<option value="${c}">@${c}</option>`;
                    });
                    $("#filter-created-by").html(options);
                }

                applyBannerFilter();
            } else {
                $(".result").hide();
            }
        },
        error: function() {
            $(".result").hide();
        }
    });
};

const applyBannerFilter = () => {
    let searchText = $("#search-input").val().toLowerCase();
    let createdByText = $("#filter-created-by").val();

    filteredBannerData = bannerData.filter(item => {
        let match = true;
        
        let typeName = String(item.type || '').toLowerCase();
        let catName = String(item.category_name || '').toLowerCase();
        let subCatName = String(item.subcategory_name || '').toLowerCase();
        
        if (searchText && !(typeName.includes(searchText) || catName.includes(searchText) || subCatName.includes(searchText))) {
            match = false;
        }

        if (createdByText && createdByText !== 'all') {
            let addedBy = item.added_by || 'admin';
            if (addedBy !== createdByText) {
                match = false;
            }
        }
        
        return match;
    });

    renderBanner(filteredBannerData);
};

const renderBanner = (data) => {
    $("#totalData").html(data.length);
    $(".result").show(); 

    let html = `
        <thead>
            <tr>
                <th>SN</th>
                <th>Type</th>
                <th>Under Category</th>
                <th>Under Middle Category</th>
                <th>Image</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
    `;

    data.forEach((item, index) => {
        html += `
            <tr>
                <td class="sl">${index + 1}</td>
                <td>${item.type}</td>
                <td>${item.category_name != null ? item.category_name : 'No'}</td>
                <td>${item.middlecategory_name != null ? item.middlecategory_name : 'No'}</td>
                <td><img src="${imgurl + item.img_path}" alt="banner image"></td>
                <td style="color:#4F46E5;"><b>@${item.added_by || 'admin'}</b></td>
                <td>
                    <label class="switch">
                        <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
                        onclick="handleCheckboxChange(this,'status','${item.id}')">
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    <div class="flex gap-10">
                        <button class="flex edit" onclick='editBannerBtn(${JSON.stringify(item)})'><i class="bi bi-pencil-square"></i></button>
                        <button class="flex delete" onclick="deleteBanber(${item.id || item.b_id})"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += `</tbody>`;
    $("#result").html(html);
};

$(document).on("keyup", "#search-input", applyBannerFilter);
$(document).on("change", "#filter-created-by", applyBannerFilter);
