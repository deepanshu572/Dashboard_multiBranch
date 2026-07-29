let categoryData = [];

const loadCategory =()=>{


$.ajax({
    url:apiurl,
    type:'POST',
    data:{'type':'loadCategory'},
    success:function(response){
        if(response !=null || response !='error'){
            let data=JSON.parse(response);
            categoryData = data;
            renderCategory(data);
           
        }
    }
})

}


const renderCategory =(data)=>{

    let html =`
    <thead>
            <tr>
                <th>SL</th>
                <th>Category Image</th>
                <th>Name</th>
                <th>Status</th>
                <th>No Of Product</th>
                <th>Action</th>
            </tr>
        </thead>
    <tbody>
    `;
    data.forEach((category, index) => {

        let items = JSON.stringify(category).replace(/'/g, '`');
       html += `
       <tr>
            <td class="sl">${index + 1}</td>
            <td><img src="${imgurl+category.image_path}" alt="${category.name}"></td>
            <td>${category.name}</td>
            <td>
                <label class="switch">
                    <input type="checkbox" ${category.status=='true' ? "checked" : ""}
                    onclick="handleCheckboxChange(this,'status','${category.id}')">
                    <span class="slider"></span>
                </label>
            </td>
            <td>${category.product_count}</td>
            <td>
                <button class="edit flex" onclick='editCategory(${items})'><i class="bi bi-pencil"></i></button>
            </td>
        </tr>
        `;
    });
    html +=`</tbody>`;
    $("#result").html(html);

}


const searchCategory = () => {
    let searchText = $("#search-input").val().toLowerCase();
    filteredData = categoryData.filter(item =>
        item.name.toLowerCase().includes(searchText)
    );
    renderCategory(filteredData);
};


$(document).on("keyup", "#search-input", function () {
    searchCategory();
});






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
            data:{'type':'updateCategory','typeStatus':typeStatus ,'id':id,'statusText':statusText},
            success:function(response){
                if(response!='error'){
                    Swal.fire({
                        title: 'Success!',
                        text: isChecked
                            ? `Category  activated successfully`
                            : `Category  deactivated successfully`,
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



const editCategory =async(item)=>{

    item = JSON.stringify(item).replace(/`/g, "'");
    item = JSON.parse(item);

    $("#cat_id").val(item.id)
    document.getElementById('categoryName').value = item.name;
    document.getElementById('imagePreview').src = imgurl+item.image_path;
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

const saveChanges =()=> {
    const cat_id =$("#cat_id").val();
    const name = document.getElementById('categoryName').value;
    // const imageSrc = document.getElementById('imageUpload');
    const file = $('#imageUpload')[0].files[0];
    // console.log('Updated Category:', name, imageSrc);

    if(name==''){
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
                data: {type: 'editCategoryWithImage', cat_id:cat_id, categoryName:name, categoryImage:base64Image,imageExtension:fileExtension},
                success: function (response) {
                    if(response=='success'){
                        successAlert('Successfully updated');
                        loadCategory();
                    }else{
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
            data: {type: 'editCategoryName', cat_id:cat_id, categoryName:name},
            success: function (response) {
                if(response=='success'){
                    successAlert('Successfully updated');
                    loadCategory();
                }else{
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