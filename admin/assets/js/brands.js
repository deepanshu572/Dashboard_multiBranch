$(document).ready(function () {

    // Image upload and preview
    $('#brand-image').change(function () {
        const file = this.files[0];
        if (file) {
            if (file.size > 100 * 1024) { // Validate file size (100KB max)
                alert('Image size should not exceed 100 KB');
                $('#brand-image').val('');
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
    $('#brand-product-image').change(function () {
        const file = this.files[0];
        if (file) {
            if (file.size > 100 * 1024) { // Validate file size (100KB max)
                alert('Image size should not exceed 100 KB');
                $('#brand-product-image').val('');
                $('#preview-img2').attr('src', 'https://placehold.co/500x500');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-img2').attr('src', e.target.result);
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
    $('#brand-form').submit(function (e) {
        e.preventDefault();

        // Fetch input values
        const brandName = $('#brand_name').val();
        const description = $('#description').val();
        const file = $('#brand-image')[0].files[0];
        const file1 = $('#brand-product-image')[0].files[0];
        let categoryId = $('#selectedCategoryId').val();
        console.log('Selected Category ID:', categoryId);
        // return; // Remove this line after debugging

        // Check if both files are provided
        if (file && file1) {
            // Convert files to base64
            const reader1 = new FileReader();
            const reader2 = new FileReader();

            reader1.onload = function (e1) {
                const base64Image1 = e1.target.result;
                const fileExtension1 = file.name.split('.').pop().toLowerCase();

                reader2.onload = function (e2) {
                    const base64Image2 = e2.target.result;
                    const fileExtension2 = file1.name.split('.').pop().toLowerCase();

                    // Make AJAX call
                    $.ajax({
                        url: apiurl,
                        type: 'POST',
                        data: {
                            type: 'addBrands',
                            brandName: brandName,
                            description: description,
                            brandLogo: base64Image1,
                            brandProductImage: base64Image2,
                            imageExtension1: fileExtension1,
                            imageExtension2: fileExtension2,
                            categoryId: categoryId
                        },
                        success: function (response) {
                            if (response === 'success') {
                                successAlert('Successfully added');
                                loadBrands(); // Reload subcategories
                            } else {
                                errorAlert('Error occurred while adding the brand.');
                            }
                        },
                        error: function () {
                            errorAlert('Error submitting data.');
                        },
                    });
                };

                reader2.readAsDataURL(file1); // Read the second file
            };

            reader1.readAsDataURL(file); // Read the first file
        } else {
            errorAlert('Please upload both images.');
        }
    });



});



const loadBrands =()=>{
    $.ajax({
        url:apiurl,
        type:'POST',
        data:{'type':'loadBrands'},
        success:function(response){
            if(response !=null && response !='error'){
               
                let data=JSON.parse(response);
                $("#totalData").html(data.length);
                let html =`
                <thead>
                        <tr>
                            <th>SN</th>
                            <th>Category name</th>
                            <th>Logo Image</th>
                            <th>Product Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Promotion</th>
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
                        <td>${item?.categoryName}</td>
                        <td><img src="${imgurl+item.logo_path}" alt="${item.name}"></td>
                        <td><img src="${imgurl+item.product_path}" alt="${item.name}"></td>
                        <td>${item.name}</td>
                        <td>${item.description}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" ${item.status=='true' ? "checked" : ""}
                                onclick="handleCheckboxChange(this,'status','${item.id}')">
                                <span class="slider"></span>
                            </label>
                        </td>
                         <td>
                            <label class="switch">
                                <input type="checkbox" ${item.promotion=='true' ? "checked" : ""}
                                onclick="handleCheckboxChange(this,'promotion','${item.id}')">
                                <span class="slider"></span>
                            </label>
                        </td>
                        <td>
                            <button class="edit flex"onclick='editBrands(${items})'><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                    `;
                });
                html +=`</tbody>`;
                $("#result").html(html);
            }else{
                $("#result").html("no data found");
            }
        }
    })

}


function handleCheckboxChange(checkbox,typeStatus,id) {
    const isChecked = checkbox.checked;
    console.log('type : ' ,typeStatus ,id)
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
           let statusText = isChecked? "true": "false";
        $.ajax({
            url:apiurl,
            type:'POST',
            data:{'type':'updateBrands','typeStatus':typeStatus ,'id':id,'statusText':statusText},
            success:function(response){
                if(response!='error'){
                    Swal.fire({
                        title: 'Success!',
                        text: isChecked
                            ? `Subcategory  activated successfully`
                            : `Subcategory  deactivated successfully`,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }else{
                    checkbox.checked = !isChecked;
                }
            }
        })
           
        } else {
            checkbox.checked = !isChecked;
        }
    });
}



//  edit section start here 


const editBrands =async(item)=>{

    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);
   
    $("#brand_id").val(item.id)
    document.getElementById('brandName').value = item.name;
    document.getElementById('brandDesc').value = item.description;
    document.getElementById('imagePreview').src = imgurl+item.logo_path;
    document.getElementById('imagePreview2').src = imgurl+item.product_path;
    $(".edit-modal").addClass('active');
    $(".wrapper-overlay").addClass('active');
    $('body').css("overflow" ,'hidden');
}


function closeModal() {
    $(".edit-modal").removeClass('active');
    $(".wrapper-overlay").removeClass('active');
    $('body').css("overflow" ,'auto');
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

function previewImage2(event) {
    const file = event.target.files[0];
    if (file && file.size > 100000) { // 100KB limit
        document.getElementById('imageError2').style.display = 'block';
        event.target.value = "";
        return;
    } else {
        document.getElementById('imageError2').style.display = 'none';
    }
    
    const reader = new FileReader();
    reader.onload = function () {
        document.getElementById('imagePreview2').src = reader.result;
    };
    reader.readAsDataURL(file);
}

const saveChanges =()=> {
    const brand_id = $("#brand_id").val();
    const brandName = document.getElementById('brandName').value;
    const brandDesc = document.getElementById('brandDesc').value;
    // const imageSrc = document.getElementById('imageUpload');
    const file = $('#imageUpload')[0].files[0];
    const file2 = $('#imageUpload2')[0].files[0];
    // console.log('Updated Category:', name, imageSrc);

    if (brandName == '') {
        warningAlert('Enter valid Name');
        return;
    }
    if (brandDesc == '') {
        warningAlert('Enter valid Description');
        return;
    }

    // Check if the first file is provided
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const base64Image = e.target.result;
            const fileExtension = file.name.split('.').pop().toLowerCase();
            let data = { type: 'editBrandWithLogo', brand_id: brand_id, brandName: brandName, brandDesc:brandDesc , brandLogo: base64Image, imageExtension: fileExtension };

            // If the second file is provided, read it as well
            if (file2) {
                const reader2 = new FileReader();
                reader2.onload = function (e2) {
                    const base64Image2 = e2.target.result;
                    const fileExtension2 = file2.name.split('.').pop().toLowerCase();
                    data.productPhoto = base64Image2; // Add second image data
                    data.imageExtension2 = fileExtension2; // Add second image extension

                    // Make the AJAX call with both images
                    $.ajax({
                        url: apiurl,
                        type: 'POST',
                        data: data,
                        success: function (response) {
                            if (response == 'success') {
                                successAlert('Successfully updated');
                                loadBrands();
                            } else {
                                errorAlert('something went wrong');
                            }
                        },
                        error: function () {
                            alert('Error submitting data.');
                        },
                    });
                };
                reader2.readAsDataURL(file2); // Read the second file
            }
         
            else {
                // Make the AJAX call with only the first image
                $.ajax({
                    url: apiurl,
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        if (response == 'success') {
                            successAlert('Successfully updated');
                            loadBrands();
                        } else {
                            errorAlert('something went wrong');
                        }
                    },
                    error: function () {
                        alert('Error submitting data.');
                    },
                });
            }
        };
        reader.readAsDataURL(file); // Read the first file
    } 
    else if (file2) { // Check if only the second file is provided
        const reader2 = new FileReader();
        reader2.onload = function (e2) {
            const base64Image2 = e2.target.result;
            const fileExtension2 = file2.name.split('.').pop().toLowerCase();
            let data = { type: 'editBrandWithLogo', brand_id: brand_id, brandName: brandName, brandDesc: brandDesc, productPhoto: base64Image2, imageExtension2: fileExtension2 };

            // Make the AJAX call with only the second image
            $.ajax({
                url: apiurl,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response == 'success') {
                        successAlert('Successfully updated');
                        loadBrands();
                    } else {
                        errorAlert('something went wrong');
                    }
                },
                error: function () {
                    alert('Error submitting data.');
                },
            });
        };
        reader2.readAsDataURL(file2); // Read the second file
    }
    else {
        // If no file is provided, just update the category name
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'editBrand', brand_id: brand_id,  brandName: brandName, brandDesc:brandDesc },
            success: function (response) {
                if (response == 'success') {
                    successAlert('Successfully updated');
                    loadBrands();
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



