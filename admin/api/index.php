<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true, 
    'cookie_samesite' => 'None', 
]);
ob_start(); // Buffer output to prevent accidental leaks
date_default_timezone_set("Asia/Kolkata");
error_reporting(0); // Suppress errors to avoid breaking JSON
ini_set('display_errors', 0);
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$con=mysqli_connect("localhost","root","","ITS_GROCERY_BRANCH");          
// Check connection
if (mysqli_connect_errno()) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Database connection failed", "error" => mysqli_connect_error()]);
    exit;
}

// Set MySQL session timezone to IST (+05:30)
mysqli_query($con, "SET time_zone = '+05:30'");

   $type = isset($_POST['type']) ? $_POST['type'] : null;

   // --- AUTHENTICATION MIDDLEWARE ---
   $publicTypes = ['adminLogin'];
   
   if (!in_array($type, $publicTypes)) {
       if (!isset($_SESSION['admin_login_status']) || $_SESSION['admin_login_status'] !== true) {
           echo json_encode(["status" => "auth_error", "message" => "Session expired or unauthorized"]);
           exit;
       }

       // --- ROLE & PERMISSION CHECK ---
       $adminOnlyTypes = ['addStaff', 'updateStaff', 'updateStaffStatus', 'updateStaffPermissions', 'deleteStaff'];
       if (in_array($type, $adminOnlyTypes) && $_SESSION['admin_role'] !== 'admin') {
           echo json_encode(["status" => "permission_error", "message" => "Only administrators can perform this action"]);
           exit;
       }

       // Permission based checks for staff
       if ($_SESSION['admin_role'] === 'staff' && $_SESSION['admin_permissions'] !== 'all') {
           $permissions = explode(',', $_SESSION['admin_permissions']);
           $productActions = ['addProduct', 'updateProduct', 'deleteProduct', 'updateProductStatus'];
           $categoryActions = ['addCategory', 'updateCategory', 'deleteCategory'];
           $orderActions = ['updateOrder', 'rejectOrder', 'assignDeliveryMan', 'updateDeliveryOrderStatus'];

           if (in_array($type, $productActions) && !in_array('Product', $permissions)) {
               echo json_encode(["status" => "permission_error", "message" => "You don't have permission to manage products"]);
               exit;
           }
           if (in_array($type, $categoryActions) && !in_array('Category', $permissions)) {
               echo json_encode(["status" => "permission_error", "message" => "You don't have permission to manage categories"]);
               exit;
           }
           if (in_array($type, $orderActions) && !in_array('Order', $permissions)) {
               echo json_encode(["status" => "permission_error", "message" => "You don't have permission to manage orders"]);
               exit;
           }
       }
   }

   // --- AUTO AUDIT LOGGING ---
   // Use a strict whitelist to ONLY log data modification events (no more db flooding from read calls)
   $isActionType = false;
    if (!empty($type)) {
        if (preg_match('/^(add|update|delete|set|change|edit|assign|upload|posOrder|updateOrder|rejectOrder|updateDeliveryOrderStatus|updateWallet|changePassword)/i', $type)) {
            $isActionType = true;
        }
    }
      if ($isActionType) {
       $log_admin_username = isset($_SESSION['admin_username']) ? mysqli_real_escape_string($con, $_SESSION['admin_username']) : 'Unknown/Direct';
       $action_type = mysqli_real_escape_string($con, $type);
       
       // --- Capture Previous Data for Diffing & Context ---
       $previousData = null;
       // Try all possible ID fields used in POST
       $idField = $_POST['id'] ?? $_POST['p_id'] ?? $_POST['b_id'] ?? $_POST['vid'] ?? $_POST['cid'] ?? $_POST['scid'] ?? $_POST['cat_id'] ?? $_POST['brand_id'] ?? null;
       
       if ($idField && !empty($idField)) {
           $tableMap = [
               // Updates & Status Changes
               'updateProduct' => ['table' => 'product', 'id' => 'p_id'],
               'updateProductStatus' => ['table' => 'product', 'id' => 'p_id'],
               'updateCategory' => ['table' => 'category', 'id' => 'id'],
               'updateCategoryStatus' => ['table' => 'category', 'id' => 'id'],
               'editCategoryName' => ['table' => 'category', 'id' => 'id'],
               'editCategoryWithImage' => ['table' => 'category', 'id' => 'id'],
               'updateSubCategory' => ['table' => 'subcategory', 'id' => 'id'],
               'updateSubCategoryStatus' => ['table' => 'subcategory', 'id' => 'id'],
               'editSubCategoryName' => ['table' => 'subcategory', 'id' => 'id'],
               'editSubCategoryWithImage' => ['table' => 'subcategory', 'id' => 'id'],
               'updateBrands' => ['table' => 'brands', 'id' => 'id'],
               'updateBrandStatus' => ['table' => 'brands', 'id' => 'id'],
               'editBrand' => ['table' => 'brands', 'id' => 'id'],
               'editBrandWithLogo' => ['table' => 'brands', 'id' => 'id'],
               'updateBanner' => ['table' => 'banner', 'id' => 'b_id'],
               'editBanner' => ['table' => 'banner', 'id' => 'b_id'],
               'updateBannerStatus' => ['table' => 'banner', 'id' => 'b_id'],
               'updateCoupon' => ['table' => 'coupon', 'id' => 'id'],
               'updateDeliveryMan' => ['table' => 'delivery_man', 'id' => 'id'],
               'updateDeliveryManStatus' => ['table' => 'delivery_man', 'id' => 'id'],
               'updateStaffStatus' => ['table' => 'admin', 'id' => 'id'],
               'updateStaffPermissions' => ['table' => 'admin', 'id' => 'id'],
               'updateUserStatus' => ['table' => 'user', 'id' => 'id'],
               
               // Deletes
               'deleteProduct' => ['table' => 'product', 'id' => 'p_id'],
               'deleteCategory' => ['table' => 'category', 'id' => 'id'],
               'deleteSubCategory' => ['table' => 'subcategory', 'id' => 'id'],
               'deleteBanner' => ['table' => 'banner', 'id' => 'b_id'],
               'deleteCoupon' => ['table' => 'coupon', 'id' => 'id'],
               'deleteVarient' => ['table' => 'varient', 'id' => 'vid'],
               'deleteDeliveryMan' => ['table' => 'delivery_man', 'id' => 'id'],
               'deleteHeroBanner' => ['table' => 'hero_banner', 'id' => 'id'],
               'deleteArea' => ['table' => 'area', 'id' => 'id']
           ];
           
           if (isset($tableMap[$type])) {
               $tableName = $tableMap[$type]['table'];
               $idCol = $tableMap[$type]['id'];
               $cleanId = mysqli_real_escape_string($con, $idField);
               $oldRes = mysqli_query($con, "SELECT * FROM `$tableName` WHERE `$idCol` = '$cleanId' LIMIT 1");
               if ($oldRes && mysqli_num_rows($oldRes) > 0) {
                   $dbData = mysqli_fetch_assoc($oldRes);
                   
                   // Remove binary/large data
                   unset($dbData['productImage'], $dbData['subCategoryImage'], $dbData['categoryImage'], $dbData['brandLogo'], $dbData['brandProductImage'], $dbData['image_path'], $dbData['logo_path'], $dbData['base64Image'], $dbData['identity_image']);
                   
                   // --- Key Mapping: Normalize DB Column Names to POST Keys for Diffing ---
                   $keyMaps = [
                       'updateProduct' => [
                           'under_category' => 'category',
                           'under_subcategory' => 'subCategory',
                           'brand_name' => 'brandName',
                           'name' => 'productName',
                           'selling_price' => 'sellingPrice',
                           'purchase_price' => 'purchasePrice',
                           'review_val' => 'review',
                           'review_nop' => 'reviewNop',
                           'sku_number' => 'skuNumber',
                           'p_limit' => 'productLimit',
                           'keyword' => 'edit_product_keyword'
                       ],
                       'editCategoryName' => [
                           'name' => 'categoryName'
                       ],
                       'editCategoryWithImage' => [
                           'name' => 'categoryName'
                       ],
                       'editSubCategoryName' => [
                           'name' => 'categoryName',
                           'under_category' => 'under_category'
                       ],
                       'editSubCategoryWithImage' => [
                           'name' => 'categoryName',
                           'under_category' => 'under_category'
                       ],
                       'editBrand' => [
                           'name' => 'brandName',
                           'description' => 'brandDesc'
                       ],
                       'editBrandWithLogo' => [
                           'name' => 'brandName',
                           'description' => 'brandDesc'
                       ],
                       'updateBanner' => [
                           'banner_title' => 'bannerTitle',
                           'banner_type' => 'bannerType',
                           'banner_link' => 'bannerLink'
                       ],
                       'editBanner' => [
                           'type' => 'bannerType',
                           'under_category' => 'category',
                           'uder_subcategory' => 'subCategory'
                       ],
                       'updateDeliveryMan' => [
                           'name' => 'deliveryManName',
                           'email' => 'deliveryManEmail',
                           'mobile' => 'deliveryManMobile'
                       ]
                   ];
                   
                   $previousData = $dbData;
                   if (isset($keyMaps[$type])) {
                       foreach ($keyMaps[$type] as $dbKey => $postKey) {
                           if (isset($dbData[$dbKey])) {
                               $previousData[$postKey] = $dbData[$dbKey];
                           }
                       }
                   }
               }
           }
       }
       
       // Encode entire POST payload (except large binary strings) for details
       $detailsPayload = $_POST;
       
       // --- Dynamic Status Normalization ---
       // If this action relies on dynamic `typeStatus` & `statusText`, inject actual column for diffing
       if (isset($_POST['typeStatus']) && isset($_POST['statusText'])) {
           $dynamicCol = $_POST['typeStatus']; // e.g. "status" or "is_popular"
           $detailsPayload[$dynamicCol] = $_POST['statusText']; 
           if (isset($dbData) && isset($dbData[$dynamicCol])) {
               $previousData[$dynamicCol] = $dbData[$dynamicCol];
           }
       } else if (isset($_POST['statusText']) && !isset($_POST['typeStatus'])) {
           // E.g. updateDeliveryManStatus hardcodes 'status'
           $detailsPayload['status'] = $_POST['statusText'];
           if (isset($dbData) && isset($dbData['status'])) {
               $previousData['status'] = $dbData['status'];
           }
       }
       
       $largeKeys = ['base64Image', 'categoryImage', 'subCategoryImage', 'brandLogo', 'brandProductImage', 'imageFiles', 'identity_image', 'license_image', 'productImage', 'productPhoto'];
       foreach($largeKeys as $lk) unset($detailsPayload[$lk]);
       
       // Combine into new vs previous structure if available
       $finalDetails = $previousData ? ['new' => $detailsPayload, 'previous' => $previousData] : $detailsPayload;
       $details = mysqli_real_escape_string($con, json_encode($finalDetails, JSON_UNESCAPED_UNICODE));
       
       // Explicitly use PHP's IST date to avoid DB timezone issues
       $timestamp = date("Y-m-d H:i:s");
       
       $audit_query = "INSERT INTO `activity_log` (`staff_username`, `action_type`, `details`, `timestamp`) VALUES ('$log_admin_username', '$action_type', '$details', '$timestamp')";
       mysqli_query($con, $audit_query);
   }




   // -------------------------

//    echo "hello"; die();

   function base64_to_jpeg($base64_string, $output_file) {
    $ifp = fopen( $output_file, 'wb' ); 
    $data = explode( ',', $base64_string );
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );
    fclose( $ifp ); 
    return $output_file; 
    }




    //   if($type=='adminLogin'){
       
//     $username=$_POST['username'];
//     $password=$_POST['password'];
//     $query ="SELECT * FROM `admin` WHERE username ='$username' AND password ='$password'";
//     $run=mysqli_query($con,$query);
//     if(mysqli_num_rows($run) >0){
//         echo "success";
//     }else{
//         echo "error";
//     }

//     }

if($type == 'adminLogin'){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // ======== IP ADDRESS DETECT CODE ==========
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // Current Date-Time
    $date = date("Y-m-d H:i:s");
    // Status
    $status = "Login";

    $query = "SELECT * FROM `admin` WHERE username='$username' AND password='$password'";
    $run = mysqli_query($con, $query);

    if(mysqli_num_rows($run) > 0){
        $adminData = mysqli_fetch_assoc($run);
        
        $admin_status = isset($adminData['status']) ? $adminData['status'] : 1;
        $admin_role = isset($adminData['role']) ? $adminData['role'] : 'admin';
        $role_id = isset($adminData['role_id']) ? $adminData['role_id'] : 0;
        $admin_permissions = isset($adminData['permissions']) ? $adminData['permissions'] : 'all';

        if($admin_status == 0 || $admin_status == '0') {
            echo json_encode(["status" => "disabled"]);
        } else {
            // Set Server-Side Session
            $_SESSION['admin_login_status'] = true;
            $_SESSION['admin_role'] = $admin_role;
            $_SESSION['role_id'] = $role_id;
            $_SESSION['admin_permissions'] = $admin_permissions;
            $_SESSION['admin_username'] = $adminData['username'];

            // Insert Login IP History
            mysqli_query($con, "INSERT INTO admin_login_history (ip_address, date, status) VALUES ('$ip', '$date', '$status')");

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "success",
                "role" => $admin_role,
                "role_id" => $role_id,
                "permissions" => $admin_permissions,
                "username" => $adminData['username'],
                "session_id" => session_id()
            ]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error"]);
    }
}

else if ($type == 'checkAuth') {
    ob_clean();
    header('Content-Type: application/json');
    if (isset($_SESSION['admin_login_status']) && $_SESSION['admin_login_status'] === true) {
        echo json_encode([
            "status" => "success",
            "role" => $_SESSION['admin_role'],
            "permissions" => $_SESSION['admin_permissions'],
            "username" => $_SESSION['admin_username']
        ]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    exit;
}

else if ($type == 'logout') {
    session_destroy();
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(["status" => "success"]);
    exit;
}

    
    else if($type=='addCategory'){
    $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']);
    $categoryImage=$_POST['categoryImage'];
    $imageExtension=$_POST['imageExtension'];
    $date=date("Y-m-d H:i:s");
    $staff_username = isset($_POST['log_admin_username']) ? mysqli_real_escape_string($con, $_POST['log_admin_username']) : 'Admin';
   
    $img_nm = date('Ymdhis').".$imageExtension";
    $imgurlbase64 = $categoryImage;
    $imgurl = 'api/uploads/'.$img_nm;
    $image = base64_to_jpeg( $imgurlbase64 , 'uploads/'.$img_nm);

    $query ="INSERT INTO `category`(`name`, `image_path`, `status`,`date`, `added_by`) VALUES ('$categoryName','$imgurl','true','$date', '$staff_username')";
    $run=mysqli_query($con,$query);
    if($run){
        echo "success";
    }else{
        echo "error";
    }

    }

    else if($type == 'loadCategory'){

    // ✅ Query: category ke sath product count
    $query = "
        SELECT 
            c.*, 
            COUNT(p.p_id) AS product_count
        FROM 
            category c
        LEFT JOIN 
            product p 
        ON 
            p.under_category = c.id
        GROUP BY 
            c.id
        ORDER BY 
            c.id DESC
    ";

    $run = mysqli_query($con, $query);

    if($run){
        $rows = [];
        while($row = mysqli_fetch_assoc($run)){
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo "error";
    }
    }

    else if ($type == 'loadActivityLogs') {
        // Fetch last 20 actions for dashboard
        $query = "SELECT * FROM `activity_log` ORDER BY id DESC LIMIT 20";
        $run = mysqli_query($con, $query);
        $data = [];
        if ($run) {
            while ($row = mysqli_fetch_assoc($run)) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
    }
    else if ($type == 'loadActivityReport') {
        // Full report with optional date filtering
        $query = "SELECT * FROM `activity_log` WHERE 1=1";
        
        if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
            $start_date = mysqli_real_escape_string($con, $_POST['start_date']) . ' 00:00:00';
            $query .= " AND timestamp >= '$start_date'";
        }
        if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
            $end_date = mysqli_real_escape_string($con, $_POST['end_date']) . ' 23:59:59';
            $query .= " AND timestamp <= '$end_date'";
        }
        
        $query .= " ORDER BY id DESC";
        $run = mysqli_query($con, $query);
        $data = [];
        if ($run) {
            while ($row = mysqli_fetch_assoc($run)) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
    }


    else if($type=='updateCategory'){
        $typeStatus=$_POST['typeStatus'];
        $id=$_POST['id'];
        $statusText=$_POST['statusText'];
        $query ="UPDATE `category` SET $typeStatus='$statusText' WHERE id ='$id'";
        $run=mysqli_query($con,$query);
        if($run){
            echo "success";
             $query2 ="UPDATE `product` SET status='$statusText' WHERE under_category ='$id'";
             $run2=mysqli_query($con,$query2);
        }else{
            echo "error";
        }
    }



    // sub category section

   else if ($type =='addSubCategory'){
    $underCategory = mysqli_real_escape_string($con, $_POST['underCategory']);
    $middleCategory = mysqli_real_escape_string($con, $_POST['middleCategory']);
    $subCategoryName = mysqli_real_escape_string($con, $_POST['subCategoryName']);
    $subCategoryImage = $_POST['subCategoryImage'];
    $imageExtension = $_POST['imageExtension'];
    $date = date("Y-m-d H:i:s");
    $staff_username = isset($_POST['log_admin_username']) ? mysqli_real_escape_string($con, $_POST['log_admin_username']) : 'Admin';

    $img_nm = date('Ymdhis').".$imageExtension";
    $imgurlbase64 = $subCategoryImage;
    $imgurl = 'api/uploads/'.$img_nm;
    $image = base64_to_jpeg($imgurlbase64 , 'uploads/'.$img_nm);

    $query = "INSERT INTO `subcategory`(`under_category`,`middle_category`, `name`, `image_path`,`status`) 
              VALUES ('$underCategory','$middleCategory','$subCategoryName','$imgurl','true')";
    // echo $query; exit(); // Debugging line to check the query
    $run = mysqli_query($con, $query);
    
    if($run){
        echo "success";
    }else{
        echo "error"; // Show actual error (useful for debugging)
    }
}


  else if($type == 'loadSubCategory'){

    $query = "SELECT
        s.*,
        c.name AS category_name,
        mc.name AS middle_category_name,
        COUNT(p.p_id) AS product_count
    FROM subcategory s
    LEFT JOIN category c
        ON s.under_category = c.id
    LEFT JOIN middle_category mc
        ON s.middle_category = mc.id
    LEFT JOIN product p
        ON p.under_subcategory = s.id
    GROUP BY s.id
    ORDER BY s.id DESC
    ";

    $run = mysqli_query($con, $query);

    if($run){
        $rows = [];
        while($row = mysqli_fetch_assoc($run)){
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo "error";
    }
    }

    else if($type == "loadMiddleCategoryList"){
         $categoryid=$_POST['categoryid'];
        $query="SELECT * FROM `middle_category` WHERE `under_category` = '$categoryid' AND `status` ='true'";
    
        $run=mysqli_query($con,$query);
    
        if(mysqli_num_rows($run)>0){
            while($row=mysqli_fetch_assoc($run)){
                $rows[]=$row;
            }; 	
            echo json_encode($rows);
        }else{
            echo "error";
        }
    }

    else if($type =='loadSubCategoryList'){
        $categoryId=$_POST['categoryid'];
        $query="SELECT * FROM `subcategory` WHERE `middle_category` = '$categoryId' AND `status` ='true'";
    
        $run=mysqli_query($con,$query);
    
        if(mysqli_num_rows($run)>0){
            while($row=mysqli_fetch_assoc($run)){
                $rows[]=$row;
            }; 	
            echo json_encode($rows);
        }else{
            echo "error";
        }
        }
        
  
        else if($type =='loadAllSubCategoryList'){
        // $categoryid=$_POST['categoryid'];
        $query="SELECT * FROM `subcategory` WHERE `status`='true'";
    
        $run=mysqli_query($con,$query);
    
        if(mysqli_num_rows($run)>0){
            while($row=mysqli_fetch_assoc($run)){
                $rows[]=$row;
            }; 	
            echo json_encode($rows);
        }else{
            echo "error";
        }
        }

      else if($type == 'updateSubCategory'){
        $typeStatus = $_POST['typeStatus'];  // e.g., "status" ya koi aur field
        $id = $_POST['id'];                  // subcategory id
        $statusText = $_POST['statusText'];  // true / false
    
        // 🔹 Subcategory table update
        $query = "UPDATE `subcategory` SET $typeStatus = '$statusText' WHERE id = '$id'";
        $run = mysqli_query($con, $query);
    
        if($run){
            // ✅ Agar status field update hua hai to product table me bhi update karo
            if($typeStatus == 'status'){
                $updateProducts = "UPDATE `product` SET status = '$statusText' WHERE under_subcategory = '$id'";
                mysqli_query($con, $updateProducts);
            }
    
            echo "success";
        } else {
            echo "error";
        }
      }



        // add brand section 


      else if ($type == 'addBrands') {
        $categoryId = mysqli_real_escape_string($con, $_POST['categoryId']);
    $brandName = mysqli_real_escape_string($con, $_POST['brandName']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $brandLogo = $_POST['brandLogo'];
    $brandProductImage = $_POST['brandProductImage'];
    $imageExtension1 = $_POST['imageExtension1'];
    $imageExtension2 = $_POST['imageExtension2'];
    $date = date("Y-m-d H:i:s");

    // Create unique image names
    $img_nm1 = date('YmdHis') . "_logo." . $imageExtension1;
    $img_nm2 = date('YmdHis') . "_product." . $imageExtension2;

    $imgurl1 = 'api/uploads/' . $img_nm1;
    $imgurl2 = 'api/uploads/' . $img_nm2;

    $added_by = mysqli_real_escape_string($con, $_POST['log_admin_username'] ?? 'admin');

    // Save images from base64
    $logoSaved = base64_to_jpeg($brandLogo, 'uploads/' . $img_nm1);
    $productImgSaved = base64_to_jpeg($brandProductImage, 'uploads/' . $img_nm2);

    if ($logoSaved && $productImgSaved) {
        $query = "INSERT INTO `brands`(`name`, `description`, `logo_path`, `product_path`, `status`, `date`, `added_by`,`categoryId`) 
                  VALUES ('$brandName','$description','$imgurl1','$imgurl2','true','$date', '$added_by','$categoryId')";

        //  echo $query; exit(); // Debugging line to check the query
        $run = mysqli_query($con, $query);

        echo $run ? "success" : "error";
    } else {
        echo "image_upload_error";
    }
}


        else if ($type =='loadBrands'){
          
            $query="SELECT * FROM `brands` ORDER BY `id` DESC";

            $run=mysqli_query($con,$query);
        
            if($run){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                echo json_encode($rows);
            }else{
                echo "error";
            }
        }


        else if($type=='updateBrands'){
            $typeStatus=$_POST['typeStatus'];
            $id=$_POST['id'];
            $statusText=$_POST['statusText'];
            $query ="UPDATE `brands` SET $typeStatus='$statusText' WHERE id ='$id'";
            $run=mysqli_query($con,$query);
            if($run){
                echo "success";
            }else{
                echo "error";
            }
        }
        
       


        // add product section 

//       else if($type=='addProduct'){

//     // Escape all necessary inputs to prevent SQL errors
//     $productName = mysqli_real_escape_string($con, $_POST['productName'] ?? '');
//     $category = mysqli_real_escape_string($con, $_POST['category'] ?? '');
//     $subCategory = mysqli_real_escape_string($con, $_POST['subCategory'] ?? '');
//     $brandName = mysqli_real_escape_string($con, $_POST['brandName'] ?? '');
//     $mrp = $_POST['mrp'] ?? null;
//     $sellingPrice = $_POST['sellingPrice'] ?? null;
//     $purchasePrice = $_POST['purchasePrice'] ?? null;
//     $stock = $_POST['stock'] ?? null;
//     $quantity = $_POST['quantity'] ?? null;
//     $unit = mysqli_real_escape_string($con, $_POST['unit'] ?? '');
//     $review = $_POST['review'] ?? null;
//     $reviewNop = $_POST['reviewNop'] ?? null;
//     $skuNumber = mysqli_real_escape_string($con, $_POST['skuNumber'] ?? '');
//     $base64Image = $_POST['base64Image'] ?? null;
//     $fileExtension = $_POST['fileExtension'] ?? null;
//     $isvarient = $_POST['isvarint'] ?? null;

//     $informationData = isset($_POST['informationData']) ? mysqli_real_escape_string($con, json_encode($_POST['informationData'], true)) : '[]';
//     $highlightData = isset($_POST['highlightData']) ? mysqli_real_escape_string($con, json_encode($_POST['highlightData'], true)) : '[]';
//     $variantData = isset($_POST['variantData']) ? json_encode($_POST['variantData'], true) : [];

//     $imageFiles = isset($_POST['imageFiles']) ? $_POST['imageFiles'] : [];

//     $date = date("Y-m-d H:i:s");

//     $img_nm = date('Ymdhis').".$fileExtension";
//     $imgurl = 'api/uploads/'.$img_nm;
//     $image = base64_to_jpeg($base64Image , 'uploads/'.$img_nm);

//     $sql = "INSERT INTO `product`(`name`, `image_path`, `under_category`, `under_subcategory`, `brand_name`, `mrp`, `selling_price`, `purchase_price`, `stock`, `quantity`, `unit`, `review_val`, `review_nop`, `information`, `highlight`, `isvarient`,`date`,`status`,`sku_number`) 
//             VALUES ('$productName','$imgurl','$category','$subCategory','$brandName','$mrp','$sellingPrice','$purchasePrice','$stock','$quantity','$unit','$review','$reviewNop','$informationData','$highlightData','$isvarient','$date','true','$skuNumber')";

//     $run = mysqli_query($con, $sql);
//     $lastId = mysqli_insert_id($con);

//     if($run){
//         // Insert main product image
//         $imgquery = "INSERT INTO `product_img`(`product_id`, `image_path`) VALUES ('$lastId','$imgurl')";
//         mysqli_query($con, $imgquery);

//         // Insert variants if any
//         if (isset($_POST['variantData'])) {
//             $variantData = json_decode($_POST['variantData'], true);
//             if (is_array($variantData)) {
//                 foreach ($variantData as $variant) {
//                     $quantity = $variant['quantity'] ?? null;
//                     $unit = mysqli_real_escape_string($con, $variant['unit'] ?? '');
//                     $mrp = $variant['mrp'] ?? null;
//                     $sellingPrice = $variant['sellingPrice'] ?? null;
//                     $purchasePrice = $variant['purchasePrice'] ?? null;
//                     $stock = $variant['stock'] ?? null;

//                     $addVariant = "INSERT INTO `varient`(`v_mrp`, `product_id`, `v_seliing_price`, `v_purchase_price`, `v_stock`, `v_quantity`, `v_unit`) 
//                                   VALUES ('$mrp', '$lastId', '$sellingPrice', '$purchasePrice', '$stock', '$quantity', '$unit')";
//                     $run1 = mysqli_query($con, $addVariant); 
//                     if (!$run1) {
//                         echo "Error inserting variant: " . mysqli_error($con);
//                         exit;
//                     }
//                 }
//             }
//         }

//         // Insert multiple product images
//         if (!empty($imageFiles)) {
//             $imageData = json_decode($imageFiles, true);
//             foreach ($imageData as $index => $imageFile) {
//                 $fileName = mysqli_real_escape_string($con, $imageFile['name']);
//                 $base64Data = $imageFile['data'];
//                 $imgExtension = $imageFile['imgExtentsion'];
//                 $uniqueFileName = "image_" . time() . "_$index.$imgExtension";
//                 $imgurl = 'api/uploads/' . $uniqueFileName;
//                 $image = base64_to_jpeg($base64Data, 'uploads/'.$uniqueFileName);

//                 $multiimgquery = "INSERT INTO `product_img`(`product_id`, `image_path`) VALUES ('$lastId','$imgurl')";
//                 $runmultiimgquery = mysqli_query($con, $multiimgquery);
//             }
//         }

//         echo "success";
//     } else {
//         echo "error: " . mysqli_error($con);
//     }
// }

         else if ($type == 'addProduct') {

    // Ensure UTF-8 encoding
    mysqli_set_charset($con, "utf8mb4");

    
    // Escape inputs
    $productName   = mysqli_real_escape_string($con, $_POST['productName'] ?? '');
    $category      = mysqli_real_escape_string($con, $_POST['category'] ?? '');
    $middleCategory      = mysqli_real_escape_string($con, $_POST['middleCategory'] ?? '');
    $subCategory   = mysqli_real_escape_string($con, $_POST['subCategory'] ?? '');
    $brandName     = mysqli_real_escape_string($con, $_POST['brandName'] ?? '');
    $mrp           = $_POST['mrp'] ?? null;
    $sellingPrice  = $_POST['sellingPrice'] ?? null;
    $purchasePrice = $_POST['purchasePrice'] ?? null;
    // $stock         = $_POST['stock'] ?? null;
    $quantity      = $_POST['quantity'] ?? null;
    $unit          = mysqli_real_escape_string($con, $_POST['unit'] ?? '');
    $review        = $_POST['review'] ?? null;
    $reviewNop     = $_POST['reviewNop'] ?? null;
    $skuNumber     = mysqli_real_escape_string($con, $_POST['skuNumber'] ?? '');
    $product_limit = $_POST['product_limit'] ?? null;;
    $base64Image   = $_POST['base64Image'] ?? null;
    $fileExtension = $_POST['fileExtension'] ?? null;
    $product_keyword   = mysqli_real_escape_string($con, $_POST['product_keyword'] ?? '');

    // ✅ Encode JSON safely with Unicode support
    $informationData = isset($_POST['informationData'])
        ? mysqli_real_escape_string($con, json_encode($_POST['informationData'], JSON_UNESCAPED_UNICODE))
        : '[]';

    $highlightData = isset($_POST['highlightData'])
        ? mysqli_real_escape_string($con, json_encode($_POST['highlightData'], JSON_UNESCAPED_UNICODE))
        : '[]';

    $variantData = isset($_POST['variantData']) ? json_decode($_POST['variantData'], true) : [];
    $imageFiles  = isset($_POST['imageFiles']) ? $_POST['imageFiles'] : [];

    $date = date("Y-m-d H:i:s");

    // Main Image
    $img_nm = date('YmdHis') . ".$fileExtension";
    $imgurl = 'api/uploads/' . $img_nm;
    $image  = base64_to_jpeg($base64Image, 'uploads/' . $img_nm);

    $staff_username = isset($_POST['log_admin_username']) ? mysqli_real_escape_string($con, $_POST['log_admin_username']) : 'Admin';


    // ✅ Insert product
    $sql = "INSERT INTO `product`
            (`name`, `image_path`, `under_category`,`under_middle_category`, `under_subcategory`, `brand_name`, 
             
             `review_val`, `review_nop`, `information`, `highlight`, `isvarient`, `date`, `status`, `sku_number`,`p_limit`,`keyword`,`added_by`) 
            VALUES (
                '$productName', '$imgurl', '$category','$middleCategory', '$subCategory', '$brandName',
                
                '$review', '$reviewNop', '$informationData', '$highlightData', 'true',
                '$date', 'true', '$skuNumber','$product_limit','$product_keyword','$staff_username'
            )";
//  echo $sql; die();
    $run = mysqli_query($con, $sql);
   
    $lastId = mysqli_insert_id($con);

    if ($run) {
        // Insert main image
        $imgquery = "INSERT INTO `product_img`(`product_id`, `image_path`) VALUES ('$lastId','$imgurl')";
        mysqli_query($con, $imgquery);

                $addVariant = "INSERT INTO `varient`
                               (`v_mrp`, `product_id`, `v_seliing_price`, `v_purchase_price`, `v_quantity`, `v_unit`,`v_p_limit`)
                               VALUES ('$mrp', '$lastId', '$sellingPrice', '$purchasePrice', '$quantity', '$unit','')"; 
                $run1 = mysqli_query($con, $addVariant);

                if($run1){

                    // Newly created Variant ID
                    $variantId = mysqli_insert_id($con);

                    // Add stock entry for every branch
                    $query = "INSERT INTO `branch_stock`
                        (branch_id, product_id, varient_id, stock, v_mrp, v_seliing_price, v_purchase_price)
                    SELECT
                        id,
                        '$lastId',
                        '$variantId',
                        '0',
                        '$mrp',
                        '$sellingPrice',
                        '$purchasePrice'
                        
                    FROM branch";

                

                    $run2 = mysqli_query($con, $query);

                    if($run2){
                        echo "Success";
                    }else{
                        echo mysqli_error($con);
                    }
                }

        // Insert multiple product images
        if (!empty($imageFiles)) {
            $imageData = json_decode($imageFiles, true);
            foreach ($imageData as $index => $imageFile) {
                $fileName      = mysqli_real_escape_string($con, $imageFile['name']);
                $base64Data    = $imageFile['data'];
                $imgExtension  = $imageFile['imgExtentsion'];
                $uniqueFileName = "image_" . time() . "_$index.$imgExtension";
                $imgurl = 'api/uploads/' . $uniqueFileName;
                $image  = base64_to_jpeg($base64Data, 'uploads/' . $uniqueFileName);

                $multiimgquery = "INSERT INTO `product_img`(`product_id`, `image_path`) VALUES ('$lastId','$imgurl')";
                mysqli_query($con, $multiimgquery);
            }
        }

        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
}

        
        else if($type=='addMoreImages'){

            // $type = $_POST['type'] ?? null;
            $lastId = $_POST['p_id'] ?? null;
            $imageFiles = isset($_POST['imageFiles']) ? json_encode($_POST['imageFiles'], true) : [];
            
                $uploadedImages = [];
                if (!empty($imageFiles)) {
                    $imageData = json_decode($imageFiles, true);
                    foreach ($imageData as $index => $imageFile) {
                        $fileName = $imageFile['name'];
                        $base64Data = $imageFile['data'];
                        $imgExtension = $imageFile['imgExtentsion'];

                        // Generate a unique filename
                        $uniqueFileName = "image_" . time() . "_$index.$imgExtension";

                        // Save the image using the base64_to_image function
                        $imgurl = 'api/uploads/' . $uniqueFileName;
                        $image = base64_to_jpeg($base64Data, 'uploads/'.$uniqueFileName);
                       
                        $multiimgquery = "INSERT INTO `product_img`(`product_id`, `image_path`) VALUES ('$lastId','$imgurl')";
                        $runmultiimgquery = mysqli_query($con, $multiimgquery);
                        if ($runmultiimgquery) {
                            echo "success";
                        } else { 
                            echo "Failed to insert image: $imgurl";
                        }
                    }
                }else{
                    echo "no image files found";
                }












        }

        else if($type=='loadProduct'){
            $query="SELECT * FROM `product` ORDER BY `p_id` DESC";

            $run=mysqli_query($con,$query);
        
            if($run){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                echo json_encode($rows);
            }else{
                echo "error";
            }
        }

        
        else if($type=='loadPosProduct'){
            $query="SELECT * FROM `product` ORDER BY `p_id` DESC";

            $run=mysqli_query($con,$query);
        
            if($run){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                   echo json_encode([
                    "status"=>"success",
                    "message"=>"load pos products !",
                    "data"=>$rows
                ]); 
                }else{
                 echo json_encode([
                    "status"=>"failed",
                    "message"=>"something wents wrong ! " . mysqli_error($con),
                    "data"=>[]
                ]);            
                }
        }
        else if($type =="loadPosBranchproduct"){
            $branchId=$_POST['branchId'];
           $query = "SELECT
            p.*,
            SUM(bs.stock) AS total_stock
        FROM branch_stock bs
        INNER JOIN product p
            ON bs.product_id = p.p_id
        WHERE
            bs.branch_id = '$branchId'
            AND bs.stock > 0
        GROUP BY bs.product_id";
            
            // echo $query; exit();
            $res=mysqli_query($con,$query);
            if(mysqli_num_rows($res)>0){
                $data=[];
                while ($row=mysqli_fetch_assoc($res)) {
                     $data[]=$row;
                }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"load branch products !",
                    "data"=>$data
                ]);
            }else{
                echo json_encode([
                    "status"=>"failed",
                    "message"=>"something wents wrong ! ",
                    "data"=>[]
                ]);
            }
        }

        else if($type=='seeAllInfo'){
            $p_id=$_POST['p_id'];
            $query="SELECT * FROM `product` WHERE `p_id` = '$p_id'";

            $run=mysqli_query($con,$query);
        
            if($run){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                echo json_encode($rows);
            }else{
                echo "error";
            }
        }

        else if($type=='seeImages'){
            $p_id=$_POST['p_id'];
            $query="SELECT * FROM `product_img` WHERE `product_id` =$p_id";

            $run=mysqli_query($con,$query);
        
            if($run){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                echo json_encode($rows);
            }else{
                echo "error";
            }
        }

         else if($type=='seeVarient'){
            $p_id=$_POST['p_id'];
            $branchId=$_POST['branchId'];
            if($branchId == 0){
              $query="SELECT * FROM `varient` WHERE product_id ='$p_id'";
            }else{
            $query = "SELECT
                v.v_unit,
                v.v_p_limit,
                v.v_quantity,
                bs.stock,
                bs.v_purchase_price,
                bs.v_seliing_price,
                bs.v_mrp,
                bs.varient_id as vid,
                bs.product_id
            FROM varient v
            LEFT JOIN branch_stock bs
                ON bs.varient_id = v.vid
                AND bs.product_id = v.product_id
                AND bs.branch_id = '$branchId'
            WHERE v.product_id = '$p_id'
            ";
            }
            
            // echo $query; exit();
            $run=mysqli_query($con,$query);
        
            if($run){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                echo json_encode($rows);
            }else{
                echo "error";
            }
        }

        else if($type=='updateProductTitle'){
            $typeStatus=$_POST['typeStatus'];
            $id=$_POST['id'];
            $statusText=$_POST['statusText'];
            $query ="UPDATE `product` SET $typeStatus='$statusText' WHERE p_id ='$id'";
            $run=mysqli_query($con,$query);
            if($run){
                echo "success";
            }else{
                echo "error";
            }
        }

        // else if($type=='deleteVarientImage'){
        //     $img_id=$_POST['img_id'];
        //     $query ="DELETE FROM `product_img` WHERE `img_id`='$img_id'";
        //     $run=mysqli_query($con,$query);
        //     if($run){
        //         echo "success";
        //     }else{
        //         echo "error";
        //     }
        // }
        
        else if($type=='deleteVarientImage'){
        $img_id = $_POST['img_id'];
    
        // Step 1: DB se image_path nikal lo
        $getImg = mysqli_query($con, "SELECT image_path FROM product_img WHERE img_id='$img_id'");
        if ($getImg && mysqli_num_rows($getImg) > 0) {
            $row = mysqli_fetch_assoc($getImg);
            $imgPath = $row['image_path']; // e.g. api/uploads/20250906123456.png
    
            // Step 2: "api/" ko remove karo sirf file deletion ke liye
            $filePath = __DIR__ . "/" . preg_replace('#^api/#', '', $imgPath); // uploads/20250906123456.png
    
            // Step 3: Agar file exist karti hai to delete karo
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

            // Step 4: DB se row delete karo
            $query = "DELETE FROM product_img WHERE img_id='$img_id'";
            $run = mysqli_query($con, $query);
        
            if ($run) {
                echo "success";
            } else {
                echo "error";
            }
        }

        else if($type == 'deleteCategory'){
            $id = mysqli_real_escape_string($con, $_POST['id']);
            
            // --- 1. Delete ALL related PRODUCTS and their data ---
            $getProds = mysqli_query($con, "SELECT p_id, image_path FROM `product` WHERE `under_category`='$id'");
            if ($getProds && mysqli_num_rows($getProds) > 0) {
                while($prod = mysqli_fetch_assoc($getProds)){
                    $p_id = $prod['p_id'];
                    $imgPath = $prod['image_path'];
                    
                    // Delete Product Image File
                    if ($imgPath) {
                        $pFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $imgPath);
                        if(file_exists($pFilePath)) unlink($pFilePath);
                    }
                    
                    // Delete Variants & Cart items for this product
                    mysqli_query($con, "DELETE FROM `varient` WHERE `product_id`='$p_id'");
                    mysqli_query($con, "DELETE FROM `cart` WHERE `p_id`='$p_id' AND `status`='true'");
                }
                // Finally Delete the products from DB
                mysqli_query($con, "DELETE FROM `product` WHERE `under_category`='$id'");
            }
            
            // --- 2. Delete ALL related SUBCATEGORIES ---
            $getSubCats = mysqli_query($con, "SELECT id, image_path FROM `subcategory` WHERE `under_category`='$id'");
            if ($getSubCats && mysqli_num_rows($getSubCats) > 0) {
                while($subcat = mysqli_fetch_assoc($getSubCats)){
                    $imgPath = $subcat['image_path'];
                    if ($imgPath) {
                        $scFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $imgPath);
                        if(file_exists($scFilePath)) unlink($scFilePath);
                    }
                }
                // Delete Subcategories from DB
                mysqli_query($con, "DELETE FROM `subcategory` WHERE `under_category`='$id'");
            }
            
            // --- 3. Delete the CATEGORY itself ---
            $getCat = mysqli_query($con, "SELECT image_path FROM `category` WHERE `id`='$id'");
            if ($getCat && mysqli_num_rows($getCat) > 0) {
                $row = mysqli_fetch_assoc($getCat);
                if ($row['image_path']) {
                    $cFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $row['image_path']);
                    if(file_exists($cFilePath)) unlink($cFilePath);
                }
            }
            
            $run = mysqli_query($con, "DELETE FROM `category` WHERE `id`='$id'");
            if($run) echo "success";
            else echo "error: " . mysqli_error($con);
        }

        else if($type == 'deleteSubCategory'){
            $id = mysqli_real_escape_string($con, $_POST['id']);
            
            // --- 1. Delete ALL related PRODUCTS and their data ---
            $getProds = mysqli_query($con, "SELECT p_id, image_path FROM `product` WHERE `under_subcategory`='$id'");
            if ($getProds && mysqli_num_rows($getProds) > 0) {
                while($prod = mysqli_fetch_assoc($getProds)){
                    $p_id = $prod['p_id'];
                    $imgPath = $prod['image_path'];
                    
                    // Delete Product Image File
                    if ($imgPath) {
                        $pFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $imgPath);
                        if(file_exists($pFilePath)) unlink($pFilePath);
                    }
                    
                    // Delete Variants & Cart items for this product
                    mysqli_query($con, "DELETE FROM `varient` WHERE `product_id`='$p_id'");
                    mysqli_query($con, "DELETE FROM `cart` WHERE `p_id`='$p_id' AND `status`='true'");
                }
                // Delete the products from DB
                mysqli_query($con, "DELETE FROM `product` WHERE `under_subcategory`='$id'");
            }
            
            // --- 2. Delete the SUBCATEGORY itself ---
            $getSubCat = mysqli_query($con, "SELECT image_path FROM `subcategory` WHERE `id`='$id'");
            if ($getSubCat && mysqli_num_rows($getSubCat) > 0) {
                $row = mysqli_fetch_assoc($getSubCat);
                if ($row['image_path']) {
                    $scFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $row['image_path']);
                    if(file_exists($scFilePath)) unlink($scFilePath);
                }
            }
            
            $run = mysqli_query($con, "DELETE FROM `subcategory` WHERE `id`='$id'");
            if($run) echo "success";
            else echo "error: " . mysqli_error($con);
        }

        else if($type == 'swapCategoryIds'){
            $id1 = mysqli_real_escape_string($con, $_POST['id1']);
            $id2 = mysqli_real_escape_string($con, $_POST['id2']);
            
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            // Fetch temp id explicitly avoiding existing ones
            $tempId = "999999" . rand(100, 999); 
            
            mysqli_autocommit($con, false);
            try {
                // 1. Move id1 out of the way to tempId
                mysqli_query($con, "UPDATE `category` SET `id`='$tempId' WHERE `id`='$id1'");
                mysqli_query($con, "UPDATE `subcategory` SET `under_category`='$tempId' WHERE `under_category`='$id1'");
                mysqli_query($con, "UPDATE `product` SET `under_category`='$tempId' WHERE `under_category`='$id1'");
                mysqli_query($con, "UPDATE `banner` SET `under_category`='$tempId' WHERE `under_category`='$id1'");
                mysqli_query($con, "UPDATE `hero_banner` SET `under_category`='$tempId' WHERE `under_category`='$id1'");

                // 2. Move id2 to id1
                mysqli_query($con, "UPDATE `category` SET `id`='$id1' WHERE `id`='$id2'");
                mysqli_query($con, "UPDATE `subcategory` SET `under_category`='$id1' WHERE `under_category`='$id2'");
                mysqli_query($con, "UPDATE `product` SET `under_category`='$id1' WHERE `under_category`='$id2'");
                mysqli_query($con, "UPDATE `banner` SET `under_category`='$id1' WHERE `under_category`='$id2'");
                mysqli_query($con, "UPDATE `hero_banner` SET `under_category`='$id1' WHERE `under_category`='$id2'");

                // 3. Move tempId (originally id1) to id2
                mysqli_query($con, "UPDATE `category` SET `id`='$id2' WHERE `id`='$tempId'");
                mysqli_query($con, "UPDATE `subcategory` SET `under_category`='$id2' WHERE `under_category`='$tempId'");
                mysqli_query($con, "UPDATE `product` SET `under_category`='$id2' WHERE `under_category`='$tempId'");
                mysqli_query($con, "UPDATE `banner` SET `under_category`='$id2' WHERE `under_category`='$tempId'");
                mysqli_query($con, "UPDATE `hero_banner` SET `under_category`='$id2' WHERE `under_category`='$tempId'");

                mysqli_commit($con);
                echo "success";
            } catch (Exception $e) {
                mysqli_rollback($con);
                echo "error: " . $e->getMessage();
            }
            mysqli_autocommit($con, true);
        }

        else if($type == 'swapSubCategoryIds'){
            $id1 = mysqli_real_escape_string($con, $_POST['id1']);
            $id2 = mysqli_real_escape_string($con, $_POST['id2']);
            
            $tempId = "999999" . rand(100, 999); 
            
            mysqli_autocommit($con, false);
            try {
                // 1. Move id1 out of the way to tempId
                mysqli_query($con, "UPDATE `subcategory` SET `id`='$tempId' WHERE `id`='$id1'");
                mysqli_query($con, "UPDATE `product` SET `under_subcategory`='$tempId' WHERE `under_subcategory`='$id1'");
                mysqli_query($con, "UPDATE `banner` SET `uder_subcategory`='$tempId' WHERE `uder_subcategory`='$id1'");
                mysqli_query($con, "UPDATE `hero_banner` SET `under_subcategory`='$tempId' WHERE `under_subcategory`='$id1'");

                // 2. Move id2 to id1
                mysqli_query($con, "UPDATE `subcategory` SET `id`='$id1' WHERE `id`='$id2'");
                mysqli_query($con, "UPDATE `product` SET `under_subcategory`='$id1' WHERE `under_subcategory`='$id2'");
                mysqli_query($con, "UPDATE `banner` SET `uder_subcategory`='$id1' WHERE `uder_subcategory`='$id2'");
                mysqli_query($con, "UPDATE `hero_banner` SET `under_subcategory`='$id1' WHERE `under_subcategory`='$id2'");

                // 3. Move tempId (originally id1) to id2
                mysqli_query($con, "UPDATE `subcategory` SET `id`='$id2' WHERE `id`='$tempId'");
                mysqli_query($con, "UPDATE `product` SET `under_subcategory`='$id2' WHERE `under_subcategory`='$tempId'");
                mysqli_query($con, "UPDATE `banner` SET `uder_subcategory`='$id2' WHERE `uder_subcategory`='$tempId'");
                mysqli_query($con, "UPDATE `hero_banner` SET `under_subcategory`='$id2' WHERE `under_subcategory`='$tempId'");

                mysqli_commit($con);
                echo "success";
            } catch (Exception $e) {
                mysqli_rollback($con);
                echo "error: " . $e->getMessage();
            }
            mysqli_autocommit($con, true);
        }


        // else if($type=='deleteProduct'){
        //     $p_id=$_POST['p_id'];
        //     $query ="DELETE FROM `product` WHERE `p_id`='$p_id'";
        //     $run=mysqli_query($con,$query);
        //     if($run){
        //         echo "success";
        //     }else{
        //         echo "error";
        //     }
        // }

            else if($type == 'deleteProduct'){
            $p_id = mysqli_real_escape_string($con, $_POST['p_id']);
        
            // 🔹 Step 1: Purani image ka path nikal lo
            $getImg = mysqli_query($con, "SELECT image_path FROM product WHERE p_id='$p_id'");
            if ($getImg && mysqli_num_rows($getImg) > 0) {
                $row = mysqli_fetch_assoc($getImg);
                $imgPath = $row['image_path']; // e.g. api/uploads/20250906150000.png
        
                // 🔹 Step 2: "api/" ko remove karo sirf file deletion ke liye
                $filePath = __DIR__ . "/" . preg_replace('#^api/#', '', $imgPath);
        
                // 🔹 Step 3: Agar file exist karti hai to delete karo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        
            // 🔹 Step 4: Product ke variants bhi delete karo (agar exist kare)
            mysqli_query($con, "DELETE FROM `varient` WHERE `product_id`='$p_id'");
        
            // 🔹 Step 5: Cart table me is product ke status=true wale items delete karo
            mysqli_query($con, "DELETE FROM `cart` WHERE `p_id`='$p_id' AND `status`='true'");
            // delete from branc_stock
            mysqli_query($con,"DELETE FROM `branch_stock` WHERE `product_id` = '$p_id'");
        
            // 🔹 Step 6: Product table se row delete karo
            $query = "DELETE FROM `product` WHERE `p_id`='$p_id'";

            $run = mysqli_query($con, $query);
        
            if ($run) {
                echo "success";
            } else {
                echo "error";
            }
        }




        // addBanner

        else if($type=='addBanner'){

            $bannerType=$_POST['bannerType'] ?? null;
            $category=$_POST['category'] ?? null;
            $middleCategory=$_POST['middleCategory'] ?? null;
            // $subCategory=$_POST['subCategory'] ?? null;
            $base64Image=$_POST['base64Image'];
            $imageExtension=$_POST['fileExtension'];
            $device=$_POST['device'];
            $date=date("Y-m-d H:i:s");
           
            $img_nm = date('Ymdhis').".$imageExtension";
            $added_by = mysqli_real_escape_string($con, $_POST['log_admin_username'] ?? 'admin');

            $imgurl = 'api/uploads/'.$img_nm;
            $image = base64_to_jpeg( $base64Image , 'uploads/'.$img_nm);
        
            $query ="INSERT INTO `banner`(`type`, `img_path`, `device`, `under_category`,`under_middle_category`, `status`, `date`, `added_by`) 
            VALUES ('$bannerType','$imgurl','$device','$category','$middleCategory','true','$date', '$added_by')";
            $run=mysqli_query($con,$query);
            if($run){
                echo "success";
            }else{
                echo "error";
            }
        }

        else if($type =='loadBanner'){

            $query="SELECT DISTINCT b.*, c.name AS category_name, 
             m.name AS middlecategory_name
             FROM banner b LEFT JOIN category c ON b.under_category = c.id 
             LEFT JOIN middle_category m ON b.under_middle_category = m.id 
             WHERE  b.under_category IS NOT NULL  
             OR b.under_middle_category IS NOT NULL ORDER BY b.b_id DESC";
        
            $run=mysqli_query($con,$query);
        
            if(mysqli_num_rows($run)>0){
                while($row=mysqli_fetch_assoc($run)){
                    $rows[]=$row;
                }; 	
                echo json_encode($rows);
            }else{
                echo "error";
            }
            }

            else if($type=='updateBanner'){
                $typeStatus=$_POST['typeStatus'];
                $id=$_POST['id'];
                $statusText=$_POST['statusText'];
                $query ="UPDATE `banner` SET $typeStatus='$statusText' WHERE b_id ='$id'";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
            }

            else if($type=='deleteBanber'){
                $b_id=$_POST['b_id'];
                $query ="DELETE FROM `banner` WHERE `b_id`='$b_id'";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
            }
            else if($type=='editBanner'){
                $b_id = mysqli_real_escape_string($con, $_POST['b_id'] ?? '');
                $bannerType = mysqli_real_escape_string($con, $_POST['bannerType'] ?? '');
                $category = mysqli_real_escape_string($con, $_POST['category'] ?? '');
                $middleCategory = mysqli_real_escape_string($con, $_POST['middleCategory'] ?? '');
                
                $base64Image = $_POST['base64Image'] ?? null;
                $imageExtension = $_POST['fileExtension'] ?? null;
                
                $catVal = ($category !== '') ? "'$category'" : "NULL";
                $middleCatVal = ($middleCategory !== '') ? "'$middleCategory'" : "NULL";
                
                $updateFields = "`type`='$bannerType', `under_category`=$catVal, `under_middle_category`=$middleCatVal";

                if ($base64Image && $imageExtension) {
                    $img_nm = date('Ymdhis').".".$imageExtension;
                    $imgurl = 'api/uploads/'.$img_nm;
                    $image = base64_to_jpeg( $base64Image , 'uploads/'.$img_nm);
                    $updateFields .= ", `img_path`='$imgurl'";
                }

                $query = "UPDATE `banner` SET $updateFields WHERE `b_id` = '$b_id'";
                $run = mysqli_query($con, $query);
                if($run){
                    echo "success";
                }else{
                    echo "error: " . mysqli_error($con);
                }
            }

            
            // else if($type=='loadAllUser'){
            //     // $b_id=$_POST['b_id'];
                
            //     $query ="SELECT * FROM `user` ORDER BY `user_id` DESC";
            //     $run=mysqli_query($con,$query);
            //     if(mysqli_num_rows($run)>0){
            //         while($row=mysqli_fetch_assoc($run)){
            //             $rows[]=$row;
            //         }; 	
            //         echo json_encode($rows);
            //     }else{
            //         echo "error";
            //     }
            // }
            
            else if ($type == 'loadAllUser') {

                $query = "
                    SELECT 
                        u.*,
                        COUNT(o.id) AS total_orders
                    FROM `user` u
                    LEFT JOIN `order` o 
                        ON o.user_id = u.mobile
                    GROUP BY u.user_id
                    ORDER BY u.user_id DESC
                ";
            
                $run = mysqli_query($con, $query);
            
                if ($run && mysqli_num_rows($run) > 0) {
                    $rows = [];
                    while ($row = mysqli_fetch_assoc($run)) {
                        $rows[] = $row;
                    }
                    echo json_encode($rows);
                } else {
                    echo json_encode([]);
                }
            }

            
            else if($type=='loadAllPosUser'){
                $branchId=$_POST['branchId'];
                
                $query ="SELECT * FROM `pos_user` WHERE `branch_id`='$branchId' ORDER BY `id` DESC";
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
            else if($type=='loadOrder'){  
                $query ="SELECT * FROM `order` ORDER BY `id` DESC"; 
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
              else if($type=='loadBranchOrder'){
                $branchId=$_POST['branchId'];
                $query="SELECT * FROM `order` WHERE `branch_id` = '$branchId' ORDER BY `id` DESC";
                // echo $query; exit();
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
            // else if($type=='loadAllUser'){
            //     // $b_id=$_POST['b_id'];
                
            //     $query ="SELECT * FROM `user` ORDER BY `id` DESC"; 
            //     $run=mysqli_query($con,$query);
            //     if(mysqli_num_rows($run)>0){
            //         while($row=mysqli_fetch_assoc($run)){
            //             $rows[]=$row;
            //         }; 	
            //         echo json_encode($rows);
            //     }else{
            //         echo "error";
            //     }
            // }
            
            else if($type=='viewOrderDetails'){
                
                $idfr=$_POST['idfr']; 
                
                $query ="SELECT * FROM `cart` WHERE status ='false' AND idfr='$idfr'";
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
            else if($type=='viewOrderUserDetails'){
                
                $user_id=$_POST['user_id']; 
                $address_id=$_POST['address_id']; 
                
                $query ="SELECT * FROM `user` AS a  LEFT JOIN location AS b ON a.mobile = b.user_id WHERE a.mobile ='$user_id' AND b.id = '$address_id'"; 
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
            //  edit category
//           else if($type == 'editCategoryWithImage'){
//     $cat_id = $_POST['cat_id']; 
//     $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']); 
//     $categoryImage = $_POST['categoryImage']; 
//     $imageExtension = $_POST['imageExtension']; 

//     $date = date("Y-m-d H:i:s");

//     $img_nm = date('Ymdhis') . ".$imageExtension";
//     $imgurlbase64 = $categoryImage;
//     $imgurl = 'api/uploads/' . $img_nm;
//     $image = base64_to_jpeg($imgurlbase64, 'uploads/' . $img_nm);

//     $query = "UPDATE `category` SET `name`='$categoryName', `image_path`='$imgurl' WHERE `id` = '$cat_id'";
//     $run = mysqli_query($con, $query);

//     if($run){
//         echo "success";
//     } else {
//         echo "error: " . mysqli_error($con); // error debugging
//     }
// }
            
            else if($type == 'editCategoryWithImage'){
            $cat_id = $_POST['cat_id']; 
            $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']); 
            $categoryImage = $_POST['categoryImage']; 
            $imageExtension = $_POST['imageExtension']; 
        
            $date = date("Y-m-d H:i:s");
        
            // 🔹 Step 1: Purani image ka path nikal lo
            $getOldImg = mysqli_query($con, "SELECT image_path FROM category WHERE id='$cat_id'");
            if ($getOldImg && mysqli_num_rows($getOldImg) > 0) {
                $row = mysqli_fetch_assoc($getOldImg);
                $oldImgPath = $row['image_path']; // e.g. api/uploads/20250906150000.png
        
                // "api/" ko remove karo aur full path banao
                $oldFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $oldImgPath);
        
                // Agar purani file exist hai to delete karo
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
        
            // 🔹 Step 2: Nayi image save karo
            $img_nm = date('Ymdhis') . ".$imageExtension";
            $imgurlbase64 = $categoryImage;
            $imgurl = 'api/uploads/' . $img_nm;
            $image = base64_to_jpeg($imgurlbase64, 'uploads/' . $img_nm);
        
            // 🔹 Step 3: DB update karo
            $query = "UPDATE `category` 
                      SET `name`='$categoryName', `image_path`='$imgurl' 
                      WHERE `id` = '$cat_id'";
            $run = mysqli_query($con, $query);
        
            if($run){
                echo "success";
            } else {
                echo "error: " . mysqli_error($con); // debugging ke liye
            }
        }


            
          else if($type == 'editCategoryName'){
    $cat_id = $_POST['cat_id']; 
    $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']); // Safe against quotes

    $query = "UPDATE `category` SET `name`='$categoryName' WHERE `id` = '$cat_id'";
    $run = mysqli_query($con, $query);

    if($run){
        echo "success";
    } else {
        echo "error: " . mysqli_error($con); // Helpful for debugging
    }
}


                //  edit subcategory
            else if($type == 'editSubCategoryWithImage'){
    $cat_id = $_POST['cat_id']; 
    $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']); 
    $categoryImage = $_POST['categoryImage']; 
    $imageExtension = $_POST['imageExtension']; 
    $under_category = mysqli_real_escape_string($con, $_POST['under_category']); 
    $middle_category = mysqli_real_escape_string($con, $_POST['middle_category']); 

    $date = date("Y-m-d H:i:s");

    $img_nm = date('Ymdhis') . ".$imageExtension";
    $imgurlbase64 = $categoryImage;
    $imgurl = 'api/uploads/' . $img_nm;
    $image = base64_to_jpeg($imgurlbase64, 'uploads/' . $img_nm);

    $query = "UPDATE `subcategory` SET `under_category`='$under_category', `name`='$categoryName', `image_path`='$imgurl', `middle_category`='$middle_category' WHERE `id`='$cat_id'";
    $run = mysqli_query($con, $query);

    if($run){
        echo "success";
    } else {
        echo "error: " . mysqli_error($con); // helpful for debugging
    }
}

            
          else if($type == 'editSubCategoryName'){
    $cat_id = $_POST['cat_id']; 
    $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']); 
    $under_category = mysqli_real_escape_string($con, $_POST['under_category']); 
    $middle_category = mysqli_real_escape_string($con, $_POST['middle_category']); 

    $query = "UPDATE `subcategory` SET `under_category`='$under_category', `name`='$categoryName', `middle_category`='$middle_category' WHERE `id`='$cat_id'";
    $run = mysqli_query($con, $query);

    if($run){
        echo "success";
    } else {
        echo "error: " . mysqli_error($con); // Optional debugging
    }
}

            
            
                //  edit brand
            else if($type == 'editBrandWithLogo') {
    $brand_id = $_POST['brand_id'];  
    $brandName = $_POST['brandName']; 
    $brandDesc = $_POST['brandDesc']; 

    $brandLogo = $_POST['brandLogo'] ?? null; 
    $imageExtension = $_POST['imageExtension'] ?? null;

    $productPhoto = $_POST['productPhoto'] ?? null;
    $imageExtension2 = $_POST['imageExtension2'] ?? null;

    $updateFields = "`name`='$brandName', `description`='$brandDesc'";

    // Handle logo
    if ($brandLogo && $imageExtension) {
        $img_nm1 = date('YmdHis') . rand(1000, 9999) . ".$imageExtension";
        $imgurl1 = 'api/uploads/' . $img_nm1;
        base64_to_jpeg($brandLogo, 'uploads/' . $img_nm1);
        $updateFields .= ", `logo_path`='$imgurl1'";
    }

    // Handle product photo
    if ($productPhoto && $imageExtension2) {
        $img_nm2 = date('YmdHis') . rand(1000, 9999) . ".$imageExtension2";
        $imgurl2 = 'api/uploads/' . $img_nm2;
        base64_to_jpeg($productPhoto, 'uploads/' . $img_nm2);
        $updateFields .= ", `product_path`='$imgurl2'";
    }

    // Final SQL query
    $query = "UPDATE `brands` SET $updateFields WHERE `id` = '$brand_id'";
    $run = mysqli_query($con, $query);

    if ($run) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
}

            
         else if($type == 'editBrand') {
    $brand_id = mysqli_real_escape_string($con, $_POST['brand_id']);  
    $brandName = mysqli_real_escape_string($con, $_POST['brandName']); 
    $brandDesc = mysqli_real_escape_string($con, $_POST['brandDesc']); 

    $query = "UPDATE `brands` SET `name`='$brandName', `description`='$brandDesc' WHERE `id`='$brand_id'";
    $run = mysqli_query($con, $query);

    if ($run) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
}

            
            
//     else if($type == 'updateProduct') {
//     $p_id = mysqli_real_escape_string($con, $_POST['p_id']);
//     $category = mysqli_real_escape_string($con, $_POST['category']);
//     $subCategory = mysqli_real_escape_string($con, $_POST['subCategory']);
//     $brandName = mysqli_real_escape_string($con, $_POST['brandName']);
//     $productName = mysqli_real_escape_string($con, $_POST['productName']);
//     $mrp = mysqli_real_escape_string($con, $_POST['mrp']);
//     $sellingPrice = mysqli_real_escape_string($con, $_POST['sellingPrice']);
//     $purchasePrice = mysqli_real_escape_string($con, $_POST['purchasePrice']);
//     $stock = mysqli_real_escape_string($con, $_POST['stock']);
//     $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
//     $unit = mysqli_real_escape_string($con, $_POST['unit']);
//     $review = mysqli_real_escape_string($con, $_POST['review']);
//     $reviewNop = mysqli_real_escape_string($con, $_POST['reviewNop']);
//     $skuNumber = mysqli_real_escape_string($con, $_POST['skuNumber']);

//     $base64Image = $_POST['base64Image'] ?? null;
//     $fileExtension = $_POST['fileExtension'] ?? null;
    
//     if ($base64Image) {
//         $img_nm = date('Ymdhis') . ".$fileExtension";
//         $imgurl = 'api/uploads/' . $img_nm;
//         base64_to_jpeg($base64Image, 'uploads/' . $img_nm);

//         $query = "UPDATE `product` SET 
//                     `name`='$productName',
//                     `image_path`='$imgurl',
//                     `under_category`='$category',
//                     `under_subcategory`='$subCategory',
//                     `brand_name`='$brandName',
//                     `mrp`='$mrp',
//                     `selling_price`='$sellingPrice',
//                     `purchase_price`='$purchasePrice',
//                     `stock`='$stock',
//                     `quantity`='$quantity',
//                     `unit`='$unit',
//                     `review_val`='$review',
//                     `review_nop`='$reviewNop',
//                     `sku_number`='$skuNumber'
//                 WHERE `p_id`='$p_id'";

//         $query2 = "SELECT img_id FROM `product_img` WHERE product_id = '$p_id' ORDER BY img_id ASC LIMIT 1";
//         $run2 = mysqli_query($con, $query2);

//         if (mysqli_num_rows($run2) > 0) {
//             $row = mysqli_fetch_assoc($run2);
//             $img_id = $row['img_id'];
//             $query3 = "UPDATE `product_img` SET `image_path`='$imgurl' WHERE `img_id`='$img_id'";
//             mysqli_query($con, $query3);
//         }
//     } else {
//         $query = "UPDATE `product` SET 
//                     `name`='$productName',
//                     `under_category`='$category',
//                     `under_subcategory`='$subCategory',
//                     `brand_name`='$brandName',
//                     `mrp`='$mrp',
//                     `selling_price`='$sellingPrice',
//                     `purchase_price`='$purchasePrice',
//                     `stock`='$stock',
//                     `quantity`='$quantity',
//                     `unit`='$unit',
//                     `review_val`='$review',
//                     `review_nop`='$reviewNop',
//                     `sku_number`='$skuNumber'
//                 WHERE `p_id`='$p_id'";
//     }

//     $run = mysqli_query($con, $query);
//     echo $run ? "success" : "error";
// }
            
//           else if($type == 'updateProduct') {
//     $p_id = mysqli_real_escape_string($con, $_POST['p_id']);
//     $category = mysqli_real_escape_string($con, $_POST['category']);
//     $subCategory = mysqli_real_escape_string($con, $_POST['subCategory']);
//     $brandName = mysqli_real_escape_string($con, $_POST['brandName']);
//     $productName = mysqli_real_escape_string($con, $_POST['productName']);
//     $mrp = mysqli_real_escape_string($con, $_POST['mrp']);
//     $sellingPrice = mysqli_real_escape_string($con, $_POST['sellingPrice']);
//     $purchasePrice = mysqli_real_escape_string($con, $_POST['purchasePrice']);
//     $stock = mysqli_real_escape_string($con, $_POST['stock']);
//     $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
//     $unit = mysqli_real_escape_string($con, $_POST['unit']);
//     $review = mysqli_real_escape_string($con, $_POST['review']);
//     $reviewNop = mysqli_real_escape_string($con, $_POST['reviewNop']);
//     $skuNumber = mysqli_real_escape_string($con, $_POST['skuNumber']);

//     $base64Image = $_POST['base64Image'] ?? null;
//     $fileExtension = $_POST['fileExtension'] ?? null;

//     if ($base64Image) {
//         // 🔹 Step 1: Purani image delete karo
//         $getOldImg = mysqli_query($con, "SELECT image_path FROM product WHERE p_id='$p_id'");
//         if ($getOldImg && mysqli_num_rows($getOldImg) > 0) {
//             $row = mysqli_fetch_assoc($getOldImg);
//             $oldImgPath = $row['image_path'];
//             $oldFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $oldImgPath);
//             if (file_exists($oldFilePath)) {
//                 unlink($oldFilePath);
//             }
//         }

//         // 🔹 Step 2: Nayi image save karo
//         $img_nm = date('Ymdhis') . ".$fileExtension";
//         $imgurl = 'api/uploads/' . $img_nm;
//         base64_to_jpeg($base64Image, 'uploads/' . $img_nm);

//         // 🔹 Step 3: Product update with image
//         $query = "UPDATE `product` SET 
//                     `name`='$productName',
//                     `image_path`='$imgurl',
//                     `under_category`='$category',
//                     `under_subcategory`='$subCategory',
//                     `brand_name`='$brandName',
//                     `mrp`='$mrp',
//                     `selling_price`='$sellingPrice',
//                     `purchase_price`='$purchasePrice',
//                     `stock`='$stock',
//                     `quantity`='$quantity',
//                     `unit`='$unit',
//                     `review_val`='$review',
//                     `review_nop`='$reviewNop',
//                     `sku_number`='$skuNumber'
//                 WHERE `p_id`='$p_id'";

//         // 🔹 Update first image in product_img table
//         $query2 = "SELECT img_id FROM `product_img` WHERE product_id = '$p_id' ORDER BY img_id ASC LIMIT 1";
//         $run2 = mysqli_query($con, $query2);
//         if ($run2 && mysqli_num_rows($run2) > 0) {
//             $row2 = mysqli_fetch_assoc($run2);
//             $img_id = $row2['img_id'];
//             mysqli_query($con, "UPDATE `product_img` SET `image_path`='$imgurl' WHERE `img_id`='$img_id'");
//         }

//     } else {
//         // 🔹 Agar image nahi aayi toh sirf details update karo
//         $query = "UPDATE `product` SET 
//                     `name`='$productName',
//                     `under_category`='$category',
//                     `under_subcategory`='$subCategory',
//                     `brand_name`='$brandName',
//                     `mrp`='$mrp',
//                     `selling_price`='$sellingPrice',
//                     `purchase_price`='$purchasePrice',
//                     `stock`='$stock',
//                     `quantity`='$quantity',
//                     `unit`='$unit',
//                     `review_val`='$review',
//                     `review_nop`='$reviewNop',
//                     `sku_number`='$skuNumber'
//                 WHERE `p_id`='$p_id'";
//     }

//     // 🔹 Final Product Update Run
//     $run = mysqli_query($con, $query);

//     if ($run) {
//         // ✅ Step 5: Cart table update
//         $updateCart = "
//             UPDATE `cart` 
//             SET 
//                 `mrp` = '$mrp',
//                 `selling_price` = '$sellingPrice',
//                 `purchase_price` = '$purchasePrice'
//             WHERE 
//                 `p_id` = '$p_id' 
//                 AND `status` = 'true' 
//                 AND (`vid` = '0' OR `vid` = '' OR `vid` IS NULL)
//         ";
//         mysqli_query($con, $updateCart);

//         echo "success";
//     } else {
//         echo "error";
//     }
// }


            
//             else if($type == 'updateVarient'){   

//     $vid = mysqli_real_escape_string($con, $_POST['vid']);  
//     $vquantity = mysqli_real_escape_string($con, $_POST['vquantity']); 
//     $vunit = mysqli_real_escape_string($con, $_POST['vunit']); 
//     $vmrp = mysqli_real_escape_string($con, $_POST['vmrp']); 
//     $vsellingPrice = mysqli_real_escape_string($con, $_POST['vsellingPrice']); 
//     $vpurchasePrice = mysqli_real_escape_string($con, $_POST['vpurchasePrice']); 
//     $vstock = mysqli_real_escape_string($con, $_POST['vstock']); 

//     // 🔹 Step 1: Update varient table
//     $query = "
//         UPDATE `varient` 
//         SET 
//             `v_mrp`='$vmrp',
//             `v_seliing_price`='$vsellingPrice',
//             `v_purchase_price`='$vpurchasePrice',
//             `v_stock`='$vstock',
//             `v_quantity`='$vquantity',
//             `v_unit`='$vunit' 
//         WHERE `vid` = '$vid'
//     "; 

//     $run = mysqli_query($con, $query); 

//     // 🔹 Step 2: If variant update success, then update cart
//     if ($run) {
//         $updateCart = "
//             UPDATE `cart`
//             SET 
//                 `mrp` = '$vmrp',
//                 `selling_price` = '$vsellingPrice',
//                 `purchase_price` = '$vpurchasePrice'
//             WHERE 
//                 `vid` = '$vid'
//                 AND `status` = 'true'
//         ";
//         mysqli_query($con, $updateCart);

//         echo "success"; 
//     } else {
//         echo "error";
//     }
// }



            else if($type == 'updateProduct') {
    $p_id = mysqli_real_escape_string($con, $_POST['p_id']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $mainCategory = mysqli_real_escape_string($con, $_POST['mainCategory']);
    $subCategory = mysqli_real_escape_string($con, $_POST['subCategory']);
    $brandName = mysqli_real_escape_string($con, $_POST['brandName']);
    $productName = mysqli_real_escape_string($con, $_POST['productName']);
    // $mrp = mysqli_real_escape_string($con, $_POST['mrp']);
    // $sellingPrice = mysqli_real_escape_string($con, $_POST['sellingPrice']);
    // $purchasePrice = mysqli_real_escape_string($con, $_POST['purchasePrice']);
    // $stock = mysqli_real_escape_string($con, $_POST['stock']);
    // $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
    // $unit = mysqli_real_escape_string($con, $_POST['unit']);
    $review = mysqli_real_escape_string($con, $_POST['review']);
    $reviewNop = mysqli_real_escape_string($con, $_POST['reviewNop']);
    $skuNumber = mysqli_real_escape_string($con, $_POST['skuNumber']);
    $productLimit = mysqli_real_escape_string($con, $_POST['productLimit']);
    $edit_product_keyword = mysqli_real_escape_string($con, $_POST['edit_product_keyword']);

    $base64Image = $_POST['base64Image'] ?? null;
    $fileExtension = $_POST['fileExtension'] ?? null;

    if ($base64Image) {
        // 🔹 Step 1: Purani image delete karo
        $getOldImg = mysqli_query($con, "SELECT image_path FROM product WHERE p_id='$p_id'");
        if ($getOldImg && mysqli_num_rows($getOldImg) > 0) {
            $row = mysqli_fetch_assoc($getOldImg);
            $oldImgPath = $row['image_path'];
            $oldFilePath = __DIR__ . "/" . preg_replace('#^api/#', '', $oldImgPath);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // 🔹 Step 2: Nayi image save karo
        $img_nm = date('Ymdhis') . ".$fileExtension";
        $imgurl = 'api/uploads/' . $img_nm;
        base64_to_jpeg($base64Image, 'uploads/' . $img_nm);

        // 🔹 Step 3: Product update with image
        $query = "UPDATE `product` SET 
                    `name`='$productName',
                    `image_path`='$imgurl',
                    `under_category`='$category',
                    `under_middle_category`='$mainCategory',
                    `under_subcategory`='$subCategory',
                    `brand_name`='$brandName',
                    `review_val`='$review',
                    `review_nop`='$reviewNop',
                    `sku_number`='$skuNumber',
                    `p_limit`='$productLimit',
                    `keyword`='$edit_product_keyword'
                WHERE `p_id`='$p_id'";

        // 🔹 Update first image in product_img table
        $query2 = "SELECT img_id FROM `product_img` WHERE product_id = '$p_id' ORDER BY img_id ASC LIMIT 1";
        $run2 = mysqli_query($con, $query2);
        if ($run2 && mysqli_num_rows($run2) > 0) {
            $row2 = mysqli_fetch_assoc($run2);
            $img_id = $row2['img_id'];
            mysqli_query($con, "UPDATE `product_img` SET `image_path`='$imgurl' WHERE `img_id`='$img_id'");
        }

    } else {
        // 🔹 Agar image nahi aayi toh sirf details update karo
        $query = "UPDATE `product` SET 
                    `name`='$productName',
                    `under_category`='$category',
                    `under_subcategory`='$subCategory',
                    `under_middle_category`='$mainCategory',
                    `brand_name`='$brandName',
                    `review_val`='$review',
                    `review_nop`='$reviewNop',
                    `sku_number`='$skuNumber',
                    `p_limit`='$productLimit',
                    `keyword`='$edit_product_keyword'
                WHERE `p_id`='$p_id'";
    }

    // 🔹 Final Product Update Run
    $run = mysqli_query($con, $query);

    if ($run) {
        // ✅ Step 5: Cart table update
        // $updateCart = "
        //     UPDATE `cart` 
        //     SET 
        //         `mrp` = '$mrp',
        //         `selling_price` = '$sellingPrice',
        //         `purchase_price` = '$purchasePrice'
        //     WHERE 
        //         `p_id` = '$p_id' 
        //         AND `status` = 'true' 
        //         AND (`vid` = '0' OR `vid` = '' OR `vid` IS NULL)
        // ";
        // mysqli_query($con, $updateCart);

        echo "success";
    } else {
        echo "error";
    }
}
 

            
else if($type == 'updateVarient'){   

            $vid = mysqli_real_escape_string($con, $_POST['vid']);  
            $vquantity = mysqli_real_escape_string($con, $_POST['vquantity']); 
            $vunit = mysqli_real_escape_string($con, $_POST['vunit']); 
            $vmrp = mysqli_real_escape_string($con, $_POST['vmrp']); 
            $vsellingPrice = mysqli_real_escape_string($con, $_POST['vsellingPrice']); 
            $vpurchasePrice = mysqli_real_escape_string($con, $_POST['vpurchasePrice']); 
            // $vstock = mysqli_real_escape_string($con, $_POST['vstock']); 
            $vlimit = mysqli_real_escape_string($con, $_POST['vlimit']); 

            // 🔹 Step 1: Update varient table
            $query = "
                UPDATE `varient` 
                SET 
                    `v_mrp`='$vmrp',
                    `v_seliing_price`='$vsellingPrice',
                    `v_purchase_price`='$vpurchasePrice',
                    `v_quantity`='$vquantity',
                    `v_unit`='$vunit',
                    `v_p_limit`='$vlimit'
                WHERE `vid` = '$vid'
            "; 

            $run = mysqli_query($con, $query); 

            // 🔹 Step 2: If variant update success, then update cart
            if ($run) {
                $updateCart = "
                    UPDATE `cart`
                    SET 
                        `mrp` = '$vmrp',
                        `selling_price` = '$vsellingPrice',
                        `purchase_price` = '$vpurchasePrice'
                    WHERE 
                        `vid` = '$vid'
                        AND `status` = 'true'
                ";
        mysqli_query($con, $updateCart);

        echo "success"; 
    } else {
        echo "error";
    }
}
else if($type == 'updateBranchVarient'){

    $pid = mysqli_real_escape_string($con, $_POST['productId']);
    $vid = mysqli_real_escape_string($con, $_POST['vid']);
    $vstock = mysqli_real_escape_string($con, $_POST['vstock']);
    $branchId = mysqli_real_escape_string($con, $_POST['branchId']);

    $vmrp = mysqli_real_escape_string($con, $_POST['vmrp']);
    $vsellingPrice = mysqli_real_escape_string($con, $_POST['vsellingPrice']);
    $vpurchasePrice = mysqli_real_escape_string($con, $_POST['vpurchasePrice']);


    // Check whether variant already exists for this branch
    $checkQuery = "SELECT id
                   FROM branch_stock
                   WHERE branch_id = '$branchId'
                   AND product_id = '$pid'
                   AND varient_id = '$vid'";

    $checkRun = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkRun) > 0) {

        // Update existing record
        $query = "UPDATE branch_stock
                  SET 
                      `stock` = '$vstock',
                      `v_mrp` = '$vmrp',
                      `v_seliing_price` = '$vsellingPrice',
                      `v_purchase_price` = '$vpurchasePrice'
                  WHERE branch_id = '$branchId'
                  AND product_id = '$pid'
                  AND varient_id = '$vid'";

    } else {

        // Insert new record
        $query = "INSERT INTO branch_stock
                  (
                      branch_id,
                      product_id,
                      varient_id,
                      stock,
                      `v_mrp`,
                      `v_seliing_price`,
                      `v_purchase_price`
                  )
                  VALUES
                  (
                      '$branchId',
                      '$pid',
                      '$vid',
                      '$vstock',
                      '$vmrp',
                      '$vsellingPrice',
                      '$vpurchasePrice'
                  )";
    }

    $res = mysqli_query($con, $query);

    if($res){
        echo "success";
    }else{
        echo mysqli_error($con);
    }
}

            
else if($type == 'deleteVarient'){   

    $vid = mysqli_real_escape_string($con, $_POST['vid']);  

    // 🔹 Step 1: Get product_id of this variant
    $getProduct = mysqli_query($con, "SELECT product_id FROM `varient` WHERE vid='$vid'");
    if ($getProduct && mysqli_num_rows($getProduct) > 0) {
        $row = mysqli_fetch_assoc($getProduct);
        $product_id = $row['product_id'];

        // 🔹 Step 2: Delete the variant
        $deleteVar = mysqli_query($con, "DELETE FROM `varient` WHERE vid='$vid'");

        if ($deleteVar) {

            // 🔹 Step 3: Delete cart entries of this variant where status=true
            $deleteCart = mysqli_query($con, "
                DELETE FROM `cart` 
                WHERE `vid` = '$vid' AND `status` = 'true'
            ");
            mysqli_query($con,"DELETE FROM `branch_stock` WHERE `varient_id` = '$vid'");


            // 🔹 Step 4: Check if product has any variants left 
            $checkVariants = mysqli_query($con, "
                SELECT COUNT(*) AS cnt 
                FROM `varient` 
                WHERE `product_id` = '$product_id'
            ");
            $variantCount = 0;
            if ($checkVariants && mysqli_num_rows($checkVariants) > 0) {
                $row2 = mysqli_fetch_assoc($checkVariants);
                $variantCount = intval($row2['cnt']);
            }

            // 🔹 Step 5: If no variants left, mark is_varient=false in product table
            if ($variantCount === 0) {
                mysqli_query($con, "
                    UPDATE `product` 
                    SET `isvarient` = 'false' 
                    WHERE `p_id` = '$product_id'
                ");
            }

            echo "success";

        } else {
            echo "error";
        }
    } else {
        echo "invalid_vid"; // invalid or already deleted
    }
    }   

           
            
           else if($type == 'addMoreVarient'){   

    $lastId = $_POST['p_id'];  

    // JSON data check
    $variantData = isset($_POST['addvarientData']) ? $_POST['addvarientData'] : null;
    
    if ($variantData) {
        $variantData = json_decode($variantData, true); // Decode JSON

        if (is_array($variantData)) {
            $inserted = false; // track if any variant inserted

            foreach ($variantData as $variant) {
                // Extract values safely
                $quantity = mysqli_real_escape_string($con, $variant['quantity'] ?? '');
                $unit = mysqli_real_escape_string($con, $variant['unit'] ?? '');
                $mrp = mysqli_real_escape_string($con, $variant['mrp'] ?? '');
                $sellingPrice = mysqli_real_escape_string($con, $variant['sellingPrice'] ?? '');
                $purchasePrice = mysqli_real_escape_string($con, $variant['purchasePrice'] ?? '');
                $stock = mysqli_real_escape_string($con, $variant['stock'] ?? '');

                // Safety check — avoid accidental arrays
                if (is_array($quantity) || is_array($unit) || is_array($mrp) || 
                    is_array($sellingPrice) || is_array($purchasePrice) || is_array($stock)) {
                    echo "Invalid variant value format.";
                    exit;
                }

                // 🔹 Insert variant
                $addVariant = "
                    INSERT INTO `varient`(
                        `v_mrp`, 
                        `product_id`, 
                        `v_seliing_price`, 
                        `v_purchase_price`, 
                        `v_quantity`, 
                        `v_unit`
                    ) VALUES (
                        '$mrp', 
                        '$lastId', 
                        '$sellingPrice', 
                        '$purchasePrice', 
                        '$quantity', 
                        '$unit'
                    )
                ";
                // echo $addVari ant; exit();

                $run1 = mysqli_query($con, $addVariant);  
                $variantId = mysqli_insert_id($con);

            mysqli_query($con,"INSERT INTO `branch_stock`
                (branch_id, product_id, varient_id, stock)
                SELECT
                    id,
                    '$lastId',
                    '$variantId',
                    '0'
                FROM branch");
            // echo $addVariant; exit();

                if (!$run1) {
                    echo "Error inserting variant: " . mysqli_error($con);
                    exit;
                }

                $inserted = true;
            }

            // ✅ If at least one variant inserted successfully
            if ($inserted) {
                // Product table update → set is_varient = 'true'
                $updateProduct = "UPDATE `product` SET `isvarient`='true' WHERE `p_id`='$lastId'";
                mysqli_query($con, $updateProduct);
                echo "success";
            } else {
                echo "no_variant_added";
            }

        } else {
            echo "Invalid variant data format.";
        }
    } else {
        echo "variantData not found.";
    }
}

            
          else if($type == 'loadAllData'){   
              
            //   echo 'hello' ; die(); 
            $data = [];
        
            // Fetch from product table
            $query = "SELECT * FROM `product`";  
            $run = mysqli_query($con, $query);
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['products'][] = $row;
                }
            }
        
            // Fetch from order table
            $query = "SELECT * FROM `order`";  
            $run = mysqli_query($con, $query); 
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['orders'][] = $row;
                }
            }
        
            // Fetch from user table
            $query = "SELECT * FROM `user`";  
            $run = mysqli_query($con, $query);
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['users'][] = $row;
                }
            }

            // Fetch from pos_order table
            $query = "SELECT * FROM `pos_order`";  
            $run = mysqli_query($con, $query);
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['pos_orders'][] = $row;
                }
            } else {
                $data['pos_orders'] = [];
            }
        
            echo json_encode($data);
        }
              
          else if($type == 'loadAllBranchData'){   

          $branch_id=$_POST['branchId'];
              
            //   echo 'hello' ; die(); 
            $data = [];
        
            // Fetch from product table
            $query = "SELECT * FROM `product`";  
            $run = mysqli_query($con, $query);
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['products'][] = $row;
                }
            }
        
            // Fetch from order table
            $query = "SELECT * FROM `order` WHERE `branch_id` = '$branch_id'";  
            $run = mysqli_query($con, $query); 
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['orders'][] = $row;
                }
            }
        
            // Fetch from user table
            $query = "SELECT
                    p.*
                FROM branch_stock bs
                INNER JOIN product p
                    ON bs.product_id = p.p_id
                WHERE
                    bs.branch_id = '$branch_id'
                    AND bs.stock > 0
                GROUP BY bs.product_id";
                // echo $query; exit();

                    $run = mysqli_query($con, $query);
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['pos_orders'][] = $row;
                }
            }

            // Fetch from pos_order table
            $query = "SELECT * FROM `pos_user` WHERE `branch_id` = '$branch_id'";  
            $run = mysqli_query($con, $query);
            if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data['users'][] = $row;
                }
            } else {
                $data['users'] = [];
            }
        
            echo json_encode($data);
        }
        
        
         else if($type =='addDeliveryMan'){
            
            
            $full_name = $_POST['full_name'];
            $last_name = $_POST['last_name'];
            $identity_type = $_POST['identity_type'];
            $identity_number = $_POST['identity_number'];
            $email = $_POST['email'];
            $mobile = $_POST['mobile'];
            $password = $_POST['password'];
            $cpassword = $_POST['cpassword'];
            $deliveryman_image = $_POST['deliveryman_image'];
            $imageExtension = $_POST['imageExtension'];
            
            $imageFiles = isset($_POST['imageFiles']) ? json_encode($_POST['imageFiles'], true) : [];
            
            $date=date("Y-m-d H:i:s");
            
            $img_nm = date('Ymdhis').".$imageExtension";
            $imgurl = 'api/uploads/'.$img_nm;
            $image = base64_to_jpeg( $deliveryman_image , 'uploads/'.$img_nm);
            
             $uploadedImages = [];
             
                if (!empty($imageFiles)) {
                    $imageData = json_decode($_POST['imageFiles'], true);
                    foreach ($imageData as $index => $imageFile) {
                        $fileName = $imageFile['name'];
                        $base64Data = $imageFile['data'];
                        $imgExtension = $imageFile['imgExtentsion'];

                        // Generate a unique filename
                        $uniqueFileName = "image_" . time() . "_$index.$imgExtension";

                        // Save the image using the base64_to_image function
                        $imgurl1 = 'api/uploads/' . $uniqueFileName;
                        $image = base64_to_jpeg($base64Data, 'uploads/'.$uniqueFileName);
                       
                             
                        $uploadedImages[] =$imgurl1;
                             
                    }
                }
            
                $imagesData = json_encode($uploadedImages) ;    
                $added_by = mysqli_real_escape_string($con, $_POST['log_admin_username'] ?? 'admin');
                
                $sql ="INSERT INTO `delivery_man`(`first_name`, `last_name`, `image_path`, `identity_type`, `identity_image`, `identity_number`, `email`, `mobile_number`, `password`, `confirm_password`,`status`, `dor`, `added_by`) VALUES ('$full_name','$last_name','$imgurl','$identity_type','$imagesData','$identity_number','$email','$mobile','$password','$cpassword','true','$date', '$added_by')";
                 $run = mysqli_query($con, $sql);
                 
                 if($run){
                     echo 'success';
                 }else{
                     echo 'error';
                 } 
                 
            
                
        }
            
            else if($type == 'loadDeliveryMan'){
                
                $query = "SELECT * FROM `delivery_man`";  
                $run = mysqli_query($con, $query);
                if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data[] = $row;
                } 
                  } 
        
                 echo json_encode($data);
                
                 }
                 
            else if($type=='updateDeliveryMan'){
                $typeStatus=$_POST['typeStatus'];
                $id=$_POST['id'];
                $statusText=$_POST['statusText'];
                $query ="UPDATE `delivery_man` SET $typeStatus='$statusText' WHERE id ='$id'";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
             
               else if ($type == 'updateDeliveryManDetails') {
        $deliveryman_id = $_POST['deliveryman_id'];
        $full_name = $_POST['full_name'];
        $last_name = $_POST['last_name'];
        $identity_type = $_POST['identity_type'];
        $identity_number = $_POST['identity_number'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
    
        // profile image
        $deliveryman_image = $_POST['deliveryman_image'] ?? '';
        $imageExtension = $_POST['imageExtension'] ?? '';
    
        $profileImageQuery = "";
        $identityImageQuery = "";
    
        // ✅ Step 1: agar deliveryman_image bheja gaya hai to update kare
        if (!empty($deliveryman_image) && !empty($imageExtension)) {
            $img_nm = date('YmdHis') . ".$imageExtension";
            $imgurl = 'api/uploads/' . $img_nm;
            base64_to_jpeg($deliveryman_image, 'uploads/' . $img_nm);
    
            // query part add kare
            $profileImageQuery = ", `image_path`='$imgurl'";
        }
    
        // ✅ Step 2: agar identity images (multiple) aaye hain to upload kare
        $uploadedImages = [];
    
        if (!empty($_POST['imageFiles'])) {
            $imageData = json_decode($_POST['imageFiles'], true);
            if (is_array($imageData)) {
                foreach ($imageData as $index => $imageFile) {
                    $fileName = $imageFile['name'];
                    $base64Data = $imageFile['data'];
                    $imgExtension = $imageFile['imgExtentsion'];
    
                    // unique filename
                    $uniqueFileName = "identity_" . time() . "_$index.$imgExtension";
                    $imgurl1 = 'api/uploads/' . $uniqueFileName;
    
                    // convert base64 to jpeg
                    base64_to_jpeg($base64Data, 'uploads/' . $uniqueFileName);
    
                    $uploadedImages[] = $imgurl1;
                }
            }
        }
    
        // ✅ Step 3: agar identity image aayi hai to update kare
        if (!empty($uploadedImages)) {
            $imagesData = json_encode($uploadedImages);
            $identityImageQuery = ", `identity_image`='$imagesData'";
        }
    
        // ✅ Step 4: final query
        $query = "UPDATE `delivery_man` 
                  SET 
                    `first_name`='$full_name',
                    `last_name`='$last_name',
                    `identity_type`='$identity_type',
                    `identity_number`='$identity_number',
                    `email`='$email',
                    `mobile_number`='$mobile',
                    `password`='$password',
                    `confirm_password`='$cpassword'
                    $profileImageQuery
                    $identityImageQuery
                  WHERE `id`='$deliveryman_id'";
    
        $run = mysqli_query($con, $query);
    
        if ($run) {
            echo "success";
        } else {
            echo "error";
        }
    }
             
            else if($type=='deleteDeliveryMan'){
                    $id=$_POST['id']; 
                    $query ="DELETE FROM `delivery_man` WHERE `id`='$id'";
                    $run=mysqli_query($con,$query); 
                    if($run){
                        echo "success";
                    }else{
                        echo "error";
                    }
             }
             
             
            // else if($type=='updateOrderStatus'){
                
            //     $id=$_POST['id']; 
            //     $status=$_POST['status'];
            //     $query ="UPDATE `order` SET `status`='$status' WHERE id ='$id'";
            //     $run=mysqli_query($con,$query);
            //     if($run){
            //         echo "success";
            //     }else{
            //         echo "error";
            //     }
            //  }
            
              else if($type == 'updateOrderStatus') {
                $id = $_POST['id']; 
                $status = $_POST['status'];
                $date = date("Y-m-d H:i:s");
                $query = "UPDATE `order` SET `status`='$status' WHERE id ='$id'";
                $run = mysqli_query($con, $query);
            
                if($run) {
                    echo "success";
            
                    if(strtolower($status) == 'delivered') {
            
                        // Step 1: Order details lo
                        $orderRes = mysqli_query($con, "SELECT `user_id`, `total` FROM `order` WHERE `id` = '$id' LIMIT 1");
                        if(mysqli_num_rows($orderRes) > 0) {
                            $orderData = mysqli_fetch_assoc($orderRes);
                            $mobileNumber = $orderData['user_id']; // mobile number stored
                            $orderTotal = $orderData['total'];
            
                            // Step 2: User table se actual ID lo
                            $userRes = mysqli_query($con, "SELECT `user_id` FROM `user` WHERE `mobile` = '$mobileNumber' LIMIT 1");
                            if(mysqli_num_rows($userRes) > 0) {
                                $userData = mysqli_fetch_assoc($userRes);
                                $actualUserId = $userData['user_id'];
            
                                // Step 3: Referral history check karo
                                $refCheck = mysqli_query($con, "SELECT * FROM `refrel_history` WHERE `user_id` = '$actualUserId' LIMIT 1");
                                if(mysqli_num_rows($refCheck) > 0) {
                                    $refData = mysqli_fetch_assoc($refCheck);
            
                                    $refBy = $refData['refrel_by']; // jisne refer kiya
                                    $refByCode = $refData['refrel_by_code'];
                                    $firstOrder = $refData['first_order'];
            
                                    // ✅ Step 4: First order bonus - both get ₹50 each only once
                                    if($firstOrder == 'false') {
                                        $bonusAmount = 50;
            
                                        // User ko ₹50
                                        mysqli_query($con, "UPDATE `user` SET `wallet_balance` = `wallet_balance` + $bonusAmount WHERE `user_id` = '$actualUserId'");
                                        mysqli_query($con, "INSERT INTO `wallet_history` (`order_id`, `user_id`, `amount`, `type`, `created_at`) 
                                                            VALUES ('$id', '$actualUserId', '$bonusAmount', 'credit', '$date')");
            
                                        // Referrer ko ₹50 (agar valid referrer mila)
                                        $refByUserCheck = mysqli_query($con, "SELECT `user_id` FROM `user` WHERE `user_id` = '$refBy' AND `refrel_code` = '$refByCode' LIMIT 1");
                                        if(mysqli_num_rows($refByUserCheck) > 0) {
                                            mysqli_query($con, "UPDATE `user` SET `wallet_balance` = `wallet_balance` + $bonusAmount WHERE `user_id` = '$refBy'");
                                            mysqli_query($con, "INSERT INTO `wallet_history` (`order_id`, `user_id`, `amount`, `type`, `created_at`) 
                                                                VALUES ('$id', '$refBy', '$bonusAmount', 'credit', '$date')");
                                        }
            
                                        // Mark first order as true
                                        mysqli_query($con, "UPDATE `refrel_history` SET `first_order` = 'true' WHERE `user_id` = '$actualUserId'");
                                    }
                                }
                            }
                        }
                    }
            
                } else {
                    echo "error";
                }
            }
                         
            else if($type=='fetchOrderStatus'){
                
                $id=$_POST['id']; 
             
                $query ="SELECT status FROM `order` WHERE id='$id'";
                $run=mysqli_query($con,$query);
                if($run){
                    $row = mysqli_fetch_assoc($run);
                    echo json_encode($row);
                }else{
                    echo "error";
                }
                
             }
             
            else if($type=='setDelvieryCharge'){
                
                $deliveryCharge=$_POST['deliveryCharge']; 
                $minAmount=$_POST['minAmount']; 
                $branchId=$_POST['branchId']; 

                $staff_username = isset($_POST['log_admin_username']) ? mysqli_real_escape_string($con, $_POST['log_admin_username']) : 'Admin';
             
                $query ="INSERT INTO `delivery_charge`(`amount`, `min_amount`, `added_by`,`branch_Id`) 
                VALUES ('$deliveryCharge','$minAmount', '$staff_username','$branchId')";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
                
             }
             
              else if($type=='loadDeliveryCharge'){
                
          $query = "SELECT 
            delivery_charge.*,
            branch.name AS branch_name
          FROM `delivery_charge`
          INNER JOIN `branch`
              ON delivery_charge.branch_id = branch.id
          ORDER BY delivery_charge.id DESC";
                          $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
              else if($type=='loadHandlingCharge'){
                 
                $query ="SELECT * FROM `other` WHERE type = 'handling_charge'";
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
              else if($type=='loadMinOrderValue'){
                 
                $query ="SELECT * FROM `other` WHERE type = 'min_order_value'";
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
              else if($type=='loadGiftProduct'){
                 
                $query ="SELECT * FROM `gift` ORDER BY `id` DESC";  
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
                
            else if($type=='deleteDelCharge'){
                
                $id=$_POST['id'];
                $query ="DELETE FROM `delivery_charge` WHERE `id`='$id'"; 
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
            
             
            else if($type=='setHandlingCharge'){
                
                $handlingCharge=$_POST['handlingCharge'];
                
                $query ="UPDATE `other` SET `min_amount`='$handlingCharge' WHERE type = 'handling_charge'"; 
                
                $run=mysqli_query($con,$query); 
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
             
            else if($type=='setMinOrderValue'){
                
                $minOrderValue=$_POST['minOrderValue'];
                
                $query ="UPDATE `other` SET `min_amount`='$minOrderValue' WHERE type = 'min_order_value'"; 
                
                $run=mysqli_query($con,$query); 
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
             
            else if($type=='addGiftProduct'){
                
                $productName=$_POST['productName'];
                $unit=$_POST['unit'];
                $giftProductQty=$_POST['giftProductQty'];
                $base64Image=$_POST['categoryImage'];
                $imageExtension=$_POST['imageExtension'];
                
                
                $img_nm = date('Ymdhis').".$imageExtension";
                $imgurl = 'api/uploads/'.$img_nm;
                $image = base64_to_jpeg( $base64Image , 'uploads/'.$img_nm);
                
                $query ="INSERT INTO `gift`(`name`, `image_path`, `unit`, `quantity`) VALUES ('$productName','$imgurl','$unit','$giftProductQty')";  
                
                $run=mysqli_query($con,$query); 
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
            
            else if($type=='deleteGiftProduct'){
                
                $id=$_POST['id'];
                $query ="DELETE FROM `gift` WHERE `id`='$id'"; 
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
             

             if ($type== 'getProductVarient') {
            
              $p_id=$_POST['p_id'];
            $branch_id = mysqli_real_escape_string($con, $_POST['branch_id']);

            $query = "SELECT 
                v.v_unit,
                v.v_p_limit,
                v.v_quantity,
                bs.v_purchase_price,
                bs.v_seliing_price,
                bs.v_mrp,
                bs.product_id,
                COALESCE(bs.stock, 0) AS stock
            FROM varient v
            LEFT JOIN branch_stock bs 
                ON bs.product_id = v.product_id
                AND bs.varient_id = v.vid
                AND bs.branch_id = '$branch_id'
            WHERE v.product_id = '$p_id'
            ";        
            // Execute query
            $result = mysqli_query($con, $query);
        
            if (mysqli_num_rows($result)>0) {
                $products = []; 
                while ($row = mysqli_fetch_assoc($result)) {
                    $products[] = $row;
                }
                echo json_encode($products);
            } else {
                echo 'error';
            }
        } 
        
            // coupon section
            
             else if($type =='addCoupon'){
                 
                   $coupon_type=$_POST['coupon_type'];
                   $coupon_title = mysqli_real_escape_string($con, $_POST['coupon_title'] ?? '');
                   $coupon_desc = mysqli_real_escape_string($con, $_POST['coupon_desc'] ?? '');
                   $coupon_code=$_POST['coupon_code'];
                   $coupon_limit=$_POST['coupon_limit'];
                   $discount_type=$_POST['discount_type'];
                   $discount_amount=$_POST['discount_amount'];
                   $minimum_purchase=$_POST['minimum_purchase'];
                   $start_date=$_POST['start_date'];
                   $end_date=$_POST['end_date'];
                   $date = date("Y-m-d H:i:s");
                   $added_by = mysqli_real_escape_string($con, $_POST['log_admin_username'] ?? 'admin');
            
                $query ="INSERT INTO `coupon`(`type`, `title`, `description`, `code`, `limit`, `discount_type`, `amount`, `minimum_purchase`, `start_date`, `end_date`, `dor`, `added_by`) VALUES ('$coupon_type','$coupon_title','$coupon_desc','$coupon_code','$coupon_limit','$discount_type','$discount_amount','$minimum_purchase','$start_date','$end_date','$date', '$added_by')";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
             
             else if($type =='updateCoupon'){
                 
                   $coupon_type=$_POST['coupon_type'];
                   $coupon_title = mysqli_real_escape_string($con, $_POST['coupon_title'] ?? '');
                   $coupon_desc = mysqli_real_escape_string($con, $_POST['coupon_desc'] ?? '');
                   $coupon_code=$_POST['coupon_code'];
                   $coupon_limit=$_POST['coupon_limit'];
                   $discount_type=$_POST['discount_type'];
                   $discount_amount=$_POST['discount_amount'];
                   $minimum_purchase=$_POST['minimum_purchase'];
                   $start_date=$_POST['start_date'];
                   $end_date=$_POST['end_date'];
                   $id=$_POST['id'];
                  
            
                $query ="UPDATE `coupon` SET `type`='$coupon_type',`title`='$coupon_title',`description`='$coupon_desc',`code`='$coupon_code',`limit`='$coupon_limit',`discount_type`='$discount_type',`amount`='$discount_amount',`minimum_purchase`='$minimum_purchase',`start_date`='$start_date',`end_date`='$end_date' WHERE id ='$id'";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
             
             else if($type =='loadCoupon'){
            
                $query="SELECT * FROM `coupon` ORDER BY `id` DESC";
            
                $run=mysqli_query($con,$query);
            
                if($run){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
             }
             
             else if($type=='deleteCoupon'){
                $id=$_POST['id'];
                $query ="DELETE FROM `coupon` WHERE `id`='$id'";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }
            
            //   delivery boy SELECT
            
               else if($type == 'deliveryBoyList'){
                
                $query = "SELECT * FROM `delivery_man`";  
                $run = mysqli_query($con, $query);
                if($run){
                while($row = mysqli_fetch_assoc($run)){
                    $data[] = $row;
                } 
                 echo json_encode($data);
                  } else{
                      echo 'error'; 
                  }
        
                
                 }
                 
                 
               else if($type == 'assignOrder'){
                   
                $orderid=$_POST['orderid']; 
                $driver_id=$_POST['driver_id'];
                $delivery_date=$_POST['delivery_date'];
                $delivery_time=$_POST['delivery_time'];
              
               $checkOrderAssign = "SELECT * FROM `assigned_delivery_man` WHERE `order_id` = '$orderid'";
               $orderAssignResult = mysqli_query($con, $checkOrderAssign);
            
                if(mysqli_num_rows($orderAssignResult) > 0) {
                    echo "This order has already been assigned.";
                    exit();
                }
                
                $query = "INSERT INTO `assigned_delivery_man`(`driver_id`, `order_id`, `date`, `time`) VALUES ('$driver_id','$orderid','$delivery_date','$delivery_time')";  
                $run = mysqli_query($con, $query);
                if($run){
                 echo "success";
                } 
                  else{
                     echo "Something went wrong.";
                  }
        
                
                 }
                 
                 else if($type == 'assignedDeliveryBoy'){
                   
                $orderid=$_POST['order_id']; 
               
               $checkOrderAssign = "SELECT * FROM `assigned_delivery_man` AS a LEFT JOIN delivery_man AS b ON a.driver_id=b.id WHERE a.order_id = '$orderid'";
               $orderAssignResult = mysqli_query($con, $checkOrderAssign);
            
                if(mysqli_num_rows($orderAssignResult)>0){
                    while($row=mysqli_fetch_assoc($orderAssignResult)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{ 
                    echo "error";
                }
              
        
                
                 }
                 
                else if($type=='loadBillingDetails'){
                 
                $query ="SELECT * FROM `billing_address`";
                $run=mysqli_query($con,$query);
                    if(mysqli_num_rows($run)>0){
                        while($row=mysqli_fetch_assoc($run)){
                            $rows[]=$row;
                        }; 	
                        echo json_encode($rows);
                    }else{
                        echo "error";
                    }
                 }
                 
                 else if($type == 'setBillingAddress'){
                      $billing_addresss=$_POST['billing_addresss']; 
                      $billing_number=$_POST['billing_number']; 
                      $gst_number=$_POST['gst_number']; 
                      
                      $query ="UPDATE `billing_address` SET `address`='$billing_addresss',`phone_number`='$billing_number',`gst_number`='$gst_number' WHERE id ='1'"; 
                        $run=mysqli_query($con,$query);
                        if($run){
                            echo "success";
                        }else{
                            echo "error";
                        }
                 }
                 
                //  else if($type == 'addToCart'){
                    
                //     $userId = $_POST['customerId'] ?? '';
                //     $date = date('Y-m-d H:i:s');
                //     $cartData = isset($_POST['cartData']) ? json_decode($_POST['cartData'], true) : [];
                    
                //      foreach ($cartData as $item) {
                //         $image_path = $item['image_path'];
                //         $mrp = $item['mrp'];
                //         $name = $item['name'];
                //         $nop = $item['nop'];
                //         $p_id = $item['p_id'];
                //         $purchase_price = $item['purchase_price'];
                //         $quantity = $item['qty'];
                //         $selling_price = $item['selling_price'];
                //         $unit = $item['unit'];
                //         $v_id = $item['v_id'];
                        
             
                        
                //           $sql="INSERT INTO `cart`(`user_id`, `idfr`, `p_id`, `name`, `image_path`, `quantity`, `unit`, `nop`, `purchase_price`, `selling_price`, `mrp`, `status`, `date`,`vid`,`product_type`) VALUES ('$userId','$idfr','$p_id','$name','$image_path','$quantity','$unit','$nop','$purchase_price','$selling_price','$mrp','true','$date','$vid','pos')";
                //              $run=mysqli_query($con,$sql);
                //              if($run){ 
                //                  echo "success";
                //              }else{ 
                //                  echo "error"; 
                //              }
                          
                       
                        
                        
                //      }

                      
                //  }
                 
                  else if($type=='addNewCustomer'){
                        $full_name=$_POST['full_name'];
                        $email=$_POST['email'];
                        $phone=$_POST['phone'];
                        $date = date("Y-m-d H:i:s");
                        $branch_id=$_POST['role_id'];

                         
                        $query ="INSERT INTO `pos_user`(`branch_id`,`username`, `mobile_number`, `email`, `dor`) VALUES ('$branch_id','$full_name','$phone','$email','$date')";
                        // echo $query; exit();
                        $run=mysqli_query($con,$query);
                        if($run){
                            echo "success";
                        }else{
                            echo "error";
                        } 
                    }
                    
                    
                               //  get brands of the day
          else if ($type =='getBrandsOfTheDay'){  
           
            $query="SELECT * FROM `brands` AS a INNER JOIN `other` AS b ON a.id = b.min_amount WHERE b.type ='brands_of_the_day'"; 
            $run=mysqli_query($con,$query); 
            
             if(mysqli_num_rows($run)>0){ 
                while($row=mysqli_fetch_assoc($run)){ 
                    $rows[]=$row;
                }; 	
                echo json_encode($rows); 
             }else{
            echo 'error'; 
                }
             }
             
            //  set brands of the day
          else if ($type =='setBrandsOfTheWay'){  
           
           $brand_id=$_POST['id'];
            $query="UPDATE `other` SET `min_amount`='$brand_id' WHERE type='brands_of_the_day'"; 
            $run=mysqli_query($con,$query); 
            
             if($run){ 
               echo 'success';
             }else{
                 echo 'error'; 
                }
             }
             
             
            //  check new order status
        //   else if ($type =='checkForNewOrder'){  
           
        //     $query="SELECT * FROM `order` WHERE `new_order`='true' LIMIT 1"; 
        //     $run=mysqli_query($con,$query); 
            
        //      if(mysqli_num_rows($run)>0){ 
        //         while($row=mysqli_fetch_assoc($run)){ 
        //             $rows[]=$row;
        //         }; 	
        //         echo json_encode($rows); 
        //      }else{
        //     echo 'error'; 
        //         }
        //      }
             
        //   else if ($type =='updateNewOrderStatus'){  
           
        //     $query="SELECT * FROM `order` WHERE `new_order`='true' LIMIT 1"; 
        //     $run=mysqli_query($con,$query); 
            
        //      if(mysqli_num_rows($run)>0){ 
        //         while($row=mysqli_fetch_assoc($run)){ 
        //             $rows[]=$row;
        //         }; 	
        //         echo json_encode($rows); 
        //      }else{
        //     echo 'error'; 
        //         }
        //      }
             
          else if ($type =='checkForNewOrder'){  
           
   
             
            
            $query = "SELECT * FROM `order` WHERE `new_order`='true' LIMIT 1";
            $run = mysqli_query($con, $query);
             
            if (mysqli_num_rows($run) > 0) {
                $rows = [];
                while ($row = mysqli_fetch_assoc($run)) { 
                    $rows[] = $row;
            
                 
                    $order_id = $row['id'];
                        
                    $update = "UPDATE `order` SET `new_order`='false' WHERE `id`='$order_id'";
                    mysqli_query($con, $update);
                }
                echo json_encode(['newOrder' => true, 'data' => $rows]);
            } else {
                echo json_encode(['newOrder' => false]);
            }

             }
             
               //  change password 
               else if($type == 'changePassword'){
                
                $current =$_POST['current'];
                $newPass =$_POST['newPass'];
                
                $query = "SELECT * FROM `admin`";  
                $run = mysqli_query($con, $query);
                if($run){
                $row = mysqli_fetch_assoc($run);
                
                if($current==$row['password']){
                    $queryPassword ="UPDATE `admin` SET `password`='$newPass' WHERE id ='1'";
                    $run1 = mysqli_query($con, $queryPassword);
                        if($run1){
                             echo 'success';
                        }
                }else{
                     echo 'password does not match';
                }
                  
                 }else{
                     echo 'something went wrong';
                 }
                  
               }
               
               
            //   time slot
               else if($type =='loadTimeSlot'){
        // $categoryid=$_POST['categoryid'];
                $query="SELECT * FROM `time_slot`";
            
                $run=mysqli_query($con,$query);
            
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
              }
              
              
               else if($type =='setTimeSlot'){
                   
                $slot=$_POST['slot'];
                $query ="INSERT INTO `time_slot`(`slot`) VALUES ('$slot')"; 
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
              }
              

              else if($type=='deleteTimeSlot'){
                $id=$_POST['id'];
                $query ="DELETE FROM `time_slot` WHERE `id`='$id'";
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{ 
                    echo "error"; 
                }
                 }
                 
                   //  load sales report
              else if($type=='loadSalesReport'){
                $branchId = $_POST['branchId'] ?? 0;
                if($branchId == 0) {
                     $query="SELECT 
                        cart.*, 
                        `order`.dor, 
                        `order`.del_charge, 
                        `order`.handling_charge, 
                        `order`.coupon_amount,
                        `order`.payment_method, 
                        `order`.status AS order_status
                    FROM 
                        cart
                    JOIN 
                        `order` ON cart.idfr = `order`.idfr
                    WHERE 
                        cart.status = 'false'
                    ";
                }else{
                    $query="SELECT 
                        cart.*, 
                        `order`.dor, 
                        `order`.del_charge, 
                        `order`.handling_charge, 
                        `order`.coupon_amount,
                        `order`.payment_method, 
                        `order`.status AS order_status
                    FROM 
                        cart
                    JOIN 
                        `order` ON cart.idfr = `order`.idfr
                    WHERE 
                        cart.status = 'false'
                        AND `cart`.branch_id = '$branchId'
                    ";
                }
                    // echo $query; exit();
                
                    $run=mysqli_query($con,$query);
                
                    if(mysqli_num_rows($run)>0){
                        while($row=mysqli_fetch_assoc($run)){
                            $rows[]=$row;
                        }; 	
                        echo json_encode($rows);
                    }else{
                        echo "error";
                    }
                 }
                 
              else if($type=='updateEmail'){
                  
                  $user_id = $_POST['user_id'];
                    $email = $_POST['email'];
                    
                    // Check if email already exists for a different user
                    $checkQuery = "SELECT * FROM `user` WHERE `email` = '$email' AND `user_id` != '$user_id'";
                    $checkRun = mysqli_query($con, $checkQuery);
                    
                    if (mysqli_num_rows($checkRun) > 0) {
                        echo "email already exists"; // Email already used by another user
                    } else {
                        // Proceed to update
                        $updateQuery = "UPDATE `user` SET `email`='$email' WHERE `user_id`='$user_id'";
                        $updateRun = mysqli_query($con, $updateQuery);
                    
                        if ($updateRun) {
                            echo "success";
                        } else {
                            echo "error: " . mysqli_error($con);
                        }
                    }
                 }
                 
                  else if($type =='loadTitle'){
        // $categoryid=$_POST['categoryid'];
                $query="SELECT 
                    ht.*,
                    c.name AS category_name
                FROM header_title ht
                INNER JOIN category c
                    ON ht.category_Id = c.id";  
            
                $run=mysqli_query($con,$query);
            
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
              }
              
                  else if($type =='loadMainTitle'){
        // $categoryid=$_POST['categoryid'];
                $query="SELECT * FROM `main_header`";  
            
                $run=mysqli_query($con,$query);
            
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
              }
              
              
                  else if($type =='saveTitle'){
                      
                $headline=$_POST['headline'];
                $title=$_POST['title'];
                $header_title=$_POST['header_title'];
                $category=$_POST['category'];
                $query="SELECT * FROM `header_title`";  
            
                 $updateQuery = "UPDATE `header_title` SET `$title`='$header_title' WHERE `title_type`='$headline' AND `category_Id`='$category'";
                        $updateRun = mysqli_query($con, $updateQuery);
                    
                        if ($updateRun) {
                            echo "success";
                        } else {
                            echo "error: " . mysqli_error($con);
                        }
              }
              
              
                  else if($type =='saveMainHeader'){
                      
                $header=$_POST['header'];
                $main_header=$_POST['main_header'];
               
                 $updateQuery = "UPDATE `main_header` SET `title`='$main_header' WHERE `header_type`='$header'";
                        $updateRun = mysqli_query($con, $updateQuery);
                    
                        if ($updateRun) {
                            echo "success";
                        } else {
                            echo "error: " . mysqli_error($con);
                        }
              }
             
             else if($type=='saveHeroBanner'){
                // $categoryName = mysqli_real_escape_string($con, $_POST['categoryName']);
                $category_id=$_POST['category_id'];
                $middleCatId=$_POST['middleCatId'] ?? '';
                $subCategoryId=$_POST['subCategoryId'] ?? '';
                $categoryImage=$_POST['image_base64'];
                $imageExtension=$_POST['image_ext'];
                $date=date("Y-m-d H:i:s");
            
                $img_nm = date('Ymdhis').".$imageExtension";
                $imgurlbase64 = $categoryImage;
                $imgurl = 'api/uploads/'.$img_nm;
                $image = base64_to_jpeg( $imgurlbase64 , 'uploads/'.$img_nm);
            
                $query ="INSERT INTO `hero_banner`( `under_category`,`under_middle_category`, `image_path`,`under_subcategory`) 
                VALUES ('$category_id','$middleCatId','$imgurl','$subCategoryId')";
                // echo $query; exit();
                $run=mysqli_query($con,$query);
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }

            }
              
        //         else if($type =='loadHeroBanner'){
        // // $categoryid=$_POST['categoryid'];
        //         $query="SELECT a.id, a.image_path, a.under_category AS category_id, b.name
        //         FROM hero_banner AS a
        //         INNER JOIN category AS b ON a.under_category = b.id";  
            
        //         $run=mysqli_query($con,$query);
            
        //         if(mysqli_num_rows($run)>0){
        //             while($row=mysqli_fetch_assoc($run)){
        //                 $rows[]=$row;
        //             }; 	
        //             echo json_encode($rows);
        //         }else{
        //             echo "error";
        //         }
        //       }
        
else if($type == 'loadHeroBanner'){

    $query = "
        SELECT 
            a.id,
            a.image_path,
            COALESCE(c.id, s.id) AS link_id,
            COALESCE(c.name, s.name) AS link_name,
            CASE 
                WHEN a.under_category IS NOT NULL 
                     AND a.under_category != '' 
                     AND a.under_category != 0
                THEN 'category'
                ELSE 'subcategory'
            END AS link_type
        FROM hero_banner a
        LEFT JOIN category c 
            ON a.under_category = c.id
        LEFT JOIN subcategory s 
            ON (a.under_category IS NULL 
                OR a.under_category = '' 
                OR a.under_category = 0)
            AND a.under_subcategory = s.id
    ";

    $run = mysqli_query($con, $query);

    if(mysqli_num_rows($run) > 0){
        while($row = mysqli_fetch_assoc($run)){
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo "error";
    }
}



                //  else if($type=='deleteHeroBanner'){
                // $id=$_POST['id'];
                // $query ="DELETE FROM `hero_banner` WHERE `id`='$id'";
                // $run=mysqli_query($con,$query);
                // if($run){
                //     echo "success";
                // }else{
                //     echo "error";
                // }
                //  }
                
                else if($type=='deleteHeroBanner'){
                $id = $_POST['id'];
            
                // Step 1: DB se image_path nikal lo
                $getImg = mysqli_query($con, "SELECT image_path FROM hero_banner WHERE id='$id'");
                if ($getImg && mysqli_num_rows($getImg) > 0) {
                    $row = mysqli_fetch_assoc($getImg);
                    $imgPath = $row['image_path']; // e.g. api/uploads/20250906032330.png
            
                    // Step 2: "api/" ko remove karo sirf file deletion ke liye
                    $filePath = __DIR__ . "/" . preg_replace('#^api/#', '', $imgPath); // uploads/20250906032330.png
            
                    // Step 3: Agar file exist karti hai to delete karo
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            
                // Step 4: DB se row delete karo
                $query = "DELETE FROM hero_banner WHERE id='$id'";
                $run = mysqli_query($con, $query);
            
                if ($run) {
                    echo "success";
                } else {
                    echo "error";
                }
            }
            
            
            // update product highlight and information
            
           else if ($type == 'updateProductDescription') {

            $p_id = mysqli_real_escape_string($con, $_POST['p_id'] ?? '');
        
            // JSON string aayega frontend se — usko dobara json_encode karte hain string ke form me save karne ke liye
            $highlight = isset($_POST['highlight']) ? json_encode($_POST['highlight']) : '"[]"';
            $information = isset($_POST['information']) ? json_encode($_POST['information']) : '"[]"';
        
            // Escape for SQL safety
            $highlight = mysqli_real_escape_string($con, $highlight);
            $information = mysqli_real_escape_string($con, $information);
        
            if (empty($p_id)) {
                echo "error: Missing product ID";
                exit;
            }
        
            $updateQuery = "UPDATE `product` 
                            SET `highlight` = '$highlight', 
                                `information` = '$information' 
                            WHERE `p_id` = '$p_id'";
        
            $runUpdate = mysqli_query($con, $updateQuery);
        
            if ($runUpdate) {
                echo "success";
            } else {
                echo "error: " . mysqli_error($con);
            }
        }
        
            // pos order section 
            
else if ($type == 'addToCart') {

    // 🟢 Step 1: Get all request data safely
    $customerId = mysqli_real_escape_string($con, $_POST['customerId']);
    $branchId = mysqli_real_escape_string($con, $_POST['branchId']);
    $cartData = json_decode($_POST['cartData'], true);
    $cartSubTotal = mysqli_real_escape_string($con, $_POST['cartSubTotal']);
    $totalCartSaving = mysqli_real_escape_string($con, $_POST['totalCartSaving']);
    $discountPrice = mysqli_real_escape_string($con, $_POST['discountPrice']);
    $percentageDiscountType = mysqli_real_escape_string($con, $_POST['percentageDiscountType']);
    $delCharge = mysqli_real_escape_string($con, $_POST['delCharge']);
    $totalCatAmount = mysqli_real_escape_string($con, $_POST['totalCatAmount']);
     $orderType = mysqli_real_escape_string($con, $_POST['orderType']);
      $paymentType = mysqli_real_escape_string($con, $_POST['paymentMethode']);

    // 🟢 Step 2: Validation
    if (!is_array($cartData) || count($cartData) == 0) {
        echo json_encode(["status" => "error", "message" => "Cart is empty"]);
        exit;
    }

    $status = "true";
    $orderType = isset($_POST['orderType']) ? mysqli_real_escape_string($con, $_POST['orderType']) : '';
    $paymentType = isset($_POST['paymentType']) ? mysqli_real_escape_string($con, $_POST['paymentType']) : '';

    $staff_username = isset($_POST['log_admin_username']) ? mysqli_real_escape_string($con, $_POST['log_admin_username']) : 'Admin';

    $status = "pending";
    $date = date("Y-m-d H:i:s");

    // 🟢 Step 4: Insert full POS order with POS-specific details
    $cartDataJson = mysqli_real_escape_string($con, json_encode($cartData));

    $insertOrder = "
        INSERT INTO `pos_order`(
            `branch_id`,
            `customer_id`, 
            `order_type`, 
            `cart_data`, 
            `sub_total`, 
            `product_discount`, 
            `extra_discount`, 
            `discount_type`, 
            `delivery_charge`, 
            `total`, 
            `payment_type`,
            `status`,
            `date`,
            `added_by`
        ) VALUES (
            '$branchId',
            '$customerId',
            '$orderType',
            '$cartDataJson',
            '$cartSubTotal',
            '$totalCartSaving',
            '$discountPrice',
            '$percentageDiscountType',
            '$delCharge',
            '$totalCatAmount',
            '$paymentType',
            '$status',
            '$date',
            '$staff_username'
        )
    ";

    $run = mysqli_query($con, $insertOrder);

    if ($run) {

        // 🟢 Step 5: Stock minus from product/variant tables
        foreach ($cartData as $item) {
            $p_id = mysqli_real_escape_string($con, $item['p_id']);
            $vid = mysqli_real_escape_string($con, $item['v_id']);
            $nop = mysqli_real_escape_string($con, $item['nop']);

            // if ($vid == 0 || $vid == '0' || $vid == '') {
            //     // 🔹 Minus from product table
            //     $updateProductStock = "
            //         UPDATE `product` 
            //         SET `stock` = GREATEST(`stock` - $nop, 0)
            //         WHERE `p_id` = '$p_id'
            //     ";
            //     mysqli_query($con, $updateProductStock);
            // } else {
                // 🔹 Minus from variant table
                $updateVariantStock = "UPDATE `branch_stock` 
                    SET `stock` = GREATEST(`stock` - $nop, 0)
                    WHERE `varient_id` = '$vid' AND `product_id` = '$p_id' AND `branch_id` = '$branchId'
                ";
                mysqli_query($con, $updateVariantStock);
            // }
        }

        echo json_encode(["status" => "success", "message" => "Order placed and stock updated"]);

    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($con)]);
    }
}

        
        else if($type=='loadPosOrder'){
                $branchId=$_POST['branchId'];
                
                $query ="SELECT * FROM `pos_order` WHERE `branch_id`='$branchId' ORDER BY `id` DESC"; 
                // echo $query; exit();
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }


// load main banner 

         else if($type =='loadMainBanner'){
        $categoryId=$_POST['categoryId'];
                $query="SELECT 
    mb.*,
    c.name AS category_name
FROM main_banner mb
LEFT JOIN category c
    ON mb.category_id = c.id";  
            
                $run=mysqli_query($con,$query);
            
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
              }
              
              
else if ($type == "saveMainBanner") {

    $categoryId = mysqli_real_escape_string($con, $_POST['categoryId']);
    $color = mysqli_real_escape_string($con, $_POST['color']);
    $iconColor = mysqli_real_escape_string($con, $_POST['iconColor']);

    // Check if category already exists
    $check = mysqli_query($con, "SELECT * FROM main_banner WHERE category_id='$categoryId'");

   if(mysqli_num_rows($check) > 0){

    // Always update color code
    mysqli_query($con, "UPDATE main_banner
                        SET color_code='$color'
                        WHERE category_id='$categoryId'");    
                        
    mysqli_query($con, "UPDATE main_banner
                        SET icon_color='$iconColor'
                        WHERE category_id='$categoryId'");

    // Update Hero Banner
    if(!empty($_POST['hero_bg_base64']) && !empty($_POST['hero_bg_ext'])){

        $hero_img_name = "hero_" . time() . "." . $_POST['hero_bg_ext'];
        $hero_img_path = "api/uploads/" . $hero_img_name;

        base64_to_jpeg($_POST['hero_bg_base64'], "uploads/".$hero_img_name);

        mysqli_query($con, "UPDATE main_banner
                            SET img_path='$hero_img_path'
                            WHERE category_id='$categoryId'");
    }

    // Update Child Banner
    if(!empty($_POST['hero_bg_child_base64']) && !empty($_POST['hero_bg_child_ext'])){

        $child_img_name = "hero_child_" . time() . "." . $_POST['hero_bg_child_ext'];
        $child_img_path = "api/uploads/" . $child_img_name;

        base64_to_jpeg($_POST['hero_bg_child_base64'], "uploads/".$child_img_name);

        mysqli_query($con, "UPDATE main_banner
                            SET child_img='$child_img_path'
                            WHERE category_id='$categoryId'");
    }

}else{

    $hero_img_path = "";
    $child_img_path = "";

    if(!empty($_POST['hero_bg_base64']) && !empty($_POST['hero_bg_ext'])){

        $hero_img_name = "hero_" . time() . "." . $_POST['hero_bg_ext'];
        $hero_img_path = "api/uploads/" . $hero_img_name;

        base64_to_jpeg($_POST['hero_bg_base64'], "uploads/".$hero_img_name);
    }

    if(!empty($_POST['hero_bg_child_base64']) && !empty($_POST['hero_bg_child_ext'])){

        $child_img_name = "hero_child_" . time() . "." . $_POST['hero_bg_child_ext'];
        $child_img_path = "api/uploads/" . $child_img_name;

        base64_to_jpeg($_POST['hero_bg_child_base64'], "uploads/".$child_img_name);
    }

    $query = "INSERT INTO main_banner(category_id, img_path, child_img, color_code, icon_color)
              VALUES('$categoryId', '$hero_img_path', '$child_img_path', '$color')";
    mysqli_query($con, $query);
}

    echo "success";
}        
               
            
//          else if($type == 'downloadStockExcel'){

//     ob_clean();
//     ob_start();

//     header("Content-Type: application/vnd.ms-excel");
//     header("Content-Disposition: attachment; filename=bulk_stock.xls");
//     header("Pragma: no-cache");
//     header("Expires: 0");

//     echo "product_id\tproduct_name\tcategory\tsub_category\tquantity\tunit\tstock\n";

//     // 🔹 STEP 1: LOOP PRODUCTS ONE BY ONE
//     $productQ = mysqli_query($con,"SELECT * FROM product");

//     while($p = mysqli_fetch_assoc($productQ)){

//         // 👉 MAIN PRODUCT ROW
//         echo $p['p_id']."\t"
//             .$p['name']."\t"
//             .$p['under_category']."\t"
//             .$p['under_subcategory']."\t"
//             .$p['quantity']."\t"
//             .$p['unit']."\t"
//             .$p['stock']."\n";

//         // 🔹 STEP 2: SAME PRODUCT KE VARIANTS
//         $variantQ = mysqli_query($con,"
//             SELECT * FROM varient 
//             WHERE product_id = '".$p['p_id']."'
//         ");

//         while($v = mysqli_fetch_assoc($variantQ)){
//             echo $p['p_id']."\t"
//                 .$p['name']."\t"
//                 .$p['under_category']."\t"
//                 .$p['under_subcategory']."\t"
//                 .$v['v_quantity']."\t"
//                 .$v['v_unit']."\t"
//                 .$v['v_stock']."\n";
//         }
//     }

//     ob_end_flush();
//     exit;
// }


        
//         /* =====================================================
//           UPLOAD & UPDATE STOCK
//         =====================================================*/
//       else if($type == 'uploadStockExcel'){

//     error_reporting(E_ALL);
//     ini_set('display_errors', 1);

//     require_once __DIR__ . '/phpspreadsheet/autoload.php';

//     if(!isset($_FILES['file'])){
//         echo "File not received";
//         exit;
//     }

//     $tmpPath = $_FILES['file']['tmp_name'];

//     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
//     $rows = $spreadsheet->getActiveSheet()->toArray(null,true,true,true);

//     foreach($rows as $i => $row){

//         if($i == 1) continue;

//         $product_id = trim($row['A'] ?? '');
//         $qty        = trim($row['E'] ?? '');
//         $unit       = trim($row['F'] ?? '');
//         $stock      = trim($row['G'] ?? '');

//         if($product_id === '' || $stock === '') continue;

//         mysqli_query($con,"
//             UPDATE product 
//             SET stock='$stock' 
//             WHERE p_id='$product_id'
//         ");

//         mysqli_query($con,"
//             UPDATE varient 
//             SET v_stock='$stock'
//             WHERE product_id='$product_id'
//               AND v_quantity='$qty'
//               AND v_unit='$unit'
//         ");
//     }

//     echo "success";
//     exit;
// }


        
        else if($type == 'downloadStockCSV'){

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bulk_stock.csv"');

    $out = fopen('php://output', 'w');

    fputcsv($out, [
        'product_id','v_id','product_name',
        'category','sub_category','quantity','unit','stock'
    ]);

    $q = mysqli_query($con,"SELECT * FROM product");
    while($p = mysqli_fetch_assoc($q)){

        // 🟢 MAIN PRODUCT ROW (v_id = 0)
        fputcsv($out, [
            $p['p_id'],
            0,
            $p['name'],
            $p['under_category'],
            $p['under_subcategory'],
            $p['quantity'],
            $p['unit'],
            $p['stock']
        ]);

        // 🟢 VARIANT ROWS (real v_id)
        $v = mysqli_query($con,"SELECT * FROM varient WHERE product_id='".$p['p_id']."'");
        while($vr = mysqli_fetch_assoc($v)){
            fputcsv($out, [
                $p['p_id'],
                $vr['vid'],
                $p['name'],
                $p['under_category'],
                $p['under_subcategory'],
                $vr['v_quantity'],
                $vr['v_unit'],
                $vr['v_stock']
            ]);
        }
    }

    fclose($out);
    exit;
}



  else if($type == 'uploadStockCSV'){

    if(!isset($_FILES['file'])){
        echo "File not found";
        exit;
    }

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if($ext != 'csv'){
        echo "Only CSV allowed";
        exit;
    }

    $handle = fopen($_FILES['file']['tmp_name'], "r");
    if(!$handle){
        echo "Unable to open file";
        exit;
    }

    $row = 0;
    while(($data = fgetcsv($handle, 1000, ",")) !== false){

        // Header skip
        if($row == 0){
            $row++;
            continue;
        }

        $product_id = trim($data[0]);
        $v_id       = trim($data[1]);   // 🔴 IMPORTANT
        $stock      = trim($data[7]);

        if($product_id === '' || $stock === '') continue;

        // 🟢 MAIN PRODUCT UPDATE
        if($v_id == '0'){
            mysqli_query($con,"
                UPDATE product 
                SET stock='$stock'
                WHERE p_id='$product_id'
            ");
        }

        // 🟢 VARIANT UPDATE
        if($v_id != '0'){
            mysqli_query($con,"
                UPDATE varient
                SET v_stock='$stock'
                WHERE vid='$v_id'
            ");
        }

        $row++;
    }

    fclose($handle);

    echo "success";
    exit;
}


        else if($type =='loadArea'){
        // $categoryid=$_POST['categoryid'];
                $query="SELECT * FROM `area`";  
            
                $run=mysqli_query($con,$query);
            
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
              }

        
        else if($type=='saveArea'){
          
            $pin_code=$_POST['pin_code'];
            $date = date("Y-m-d H:i:s");
            
            $query ="INSERT INTO `area`(`pin_code`, `status`, `dor`) VALUES ('$pin_code','true','$date')";
            $run=mysqli_query($con,$query);
            if($run){
                echo "success";
            }else{
                echo "error";
            }

              }
              
              
        else if($type=='deleteArea'){
               $id = $_POST['id'];
            // Step 4: DB se row delete karo
            $query = "DELETE FROM area WHERE id='$id'";
            $run = mysqli_query($con, $query);
        
            if ($run) {
                echo "success";
            } else {
                echo "error";
            }
        }
        
        else if($type=='updateUserStatus'){
        $typeStatus=$_POST['typeStatus'];
        $id=$_POST['id'];
        $statusText=$_POST['statusText'];
        $query ="UPDATE `user` SET status='$statusText' WHERE user_id ='$id'";
        $run=mysqli_query($con,$query);
        if($run){
            echo "success";
        }else{
            echo "error";
        }
    }
    
        else if($type=='loadGiftOrderValue'){
                 
                $query ="SELECT * FROM `other` WHERE type = 'gift'";
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                }else{
                    echo "error";
                }
            }
            
            
             else if($type=='setGiftOrderValue'){
                
                $giftOrderValue=$_POST['giftOrderValue'];
                
                $query ="UPDATE `other` SET `min_amount`='$giftOrderValue' WHERE type = 'gift'"; 
                
                $run=mysqli_query($con,$query); 
                if($run){
                    echo "success";
                }else{
                    echo "error";
                }
             }

        
        else if ($type == 'getShopStatus') {

            $query = "SELECT details, msg FROM other_info WHERE type='shop_open' LIMIT 1";
            $run = mysqli_query($con, $query);
        
            if ($run && mysqli_num_rows($run) > 0) {
                $row = mysqli_fetch_assoc($run);
        
                $status = ($row['details'] == 'true') ? 'open' : 'closed';
        
                echo json_encode([
                    "status" => $status,
                    "message" => $row['msg']
                ]);
            } else {
                echo json_encode([
                    "status" => "open",
                    "message" => ""
                ]);
            }
        }
        
        else if ($type == 'setShopStatus') {

            $status = $_POST['status'];     // open / closed
            $message = $_POST['message'];
        
            $details = ($status == 'open') ? 'true' : 'false';
        
            $query = "UPDATE other_info 
                      SET details='$details', msg='$message' 
                      WHERE type='shop_open'";
        
            $run = mysqli_query($con, $query);
        
            if ($run) {
                echo "success";
            } else {
                echo "error";
            }
        }
        
        else if ($type == 'getAllOptions') {

            $query = "SELECT type, details FROM other_info";
            $run = mysqli_query($con, $query);
        
            $data = [];
        
            while ($row = mysqli_fetch_assoc($run)) {
                $data[] = $row;
            }
        
            echo json_encode($data);
        }


        else if ($type == 'setOptionStatus') {

        $option = $_POST['option'];
        $value  = $_POST['value']; // true / false
    
        $query = "UPDATE other_info SET details='$value' WHERE type='$option'";
        $run = mysqli_query($con, $query);
    
        if ($run) {
            echo "success";
        } else {
            echo "error";
        }
    }
    
    
    else if ($type == 'getContactInfo') {

    $query = "SELECT contact_type, contact_info FROM contact_info";
    $run = mysqli_query($con, $query);

    $data = [];

    while ($row = mysqli_fetch_assoc($run)) {
        $data[$row['contact_type']] = $row['contact_info'];
    }

    echo json_encode($data);
    }


    else if ($type == 'setContactInfo') {

    $phone     = $_POST['phone_no'];
    $whatsapp  = $_POST['whatsapp_no'];
    $gmail     = $_POST['gmail'];
    $instagram = $_POST['instagram'];
    $facebook = $_POST['facebook'];

    mysqli_query($con, "UPDATE contact_info SET contact_info='$phone' WHERE contact_type='phone_no'");
    mysqli_query($con, "UPDATE contact_info SET contact_info='$whatsapp' WHERE contact_type='whatsapp_no'");
    mysqli_query($con, "UPDATE contact_info SET contact_info='$gmail' WHERE contact_type='gmail'");
    mysqli_query($con, "UPDATE contact_info SET contact_info='$instagram' WHERE contact_type='instagram'");
    mysqli_query($con, "UPDATE contact_info SET contact_info='$facebook' WHERE contact_type='facebook'");

    echo "success";
}



    else if ($type == 'getRecentOrders') {
        $branchId=$_POST['branchId'] ?? 0;
        if($branchId == 0){
            $query = "SELECT id, dor, total, status 
                      FROM `order`
                      ORDER BY id DESC
                      LIMIT 3";
        } else {
            $query = "SELECT id, dor, total, status 
                      FROM `order`
                      WHERE branch_id='$branchId'
                      ORDER BY id DESC
                      LIMIT 3";
        }

  

    $run = mysqli_query($con, $query);

    $data = [];

    while ($row = mysqli_fetch_assoc($run)) {
        $data[] = $row;
    }

    echo json_encode($data);
}


    // ==========================================
    //        STAFF MANAGEMENT APIs
    // ==========================================

    else if ($type == 'addStaff') {
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
        $permissions = mysqli_real_escape_string($con, $_POST['permissions']); // JSON ya comma separated
        
        // Check if username already exists
        $check = mysqli_query($con, "SELECT id FROM admin WHERE username='$username'");
        if(mysqli_num_rows($check) > 0) {
            echo "exists";
        } else {
            // Note: Agar aapne database me 'name' column bhi add kiya hai toh usko bhi insert karein,
            // Filhal admin table structure ke hisaab se username, password, email, role, status, permissions bhej rahe hain.
            $query = "INSERT INTO `admin` (`username`, `password`, `email`, `role`, `status`, `permissions`) 
                      VALUES ('$username', '$password', '$email', 'staff', 1, '$permissions')";
            if(mysqli_query($con, $query)){
                echo "success";
            } else {
                echo "error";
            }
        }
    }

    else if ($type == 'updateStaff') {
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
        $role = mysqli_real_escape_string($con, $_POST['role'] ?? 'staff');
        $permissions = mysqli_real_escape_string($con, $_POST['permissions']);
        $password = isset($_POST['password']) ? mysqli_real_escape_string($con, $_POST['password']) : '';
        
        $check = mysqli_query($con, "SELECT id FROM admin WHERE username='$username' AND id!='$id'");
        if(mysqli_num_rows($check) > 0) {
            echo "exists";
        } else {
            $update_pass = "";
            if (!empty($password)) {
                $update_pass = "`password`='$password', ";
            }
            $query = "UPDATE `admin` SET `username`='$username', $update_pass `email`='$email', `role`='$role', `permissions`='$permissions' WHERE id='$id'";
            if(mysqli_query($con, $query)){
                echo "success";
            } else {
                echo "error";
            }
        }
    }

    else if ($type == 'loadStaff') {
        // Sirf unko load karein jinka role 'admin' nahi hai (yani staff hain) // UPDATE: Reverted to hide 'admin' as per request
        $query = "SELECT id, username, email, role, status, permissions FROM `admin` WHERE role != 'admin' ORDER BY id DESC";
        $run = mysqli_query($con, $query);
        $data = [];
        if($run && mysqli_num_rows($run) > 0){
             while($row = mysqli_fetch_assoc($run)){
                 $data[] = $row;
             }
        }
        echo json_encode($data);
    }

    else if ($type == 'updateStaffStatus') {
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $status = mysqli_real_escape_string($con, $_POST['status']); // 1 ya 0
        $query = "UPDATE `admin` SET `status`='$status' WHERE id='$id'";
        if(mysqli_query($con, $query)){
            echo "success";
        } else {
            echo "error";
        }
    }

    else if ($type == 'updateStaffPermissions') {
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $permissions = mysqli_real_escape_string($con, $_POST['permissions']);
        $query = "UPDATE `admin` SET `permissions`='$permissions' WHERE id='$id'";
        if(mysqli_query($con, $query)){
            echo "success";
        } else {
            echo "error";
        }
    }

    else if ($type == 'deleteStaff') {
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $query = "DELETE FROM `admin` WHERE id='$id'";
        if(mysqli_query($con, $query)){
            echo "success";
        } else {
            echo "error";
        }
    }


  else if($type == 'getAllDeliveryOrders'){
    $query = "SELECT o.*, adm.driver_id AS delivery_man_id, dm.first_name, dm.last_name, dm.mobile_number 
              FROM `order` o 
              LEFT JOIN `assigned_delivery_man` adm ON o.id = adm.order_id 
              LEFT JOIN `delivery_man` dm ON adm.driver_id = dm.id 
              ORDER BY o.id DESC";
              
    $res = mysqli_query($con, $query);
    $data = array();
    while($row = mysqli_fetch_assoc($res)){
        // Agar delivery boy assigned nahi hai to null handle karein
        if($row['delivery_man_id'] == null){
             $row['delivery_man_id'] = '';
             $row['first_name'] = '';
             $row['last_name'] = '';
        }
        $data[] = $row;
    }
    echo json_encode($data);
}

else if($type == 'loadAllCategory'){
    $query = "SELECT * FROM `category` WHERE status='true'";

    $res = mysqli_query($con,$query);
    if(mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)){
            $rows[] = $row;
        }
        echo json_encode([
            "status" => "success",
            "data" => $rows
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No categories found"
        ]);
    }
}

else if($type == 'addBranch'){
    $name = $_POST['name'];
    $description = $_POST['description'];
    $phone_no = $_POST['phone_no'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    $coverage = $_POST['coverage'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];
    $isOpen = $_POST['isOpen'];
    $status = $_POST['status'];

    $query1= "INSERT INTO `branch`
    (
        `name`,
        `description`,
        `phone_no`,
        `email`,
        `password`,
        `address`,
        `latitude`,
        `longitude`,
        `coverage`,
        `city`,
        `state`,
        `pincode`,
        `isOpen`,
        `status`
    )
    VALUES
    (
        '$name',
        '$description',
        '$phone_no',
        '$email',
        '$password',
        '$address',
        '$longitude',
        '$latitude',
        '$coverage',
        '$city',
        '$state',
        '$pincode',
        '$isOpen',
        '$status'
    )";

    $res1 = mysqli_query($con,$query1);
    
  if ($res1) {

    $lastId = mysqli_insert_id($con);

    $query2 = "INSERT INTO admin
    (username, password, email, role, role_id, status, permissions)
    VALUES
    ('$name','$password','$email','branch','$lastId','1','pos,Product,Category,Order')";

    $res2 = mysqli_query($con, $query2);

    if ($res2) {

        // Copy all variants into branch_stock
        $query3 = "INSERT INTO branch_stock
        (branch_id, product_id, varient_id, stock, v_mrp, v_seliing_price, v_purchase_price)
        SELECT
            '$lastId',
            product_id,
            vid,
            0,
            v_mrp,
            v_seliing_price,
            v_purchase_price
        FROM varient";
        // echo $query3; exit();

        $res3 = mysqli_query($con, $query3);

        if ($res3) {

            echo json_encode([
                "status" => "success",
                "message" => "Branch, admin and stock initialized successfully!"
            ]);

        } else {

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($con)
            ]);

        }

    } else {

        echo json_encode([
            "status" => "error",
            "message" => mysqli_error($con)
        ]);

    }

} else {

    echo json_encode([
        "status" => "error",
        "message" => mysqli_error($con)
    ]);

}

}
else if($type == 'loadBranch'){
    $query  = "SELECT * FROM `branch`
    ORDER BY `id` DESC";
    $res = mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"loadbranch successfully !",
            "data"=>$data
        ]);
    }
    else{
        echo json_encode([
            "status"=>"success",
            "message"=>"something wents wrong !",
            "data"=>[]
        ]);
    }
}
else if($type=='updateStatus'){
     $typeStatus=$_POST['typeStatus'];
            $id=$_POST['id'];
            $statusText=$_POST['statusText'];
            $query ="UPDATE `branch` SET $typeStatus='$statusText' WHERE id ='$id'";
            // echo $query; exit();
            $run=mysqli_query($con,$query);
            if($run){
                echo "success";
            }else{
                echo "error";
            }
}

else if($type == "updateBranch"){
    $id = $_POST['id'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $phone_no = $_POST['phone_no'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];
    $coverage =$_POST['coverage'];
    

    $query = "UPDATE `branch` SET
        `name` = '$name',
        `description` = '$description',
        `phone_no` = '$phone_no',
        `email` = '$email',
        `address` = '$address',
        `password` = '$password',
        `city` = '$city',
        `state` = '$state',
        `pincode` = '$pincode',
        `coverage`='$coverage'
    WHERE `id` = '$id'";
    // echo $query; exit();

    if (mysqli_query($con, $query)) {
        echo json_encode([
            "status" => "success",
            "message" => "Branch updated successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => mysqli_error($con)
        ]);
    }
}
else if($type == "deleteBranch"){
    $id=$_POST['id'];
    $query="DELETE FROM `branch` WHERE `branch`.`id` = '$id'";
    // echo $query; die();
    $res=mysqli_query($con,$query);
    if($res){
       
        echo json_encode([
            "status"=>"success",
            "message"=>"branch delete successfully !"
        ]);
    }else{
        echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !"
        ]);
    }

}
else if($type == "getAllBranch"){
    $query = "SELECT * FROM `branch`";
    $res=mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
             $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"branch found successfully !",
            "data"=>$data
        ]);
    }else{
         echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !"
        ]);
    }
}
else if($type == "getMainCategory"){
    $query="SELECT * FROM `category`";
    $res = mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row = mysqli_fetch_assoc($res)) {
             $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"maincategory fetched successfully !",
            "data"=>$data
        ]);
    }
    else{
        echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !"
        ]);
    }
}
else if($type == "getmiddleCategory"){
    $categoryId = $_POST['categoryId'];
    $query = "SELECT * FROM `middle_category` WHERE `under_category`='$categoryId'";
    // echo $query; exit();

    $res = mysqli_query($con,$query);

    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"getmiddleCategory fetched successfully !",
            "data"=>$data
        ]);
    }else{
         echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !",
            "data"=>[]
        ]);
    }
}
else if($type == "getmiddleCategory2"){
    $categoryId = $_POST['categoryId'];
    $query = "SELECT 
        mc.*,
        c.name AS category_name
    FROM 
        `middle_category` mc
    JOIN 
        `category` c
        ON mc.under_category = c.id
        ORDER BY `mc`.`id` DESC
    ";
    // echo $query; exit();
    
    $res = mysqli_query($con,$query);

    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"getmiddleCategory fetched successfully !",
            "data"=>$data
        ]);
    }else{
         echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !",
            "data"=>[]
        ]);
    }
}
else if($type == "handleMiddleCategory"){

    $middleCategory = $_POST['middleCategory'];
    $categoryId = $_POST['categoryId'];

    // Images
    $category_img = $_FILES['category_img'] ?? null;



    $logoName =  "ITS_" . $_FILES['category_img']['name'];
    $logoTmp = $_FILES['category_img']['tmp_name'];
    
    
    $logo = move_uploaded_file($logoTmp, "uploads/" . $logoName);

    $logoNameDB = "api/uploads/" . $logoName;

    
    
    
    $query = "INSERT INTO `middle_category`(`under_category`, `name`, `image_path`, `status`) 
    VALUES ('$categoryId','$middleCategory','$logoNameDB','true')";
    // echo $query; exit();
    $res = mysqli_query($con,$query);

    if($res){
       
        echo json_encode([
            "status"=>"success",
            "message"=>"inserted successfully !"
        ]);
    }else{
        echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !"
        ]);
    }
}
else if($type=="updateMiddleCategory"){
    $typeStatus=$_POST['typeStatus'];
            $id=$_POST['id'];
            $statusText=$_POST['statusText'];
            $query ="UPDATE `middle_category` SET $typeStatus='$statusText' WHERE id ='$id'";
            // echo $query; exit();
            $run=mysqli_query($con,$query);
            if($run){
                echo "success";
            }else{
                echo "error";
            }
}

  else if($type == 'editMiddleCategoryName'){
    $id = $_POST['id'];
    $middleCategory = $_POST['middleCategory'];
    $categoryId = $_POST['categoryId'];


    $query = "UPDATE `middle_category` SET `under_category`='$categoryId', `name`='$middleCategory' WHERE `id`='$id'";
       $run = mysqli_query($con, $query);

    if($run){
        echo "success";
    } else {
        echo "error: " . mysqli_error($con); // Optional debugging
    }
}


                //  edit subcategory
else if($type == 'editMiddleSubCategoryWithImage'){
       
    $id = $_POST['id'];
    $middleCategory = $_POST['middleCategory'];
    $categoryId = $_POST['categoryId'];

    // Images
    $category_img = $_FILES['category_img'] ?? null;



    $logoName =  "ITS_" . $_FILES['category_img']['name'];
    $logoTmp = $_FILES['category_img']['tmp_name'];
    
    
    $logo = move_uploaded_file($logoTmp, "uploads/" . $logoName);

    $logoNameDB = "api/uploads/" . $logoName;

        $query = "UPDATE `middle_category` SET `under_category`='$categoryId', `name`='$middleCategory', 
        `image_path`='$logoNameDB' WHERE `id`='$id'";
        $run = mysqli_query($con, $query);

        if($run){
            echo "success";
        } else {
            echo "error: " . mysqli_error($con); // helpful for debugging
        }
}
else if($type == 'getAllMiddleCategory'){
    $query="SELECT * FROM `middle_category`";
    $res = mysqli_query($con,$query);

    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"getAllMiddleCategory fetched successfully !", 
            "data"=>$data
        ]);
    }
    else{
         echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !",
            "data"=>[]
        ]);
    }

}


else if($type=="getSubCategory"){
    $middleCategory = $_POST['categoryId'];
    $query = "SELECT * FROM `subcategory` WHERE `middle_category`='$middleCategory'";
    $res=mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
             $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"getSubCategory fetched successfully !",
            "data"=>$data
        ]);
    }else{
          echo json_encode([
            "status"=>"failed",
            "message"=>"something wents wrong !",
            "data"=>[]
        ]);
    }
}





?>