       let categoryListData = [];
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



        function handleMiddleCategory(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append("type", "handleMiddleCategory")
            formData.append("categoryId", $("#category").val())
            formData.append("middleCategory", $("#middle-category-name").val())
            formData.append("category_img", $("#middle-category-image")[0].files[0])
            // console.log(middleCategory,categoryId,category_img)
            $.ajax({
                url: apiurl,
                method: "POST",
                dataType: "JSON",
                processData: false,
                contentType: false,
                data: formData,
                success: function (response) {
                    if (response.status == "success") {
                        console.log(response.message);
                        getMiddlecategory();
                        $('#middle-category-form')[0].reset();
                        $('#preview-img').attr('src', 'https://placehold.co/500x500');


                    } else {
                        console.log(response.message);

                    }
                }
            })
        }

        function getMiddlecategory() {
            $.ajax({
                url: apiurl,
                method: "POST",
                dataType: "JSON",
                data: {
                    type: "getmiddleCategory2",
                },
                success: function (response) {
                    if (response.status == "success") {
                        console.log(response.data);
                        renderSubCategory(response.data);
                        $("#totalData").html(response?.data?.length)
                    } else {
                        console.log(response.message);
                    }
                }
            });
        }
       
        getMiddlecategory();


        const renderSubCategory = (data) => {
            let html = `
            <thead>
                    <tr>
                        <th>SN</th>
                        <th>Image</th>
                        <th>Main <br> Category</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Title 1</th>
                        <th>Title 2</th>
                        <th>Title 3</th>
                        <th>Title 4</th>
                        <th>Title 5</th>
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

                        <td>${item.name}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
                                onclick="handleCheckboxChange(this,'status','${item.id}')">
                                <span class="slider"></span>
                            </label>
                        </td> 
                        <td>
                <label class="switch">
                    <input type="checkbox" ${item.title1 == 'true' ? "checked" : ""}
                    onclick="handleCheckboxChange(this,'title1','${item.id}')">
                    <span class="slider"></span>
                </label>
            </td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${item.title2 == 'true' ? "checked" : ""}
                    onclick="handleCheckboxChange(this,'title2','${item.id}')">
                    <span class="slider"></span>
                </label>
            </td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${item.title3 == 'true' ? "checked" : ""} 
                    onclick="handleCheckboxChange(this,'title3','${item.id}')">
                    <span class="slider"></span>
                </label>
            </td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${item.title4 == 'true' ? "checked" : ""}  onclick="handleCheckboxChange(this,'title4','${item.id}')">
                    <span class="slider"></span>
                </label>
            </td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${item.title5 == 'true' ? "checked" : ""}  onclick="handleCheckboxChange(this,'title5','${item.id}')">
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
                        data: { 'type': 'updateMiddleCategory', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
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


        $('#middle-category-image').change(function () {
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
            $('#middle-category-form')[0].reset();
            $('#preview-img').attr('src', 'https://placehold.co/500x500');
        });


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


