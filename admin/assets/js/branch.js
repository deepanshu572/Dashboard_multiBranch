let editBranchMap = null;   
   
   function AddBranch(e) {
            e.preventDefault();

            let branchName = $("#branch-name").val();
            let branchDesc = $("#branch-desc").val();
            let branchPhone = $("#branch-phone").val();
            let branchEmail = $("#branch-email").val();
            let branchAddress = $("#branch-address").val();
            let branchCity = $("#branch-city").val();
            let branchState = $("#branch-state").val();
            let branchPincode = $("#branch-pincode").val();
            let branchLatitude = $("#branch-latitude").val();
            let branchLongitude = $("#branch-longitude").val();
            let coverage = $("#branch-Coverage").val();
            let branchIsOpen = $("#branch-isOpen").val();
            let branchStatus = $("#branch-status").val();
            let password = $("#branch-password").val();

            

            // console.log(branchName,branchAddress,branchCity,branchDesc,branchEmail,branchIsOpen,branchPincode,branchStatus);
            $("#submit-button").prop("disabled", true)

            $.ajax({
                url: apiurl,
                method: "POST",
                dataType: "JSON",
                data: {
                    type: "addBranch",
                    name: branchName,
                    description: branchDesc,
                    phone_no: branchPhone,
                    email: branchEmail,
                    password:password,
                    address: branchAddress,
                    city: branchCity,
                    latitude:branchLatitude,
                    longitude:branchLongitude,
                    coverage:coverage,
                    state: branchState,
                    pincode: branchPincode,
                    isOpen: branchIsOpen,
                    status: branchStatus
                },
                success: function (response) {
                    if (response.status == "success") {
                        successAlert('Successfully added');
                        $("#submit-button").prop("disabled", false);
                        loadBranch();
                        $("#branch-form")[0].reset();
                    } else {
                        console.log(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    alert('Error submitting data: ' + error);
                    $("#submit-button").prop("disabled", false);
                }
            });
        }
        function loadBranch() {

            $.ajax({
                url: apiurl,
                method: "POST",
                dataType: "JSON",
                data: {
                    type: "loadBranch"
                },
                success: function (response) {
                    if (response.status == "success") {
                        let data = response.data;
                        $("#totalData").html(data.length);

                        let html = `
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Branch Name</th>
                                <th>Description</th>
                                <th>Phone No</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Coverage</th>
                                <th>State</th>
                                <th>Pincode</th>
                                <th>Open</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        `;

                        data.forEach((item, index) => {
                            let items = JSON.stringify(item).replace(/'/g, "`");

                            html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.name}</td>
                            <td>${item.description}</td>
                            <td>${item.phone_no}</td>
                            <td>${item.email}</td>
                            <td>${item.city}</td>
                            <td>${item.latitude}</td>
                            <td>${item.longitude}</td>
                            <td>${item.coverage}</td>
                            <td>${item.state}</td>
                            <td>${item.pincode}</td>
                         
                            <td>
                            <label class="switch">
                                <input type="checkbox" ${item.isOpen == 'true' ? "checked" : ""}
                                onclick="handleCheckboxChange(this,'isOpen','${item.id}')">
                                <span class="slider"></span>
                            </label>
                        </td>
                         <td>
                            <label class="switch">
                                <input type="checkbox" ${item.status == 'true' ? "checked" : ""}
                                onclick="handleCheckboxChange(this,'status','${item.id}')">
                                <span class="slider"></span>
                            </label>
                        </td>
                            <td>
                            <div class="flex gap-20">
                                <button class="edit flex" onclick='editBranch(${items})'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="delete flex" onclick="deleteBranch(${item.id})"><i class="bi bi-trash3"></i></button>
                           </div>
                                </td>
                        </tr>
                        `;
                        });

                        html += `</tbody>`;

                        $("#result").html(html);
                    } else {
                        console.log(response.message);
                    }
                }
            });
        }
        loadBranch();


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
                        data: { 'type': 'updateStatus', 'typeStatus': typeStatus, 'id': id, 'statusText': statusText },
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

function editBranch(item) {
    $("#edit-branch-password").val(item.password);

    $("#edit-branch-id").val(item.id);
    $("#edit-branch-name").val(item.name);
    $("#edit-branch-desc").val(item.description);
    $("#edit-branch-phone").val(item.phone_no);
    $("#edit-branch-email").val(item.email);
    $("#edit-branch-address").val(item.address);
    $("#edit-branch-city").val(item.city);
    $("#edit-branch-state").val(item.state);
    $("#edit-branch-pincode").val(item.pincode);
    $("#edit-branch-latitude").val(item.latitude);
    $("#edit-branch-longitude").val(item.longitude);
    $("#coverage-branch").val(item.coverage);

    openBranchModal();

    setTimeout(() => {

        if (!editBranchMap) {

            editBranchMap = initBranchMap({
                mapId: "editMap",
                latInput: "#edit-branch-latitude",
                lngInput: "#edit-branch-longitude"
            });

        }

        editBranchMap.setLocation(
            item.latitude,
            item.longitude
        );

        editBranchMap.map.invalidateSize();

    }, 300);

}
        function deleteBranch(id) {
            $.ajax({
                url:apiurl,
                method:"POST",
                dataType:"JSON",
                data:{
                    type:"deleteBranch",
                    id
                },
                success:function (response) {
                    if(response.status == "success"){
                        console.log(response.message);
                        alert("deleted successfully !");
                        loadBranch();
                    }else{
                         console.log(response.message);
                    }
                }
            })
        }

        function updateBranch() {


            $.ajax({
                url: apiurl,
                method: "POST",
                dataType: "JSON",
                data: {
                    type: "updateBranch",
                    id: $("#edit-branch-id").val(),
                    name: $("#edit-branch-name").val(),
                    description: $("#edit-branch-desc").val(),
                    phone_no: $("#edit-branch-phone").val(),
                    email: $("#edit-branch-email").val(),
                    address: $("#edit-branch-address").val(),
                    password: $("#edit-branch-password").val(),
                    city: $("#edit-branch-city").val(),
                    state: $("#edit-branch-state").val(),
                    pincode: $("#edit-branch-pincode").val(),
                    longitude: $("#longitude-branch").val(),
                    latitude:$("#latitude-branch").val(),
                    coverage:$("#coverage-branch").val()
                },
                success: function (response) {
                    if (response.status == "success") {
                        alert(response.message);
                        loadBranch();
                        closeBranchModal();

                    } else {
                        console.log(response.message);
                    }
                }
            });
        }
        function openBranchModal() {
            $("#branchModal").css("display", "flex");
        }

        function closeBranchModal() {
            $("#branchModal").hide();
        }


          
