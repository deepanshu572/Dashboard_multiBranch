
$('#deliveryman_image').change(function () {
    const file = this.files[0];
    if (file) {
        if (file.size > 100 * 1024) { // Validate file size (100KB max)
            alert('Image size should not exceed 100 KB');
            $('#deliveryman_image').val('');
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

const fileInput2 = document.getElementById('indentityImage');
const imagePreviewContainer2 = document.getElementById('identityImagePreview');
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
                    <div class="identityimg">
                    <img src="${base64}" alt="Preview">
                    <button class="delete-icon" title="Delete"><i class="bi bi-trash3"></i></button>
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




$('#add-deliveryman-form').submit(function (e) {
    e.preventDefault();

    // Collect form data
    const full_name = $('#full_name').val().trim();
    const last_name = $('#last_name').val().trim();
    const identity_type = $('#identity_type').val().trim();
    const identity_number = $('#identity_number').val().trim();
    const email = $('#email').val().trim();
    const mobile = $('#mobile').val().trim();
    const password = $('#password').val().trim();
    const cpassword = $('#cpassword').val().trim();
    const file = $('#deliveryman_image')[0].files[0];

    // Validate required fields
    if (!full_name || !last_name || !identity_type || !identity_number || !email || !password || !cpassword) {
        errorAlert('Please fill out all required fields.');
        return;
    }
    if (password !== cpassword) {
        errorAlert('Passwords do not match.');
        return;
    }

    // Prepare FormData object
    const formData = new FormData();
    formData.append('type', 'addDeliveryMan');
    formData.append('full_name', full_name);
    formData.append('last_name', last_name);
    formData.append('identity_type', identity_type);
    formData.append('identity_number', identity_number);
    formData.append('email', email);
    formData.append('mobile', mobile);
    formData.append('password', password);
    formData.append('cpassword', cpassword);
    formData.append('imageFiles', JSON.stringify(imageFiles2));

    $("#submit-button").prop("disabled", true);

    // Image Upload Handling
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const base64Image = e.target.result;
            const fileExtension = file.name.split('.').pop().toLowerCase();

            formData.append('deliveryman_image', base64Image);
            formData.append('imageExtension', fileExtension);

            submitForm(formData);
        };
        reader.readAsDataURL(file);
    } else {
        // If no file is selected, submit without image
        submitForm(formData);
    }

    function submitForm(formData) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $("#submit-button").prop("disabled", false);
                if (response === 'success') {
                    successAlert('Successfully added');
                    $('#add-deliveryman-form')[0].reset();
                    loadDeliveryMan();
                } else {
                    errorAlert('Something went wrong');
                }
            },
            error: function () {
                $("#submit-button").prop("disabled", false);
                errorAlert('Error submitting data.');
            },
        });
    }
});


let deliveryManData = [];
let filteredDeliveryManData = [];

const loadDeliveryMan = async () => {
    const myFormData = new FormData();
    myFormData.append('type', 'loadDeliveryMan');
    const response = await fetch(apiurl, {
        method: 'POST',
        body: myFormData
    });
    const data = await response.json();
    console.log(data);

    if (data == null || data == 'error') {
        $("#result").html('no data found');
        return;
    }

    deliveryManData = data;

    if (localStorage.getItem('admin_role') === 'admin') {
        $("#filter-created-by").show();
        let creators = [...new Set(data.map(item => item.added_by || 'admin'))];
        let options = `<option value="all">All Creators</option>`;
        creators.forEach(c => {
            options += `<option value="${c}">@${c}</option>`;
        });
        $("#filter-created-by").html(options);
    }

    applyDeliveryManFilter();
}

const applyDeliveryManFilter = () => {
    let searchText = $("#search-input").val().toLowerCase();
    let createdByText = $("#filter-created-by").val();

    filteredDeliveryManData = deliveryManData.filter(item => {
        let match = true;
        
        if (searchText) {
            let nameStr = String(item.first_name + " " + item.last_name).toLowerCase();
            let emailStr = String(item.email).toLowerCase();
            if (!nameStr.includes(searchText) && !emailStr.includes(searchText)) {
                match = false;
            }
        }

        if (createdByText && createdByText !== 'all') {
            let addedBy = item.added_by || 'admin';
            if (addedBy !== createdByText) {
                match = false;
            }
        }
        
        return match;
    });

    renderDeliveryMan(filteredDeliveryManData);
};

const renderDeliveryMan = (data) => {
    $("#totalData").html(data.length);
    let html = `
    <thead>
            <tr>
                <th>SN</th>
                <th>Name</th>
                <th>Contact Info</th>
                <th>Created By</th>
                <th>Status</th>
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
            <td>
                <div class="flex gap-10">
                <img class="deliverymanimg" src="${imgurl + item.image_path}" alt="${item.name}">
                <span>${item.first_name} ${item.last_name}</span>
                </div>
            </td>
            <td>
                <p>${item.email}</p>
                <p>${item.mobile_number}</p>
            </td>
            <td style="color:#4F46E5; font-size:12px;"><b>@${item.added_by || 'admin'}</b></td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
                    onclick="handleCheckboxChange(this,'status','${item.id}')">
                    <span class="slider"></span>
                </label>
            </td>
           
            <td>
                 <div class="flex gap-5">
                    <button class="edit flex" onclick='editDeliveryMan(${items})'><i class="bi bi-pencil"></i></button>
                    <button class="delete flex" onclick='deleteDeliveryMan(${items})'><i class="bi bi-trash3"></i></button>
                </div>
            </td>
        </tr>
        `;
    });
    html += `</tbody>`;
    $("#result").html(html);
}

$(document).on("keyup", "#search-input", applyDeliveryManFilter);
$(document).on("change", "#filter-created-by", applyDeliveryManFilter);




function handleCheckboxChange(checkbox, typeStatus, id) {
    const isChecked = checkbox.checked;
    console.log('type : ', typeStatus, id)
    Swal.fire({
        title: 'Are you sure?',
        text: isChecked
            ? "You want to activate this delivery man"
            : "You want to deactivate this delivery man",
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
                data: { 'type': 'updateDeliveryMan', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
                success: function (response) {
                    if (response != 'error') {
                        Swal.fire({
                            title: 'Success!',
                            text: isChecked
                                ? `delivery man  activated successfully`
                                : `delivery man  deactivated successfully`,
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

let updateIdentityImageFiles = [];
const editDeliveryMan = (data) => {
    console.log(data);

    $('#full_name').val(data.first_name)
    $('#last_name').val(data.last_name)
    $('#identity_type').val(data.identity_type)
    $('#identity_number').val(data.identity_number)
    $('#email').val(data.email)
    $('#mobile').val(data.mobile_number)
    $('#password').val(data.password)
    $('#cpassword').val(data.password)

    $('#preview-img').attr('src', imgurl + data.image_path);
    $('#deliveryman_id').val(data.id)

    let multipleImage = JSON.parse(data.identity_image);

    console.log(multipleImage);
    updateIdentityImageFiles = multipleImage;

    multipleImage.map((item)=>{
        const imageElement = document.createElement('div');
        imageElement.classList.add('mip');
        imageElement.innerHTML = `
                <div class="identityimg">
                <img src="${imgurl + item}" alt="Preview">
                <button class="delete-icon" title="Delete"><i class="bi bi-trash3"></i></button>
                </div>
                `;

        imagePreviewContainer2.appendChild(imageElement);

        const deleteButton = imageElement.querySelector('.delete-icon');
        deleteButton.addEventListener('click', () => {

            imageElement.remove();
            imageFiles2 = imageFiles2.filter((img) => img.data !== item);
            console.log('Updated Files:', imageFiles2);
        });
        
    })

    document.getElementById('add-deliveryman-form').scrollIntoView({
        behavior: "smooth", 
        block: "center", 
        inline: "center"
    });
    $("#submit-button").hide();
    $("#update-button").show();

}

$("#reset-button").click(()=>{
    $('#add-deliveryman-form')[0].reset();
    $('#preview-img').attr('src', 'https://placehold.co/500x500');
    imageFiles2 = [];
    $("#submit-button").show();
    $("#update-button").hide();
    $("#identityImagePreview").html('');
})


const updateDeliveryMan = async () => {
        const deliveryman_id = $('#deliveryman_id').val();
        const full_name = $('#full_name').val().trim();
        const last_name = $('#last_name').val().trim();
        const identity_type = $('#identity_type').val().trim();
        const identity_number = $('#identity_number').val().trim();
        const email = $('#email').val().trim();
        const mobile = $('#mobile').val().trim();
        const password = $('#password').val().trim();
        const cpassword = $('#cpassword').val().trim();
        const file = $('#deliveryman_image')[0].files[0];

        updateIdentityImageFiles

        // Validate required fields
        if (!full_name || !last_name || !identity_type || !identity_number || !email || !password || !cpassword) {
            errorAlert('Please fill out all required fields.');
            return;
        }
        if (password !== cpassword) {
            errorAlert('Passwords do not match.');
            return;
        }

        // Prepare FormData object
        const formData = new FormData();
        formData.append('type', 'updateDeliveryMan');
        formData.append('deliveryman_id', deliveryman_id);
        formData.append('full_name', full_name);
        formData.append('last_name', last_name);
        formData.append('identity_type', identity_type);
        formData.append('identity_number', identity_number);
        formData.append('email', email);
        formData.append('mobile', mobile);
        formData.append('password', password);
        formData.append('cpassword', cpassword);
       
        formData.append('updateIdentityImageFiles', JSON.stringify(updateIdentityImageFiles));

        $("#update-button").prop("disabled", true);

        // Image Upload Handling
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const base64Image = e.target.result;
                const fileExtension = file.name.split('.').pop().toLowerCase();

                formData.append('deliveryman_image', base64Image);
                formData.append('imageExtension', fileExtension);

                submitForm(formData);
            };
            reader.readAsDataURL(file);
        } else {
            // If no file is selected, submit without image
            submitForm(formData);
        }

        function submitForm(formData) {
            $.ajax({
                url: apiurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $("#update-button").prop("disabled", false);
                    if (response === 'success') {
                        successAlert('Successfully updated');
                        $('#add-deliveryman-form')[0].reset();
                        loadDeliveryMan();
                        $("#submit-button").show();
                        $("#update-button").hide();
                    } else {
                        errorAlert('Something went wrong');
                    }
                },
                error: function () {
                    $("#update-button").prop("disabled", false);
                    errorAlert('Error submitting data.');
                },
            });
        }
}



const deleteDeliveryMan =async(item)=>{

    console.log(item);

    const result = await showConfirmationDialog('Do you want to delete?');
    if (result) {
        $.ajax({
            url: apiurl,
            type: 'POST',
            data: { type: 'deleteDeliveryMan', id: item.id },
            success: function (response) {
                if (response != 'error' && response != 'null') {
                    successAlert('Successfully Deleted');
                    loadDeliveryMan();
                } else {
                    errorAlert('Something went wrong');
                }
            }
        })
    }
}