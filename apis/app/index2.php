<?php
date_default_timezone_set("Asia/Kolkata");
header("Access-Control-Allow-Origin:*"); 
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
$con=mysqli_connect("localhost","root","","u907727509_Krishanthmart"); // Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL The Eroor is : " . mysqli_connect_error();
  }
mysqli_query($con, "SET time_zone = '+05:30'");
  $type=$_REQUEST['type'];    


   function base64_to_jpeg($base64_string, $output_file) {
    $ifp = fopen( $output_file, 'wb' ); 
    $data = explode( ',', $base64_string );
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );
    fclose( $ifp ); 
    return $output_file; 
    }

    //  send admin order notification
    
   function sendNewOrderMailToAdmin($orderAutoId)
{
    global $con; // 👈 existing DB connection use

    if (empty($orderAutoId)) {
        return ['status' => 'error', 'msg' => 'Order ID missing'];
    }

    // ---------- FETCH ADMIN EMAIL ----------
    $adminRes = mysqli_query($con, "SELECT email FROM admin LIMIT 1");
    if (!$adminRes) {
        return ['status' => 'error', 'msg' => 'Admin query failed'];
    }

    $adminRow = mysqli_fetch_assoc($adminRes);
    if (empty($adminRow['email'])) {
        return ['status' => 'error', 'msg' => 'Admin email not found'];
    }

    $adminEmail = $adminRow['email'];

    // ---------- FETCH ORDER + USER ----------
    $orderSql = "
        SELECT 
            o.id,
            o.total,
            o.date,
            o.time,
            o.order_type,
            o.payment_methode,
            o.del_charge,
            o.handling_charge,
            o.status,
            u.full_name,
            u.mobile
        FROM `order` o
        LEFT JOIN `user` u ON u.mobile = o.user_id
        WHERE o.id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($con, $orderSql);
    if (!$stmt) {
        return ['status' => 'error', 'msg' => 'Prepare failed'];
    }

    mysqli_stmt_bind_param($stmt, "i", $orderAutoId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) == 0) {
        return ['status' => 'error', 'msg' => 'Order not found'];
    }

    $order = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    // ---------- MAIL CONTENT ----------
    $subject = "🛒 New Order Received | Order #{$order['id']}";

    $message = "
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family:Arial'>
        <h2 style='color:#e67e22'>New Order Alert</h2>

        <p><b>Order ID:</b> {$order['id']}</p>
        <p><b>Customer Name:</b> {$order['full_name']}</p>
        <p><b>Mobile:</b> {$order['mobile']}</p>

        <hr>

        <p><b>Order Type:</b> {$order['order_type']}</p>
        <p><b>Payment Method:</b> {$order['payment_methode']}</p>
        <p><b>Total Amount:</b> ₹{$order['total']}</p>
        <p><b>Delivery Charge:</b> ₹{$order['del_charge']}</p>
        <p><b>Handling Charge:</b> ₹{$order['handling_charge']}</p>
        <p><b>Status:</b> {$order['status']}</p>

        <p><b>Date & Time:</b> {$order['date']} {$order['time']}</p>

        <br>
        <p>Please login to admin panel to process this order.</p>
    </body>
    </html>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html; charset=UTF-8\r\n";
    $headers .= "From: ITS Mart <noreply@itsmart.com>\r\n";

    // ---------- SEND MAIL ----------
    $mailSent = mail($adminEmail, $subject, $message, $headers);

    return [
        'status'   => $mailSent ? 'success' : 'failed',
        'sent_to'  => $adminEmail,
        'order_id' => $orderAutoId
    ];
}





    
    if($type == 'signup') {
        $fullName = $_POST['fullName'];
        $email = $_POST['email'];
        $mobileNumber = $_POST['mobileNumber'];
        $password = $_POST['password'];
        $deviceID = $_POST['DeviceToken'] ?? null; 
        $newreferralCode = $_POST['refrelCode'] ?? null; 
        $walletBalance = 0; // Initial wallet balance
        $status = 'true'; // Active user
        $date = date('Y-m-d H:i:s'); // Current date and time
        $prefix = "ITS"; // Fixed prefix for referral code
    //  $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $checkMobileQuery = "SELECT * FROM `user` WHERE `mobile` = '$mobileNumber'";
        $mobileCheckResult = mysqli_query($con, $checkMobileQuery);
        
        // if($byRefrelCode){
        //     echo 'yes';
        // }else{
        //      echo 'no';
        // }
        // exit;  
        
        if(mysqli_num_rows($mobileCheckResult) > 0) {
            echo "Mobile number already exists.";
            exit();
        }
    
        // Generate a unique referral code
        do {
            $uniqueNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Random 4-digit number
            $referralCode = $prefix . $uniqueNumber;
    
            $checkReferralQuery = "SELECT * FROM `user` WHERE `refrel_code` = '$referralCode'";
            $referralCheckResult = mysqli_query($con, $checkReferralQuery);
        } while(mysqli_num_rows($referralCheckResult) > 0);
    
        // Insert the user data
        $query = "INSERT INTO `user`(`full_name`, `mobile`, `email`, `password`, `refrel_code`, `wallet_balance`, `device_id`, `status`, `date`) 
                  VALUES ('$fullName', '$mobileNumber', '$email', '$password', '$referralCode', '$walletBalance', '$deviceID', '$status', '$date')";
        $run = mysqli_query($con, $query);
    
        if($run) {
            
            echo "success";
             // Naya user ka ID le lo
            $newUserId = mysqli_insert_id($con);
        
            // Agar referral code diya gaya hai
            if (!empty($referralCode)) {
                
                // echo 'yes';
                // Check karo referral code kis user ka hai
                $checkRef = mysqli_query($con, "SELECT `user_id` FROM `user` WHERE `refrel_code` = '$newreferralCode' LIMIT 1");
                
                if (mysqli_num_rows($checkRef) > 0) {
                    $refData = mysqli_fetch_assoc($checkRef);
                    $refById = $refData['user_id']; // jisne invite kiya 
                    
                    // echo $refById ; die(); 
        
                    // Referral amount set karo
                    $refAmount = 50; // Example amount, aap apna rakh sakte ho
        
                    // First order flag
                    $firstOrder = 'false'; // 0 = abhi order nahi kiya
        
                    // Insert referral history
                    $insertHistory = "INSERT INTO `refrel_history`
                                      (`user_id`, `refrel_by`, `refrel_by_code`, `refrel_amount`, `first_order`, `dor`) 
                                      VALUES 
                                      ('$newUserId', '$refById', '$newreferralCode', '$refAmount', '$firstOrder', '$date')";
                    mysqli_query($con, $insertHistory);
                }
            }
            
        } else {
            echo "error";
        }
    }
    


        else if($type == 'login'){
            $mobileNumber = $_POST['mobileNumber'];
            $password = $_POST['password'];
            $DeviceToken = $_POST['DeviceToken'];

        $checkMobileQuery = "SELECT * FROM `user` WHERE `mobile` = '$mobileNumber'";
        $mobileCheckResult = mysqli_query($con, $checkMobileQuery);
         $data = [];
        if(mysqli_num_rows($mobileCheckResult) > 0) {
            $row=mysqli_fetch_assoc($mobileCheckResult);
             $hashedPasswordFromDb = $row['password'];
            if($password==$row['password']){
                 $update = "UPDATE `user` SET `device_id`='$DeviceToken' WHERE mobile ='$mobileNumber'";
                 $run = mysqli_query($con, $update);
                $data = [
                        'message' => 'success',
                        'data' => $row,
                        ];
                echo json_encode($data);
            }else{
                // echo 'Incorrect passsword';
                 $data = [
                        'message' => 'Incorrect passsword',
                        ];
                echo json_encode($data);
            }
        }else{
            // echo "Mobile number does not exists.";
             $data = [
                        'message' => 'Mobile number does not exists',
                        ];
                echo json_encode($data);
            exit();
        }

        }

        else if($type == 'wsignup') {
        $fullName = $_POST['fullName'];
        $email = $_POST['email'];
        $mobileNumber = $_POST['mobileNumber'];
        $password = $_POST['password'];
        $deviceID = $_POST['deviceID'] ?? null; 
        $walletBalance = 0; // Initial wallet balance
        $status = 'true'; // Active user
        $date = date('Y-m-d H:i:s'); // Current date and time
        $prefix = "ITS"; // Fixed prefix for referral code
    //  $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $checkMobileQuery = "SELECT * FROM `user` WHERE `mobile` = '$mobileNumber'";
        $mobileCheckResult = mysqli_query($con, $checkMobileQuery);
    
        if(mysqli_num_rows($mobileCheckResult) > 0) {
            echo "Mobile number already exists.";
            exit();
        }
    
        // Generate a unique referral code
        do {
            $uniqueNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Random 4-digit number
            $referralCode = $prefix . $uniqueNumber;
    
            $checkReferralQuery = "SELECT * FROM `user` WHERE `refrel_code` = '$referralCode'";
            $referralCheckResult = mysqli_query($con, $checkReferralQuery);
        } while(mysqli_num_rows($referralCheckResult) > 0);
    
        // Insert the user data
        $query = "INSERT INTO `user`(`full_name`, `mobile`, `email`, `password`, `refrel_code`, `wallet_balance`, `device_id`, `status`, `date`) 
                  VALUES ('$fullName', '$mobileNumber', '$email', '$password', '$referralCode', '$walletBalance', '$deviceID', '$status', '$date')";
        $run = mysqli_query($con, $query);
    
        if($run) {
            echo "success";
        } else {
            echo "error";
        }
    }
    


        else if($type == 'wlogin'){
            $mobileNumber = $_POST['mobileNumber'];
            $password = $_POST['password'];

        $checkMobileQuery = "SELECT * FROM `user` WHERE `mobile` = '$mobileNumber'";
        $mobileCheckResult = mysqli_query($con, $checkMobileQuery);
      $data = [];
        if(mysqli_num_rows($mobileCheckResult) > 0) {
            $row=mysqli_fetch_assoc($mobileCheckResult);
             $hashedPasswordFromDb = $row['password'];
            if($password==$row['password']){
                
                 $data = [
                'message' => 'success',
                'data' => $row,
            ];
            echo json_encode($data);
                
            }else{
                // echo 'Incorrect passsword';
                 $data = [
                'message' => 'Incorrect passsword',
            ];
            echo json_encode($data);
            }
        }else{
                $data = [
                'message' => 'Mobile number does not exists',
            ];
            echo json_encode($data);
            // echo "Mobile number does not exists.";
            exit();
        }

        }

        if ($type == 'passwordRetrieve') {
            $mobileNumber = $_POST['mobileNumber'];
        
            // Check if the mobile number exists
            $checkMobileQuery = "SELECT * FROM `user` WHERE `mobile` = '$mobileNumber'";
            $mobileCheckResult = mysqli_query($con, $checkMobileQuery);
        
            if (mysqli_num_rows($mobileCheckResult) > 0) {
                $row = mysqli_fetch_assoc($mobileCheckResult);
                $email = $row['email'];
                $password = $row['password']; // Assuming the password is stored in plain text (not recommended)
        
                // Email content
                $subject = "Password Retrieval";
                $message = "Hello " . $row['full_name'] . ",\n\nYour password is: $password\n\nPlease keep it safe.";
                $headers = "From: support@indiantechsolution.com\r\n"; // Replace with your Verpex email address
                $headers .= "Reply-To: your_email@yourdomain.com\r\n"; // Optional: For replies
        
                // Send the email
                if (mail($email, $subject, $message, $headers)) {
                    echo "success"; 
                } else {
                    echo "Email could not be sent.";
                }
            } else {
                echo "Mobile number does not exist.";
                exit();
            }
        }
        
        




 
    else if($type =='loadCategory'){

    $query="SELECT * FROM `category` WHERE status = 'true' ORDER BY `id` ASC";

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
     else if($type =='loadPhrasesCategory'){

    $query="SELECT * FROM `category` WHERE status = 'true' ORDER BY `id` ASC";

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
    
    else if($type =='fetchCategory'){
        
    $cat_id =$_POST['cat_id'];
    $query="SELECT * FROM `category` WHERE id ='$cat_id'"; 
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

    else if($type =='getCategoryDetails'){
        $cat_id=$_POST['cat_id'];
    $query="SELECT * FROM `category` WHERE `id`='$cat_id'";
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

    else if($type =='getSubCategoryDetails'){
        $cat_id=$_POST['cat_id'];
        $query="SELECT  * FROM subcategory WHERE `under_category`= '$cat_id' AND status !='false' ORDER BY `id` ASC";
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

    else if($type =='getCategoryProduct'){
        $cat_id=$_POST['cat_id'];
        $query="SELECT * FROM product WHERE `under_category`='$cat_id' AND `status`='true' ORDER BY `p_id` ASC"; 
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




    else if($type =='loadSubCategory'){

        $query="SELECT  subcategory.*, category.name AS category_name FROM subcategory JOIN category ON subcategory.under_category = category.id WHERE subcategory.status = 'true' ORDER BY `id` ASC"; 
    
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
   



      

     

       



      

        else if($type=='loadProduct'){
            $query="SELECT * FROM `product` WHERE status ='true' ORDER BY `p_id` ASC";

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
         else if($type=='loadFlashSaleProducts'){
            $query = "SELECT * FROM `product` 
          WHERE `status` = 'true' AND `flash_sale` ='true'
          ORDER BY `p_id` ASC";

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

       

        else if($type =='loadBanner'){

            // $query="SELECT DISTINCT b.*, c.name AS category_name, s.name AS subcategory_name FROM banner b LEFT JOIN category c ON b.under_category = c.id LEFT JOIN subcategory s ON b.uder_subcategory = s.id WHERE  b.under_category IS NOT NULL  OR b.uder_subcategory IS NOT NULL AND b.status='true' ORDER BY b.b_id ASC";
            $query = "SELECT DISTINCT 
            b.*, 
            c.name AS category_name, 
            s.name AS subcategory_name 
          FROM 
            banner b 
          LEFT JOIN 
            category c ON b.under_category = c.id 
          LEFT JOIN 
            subcategory s ON b.uder_subcategory = s.id 
          WHERE  
            (b.under_category IS NOT NULL OR b.uder_subcategory IS NOT NULL) 
            AND b.status = 'true' 
          ORDER BY 
            b.b_id ASC"; 
        
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

          

            // load all category 

            else if($type=='loadAllCategory'){
                $sql = "SELECT c.id, c.name, s.id as subcat_id, s.name as subcat_name,s.image_path 
                        FROM category c 
                        LEFT JOIN subcategory s ON c.id = s.under_category WHERE s.status='true'
                        ORDER BY c.id";

                $result = $con->query($sql);

                $data = [];
                $current_catid = null;

                while ($row = $result->fetch_assoc()) {
                    // If a new category is encountered
                    if ($current_catid !== $row['id']) {
                        $current_catid = $row['id'];
                        $data[] = [
                            'catid' => $row['id'],
                            'cat_name' => $row['name'],
                            'subcategory' => []
                        ];
                    }
            // Add subcategory if it exists
                if (!empty($row['subcat_id'])) {
                    $data[count($data) - 1]['subcategory'][] = [
                        'subcatid' => $row['subcat_id'],
                        'subcat_name' => $row['subcat_name'],
                        'image_path' => $row['image_path']
                    ];
                }
            }

            echo json_encode($data, JSON_PRETTY_PRINT);
            }

        //    loadSingleProductDetails

        else if($type=='loadSingleProductDetails'){
            $p_id=$_POST['p_id'];
            $query="SELECT * FROM `product`  WHERE `p_id` = '$p_id'";
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

        else if($type=='loadSingleProductImage'){
            $p_id=$_POST['p_id'];
            $query="SELECT * FROM `product_img`  WHERE `product_id` = '$p_id'";
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



        // loadBrands
        else if ($type =='loadBrands'){
          
            $query="SELECT * FROM `brands` WHERE status ='true' ORDER BY `id` ASC";
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


        else if ($type =='loadSelectedProduct'){
          
            $cat_id=$_POST['cat_id'];
            $productType=$_POST['productType'];
            $query="SELECT * FROM `product` WHERE $productType ='$cat_id' AND status='true' ORDER BY `p_id` ASC";

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
        
        // else if ($type =='productSearch'){
          
        //     // $cat_id=$_POST['cat_id'];
        //     $searchInput=$_POST['searchInput'];
        //     $query = "SELECT * FROM `product` WHERE `name` LIKE '%$searchInput%' AND status='true' ORDER BY `p_id` ASC";

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
        
        else if ($type == 'productSearch') {

    $searchInput = trim($_POST['searchInput']);

    if(strlen($searchInput) < 1){
        echo "error";
        exit;
    }

    $searchInput = mysqli_real_escape_string($con, $searchInput);

    $query = "
        SELECT *
        FROM product
        WHERE status = 'true'
        AND (
            name LIKE '%$searchInput%'
            OR keyword LIKE '%$searchInput%'
        )
        ORDER BY p_id ASC
        LIMIT 30
    ";

    $run = mysqli_query($con, $query);

    if(mysqli_num_rows($run) > 0){
        $rows = [];
        while($row = mysqli_fetch_assoc($run)){
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo "error";
    }
}

        
        else if ($type =='loadAllSubCategory'){
          
            $cat_id=$_POST['cat_id'];
            $productType=$_POST['productType'];
            $query="SELECT * FROM `subcategory` WHERE $productType ='$cat_id' ORDER BY `id` ASC";

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

        else if ($type == 'getSuggestedProduct') {

      // Decode product IDs 
        $product_ids = isset($_POST['product_ids']) ? json_decode($_POST['product_ids'], true) : null;
    
        // Validate product_ids
        if (!is_array($product_ids) || empty($product_ids)) {
            echo "error";
            exit;
        }
    
        // Safe integer placeholders
        $placeholders = implode(',', array_map('intval', $product_ids));
    
        // JOIN with category table
         $query = "
    SELECT 
        p.*,
        c.name as cat_name
    FROM product p
    LEFT JOIN category c ON p.under_category = c.id
    WHERE p.p_id IN ($placeholders)
    AND p.status = 'true'
     ";
    
        $result = mysqli_query($con, $query);
    
        if ($result) {
            $products = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }
            echo json_encode($products);
        } else {
            echo "error";
        }
    } 
        
        else if ($type== 'loadBestSellingProduct') {
           
            $query = "SELECT * FROM product WHERE btitle1='true' 
            OR btitle2='true' OR btitle3='true' OR btitle4='true' 
            OR btitle5='true' OR btitle6='true'"; 
        
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
        
         else if ($type== 'loadBestSellingProduct2') {
           
            $query = "SELECT * FROM product WHERE dtitle1='true' OR dtitle2='true' OR dtitle3='true' OR dtitle4='true' OR dtitle5='true' OR dtitle6='true'"; 
        
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
        
        else if ($type== 'topProduct') {
           $cat_id=$_POST['cat_id'];
            $subcat_id=$_POST['subcat_id'];
           $query = "SELECT * FROM product 
          WHERE (btitle1='true' OR btitle2='true' OR btitle3='true' OR btitle4='true' OR btitle5='true' OR btitle6='true' OR 
                 title1='true' OR title2='true' OR title3='true' OR title4='true' OR title5='true' OR title6='true') 
          AND under_category='$cat_id' AND under_subcategory !='$subcat_id' AND status='true'"; 
        
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
        
        else if ($type== 'recommendedProduct') {
           $subcat_id=$_POST['subcat_id'];
           $query = "SELECT * FROM product WHERE under_subcategory='$subcat_id' AND status='true'";
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
        
        else if ($type== 'getProductVarient') {
            
              $p_id=$_POST['p_id'];
            $query = "SELECT * FROM varient WHERE product_id='$p_id'"; 
        
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
        
        // fetch user location
        
          else if ($type =='fetchUserLocation'){
           
            $userId=$_POST['userId'];
   
            $query="SELECT * FROM `location` WHERE user_id ='$userId' ORDER BY `id` ASC LIMIT 1";

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
        
          else if ($type =='insertUserLocation'){
           
            $userId=$_POST['userId'];
            $city=$_POST['city'];
            $state=$_POST['state'];
            $postalCode=$_POST['postalCode'];
            $area=$_POST['area'];
            $latitude = $_POST['latitude'] ?? null; 
            $longitude = $_POST['longitude'] ?? null; 
            $date = date('Y-m-d H:i:s');
            
            $query="INSERT INTO `location`(`user_id`, `city`, `state`, `pin_code`, `area`, `date`,`latitude`,`longitude`) VALUES ('$userId','$city','$state','$postalCode','$area','$date','$latitude','$longitude')"; 
            $run=mysqli_query($con,$query);
        
            if($run){ 
               echo "success";
            }else{
                echo "error";
            }

        }
        
        // add to cart
          else if ($type =='addToCart'){
           
            $userId=$_POST['userId'];
            $idfr=$_POST['idfr'];
            $p_id=$_POST['p_id']; 
            // $name=$_POST['name'];
            $name = mysqli_real_escape_string($con, $_POST['name']);
            $image_path=$_POST['image_path'];
            $quantity=$_POST['quantity'];
            $unit=$_POST['unit'];
            $purchase_price=$_POST['purchase_price'];
            $selling_price=$_POST['selling_price'];
            $mrp=$_POST['mrp'];
            $nop=$_POST['nop'];
            $isvarient=$_POST['isvarient'];
            $vid=$_POST['vid']?? null;
            $date = date('Y-m-d H:i:s');
            $query="SELECT * FROM `cart` WHERE user_id ='$userId' AND idfr ='$idfr' AND p_id = '$p_id' AND vid='$vid' AND status = 'true'"; 
            $run=mysqli_query($con,$query);
            //   $number = mysqli_num_rows($run);
            //   echo $number ; die();
            if(mysqli_num_rows($run)==0){
                
              $sql="INSERT INTO `cart`(`user_id`, `idfr`, `p_id`, `name`, `image_path`, `quantity`, `unit`, `nop`, `purchase_price`, `selling_price`, `mrp`, `isvarient`, `status`, `date`,`vid`) VALUES ('$userId','$idfr','$p_id','$name','$image_path','$quantity','$unit','$nop','$purchase_price','$selling_price','$mrp','$isvarient','true','$date','$vid')";
                 $run1=mysqli_query($con,$sql);
                 if($run1){ 
                     echo "success";
                 }else{ 
                     echo "error"; 
                 }
              
            }else{
                echo "Already added in your cart";
            }

        }
        
        
          else if ($type =='addGiftProduct'){
           
            $userId=$_POST['userId'];
            $idfr=$_POST['idfr'];
            $p_id=$_POST['giftId']; 
            $name=$_POST['name'];
            $image_path=$_POST['image_path'];
            $quantity=$_POST['quantity'];
            $unit=$_POST['unit'];
            $purchase_price=$_POST['purchase_price'] ?? null;
            $selling_price=$_POST['selling_price'] ?? null;
            $mrp=$_POST['mrp'] ?? null;
            $nop=$_POST['nop'] ?? null;
            $isvarient=$_POST['isvarient'] ?? null;
            $vid=$_POST['vid']?? null;
            $date = date('Y-m-d H:i:s');
            $query="SELECT * FROM `cart` WHERE user_id ='$userId' AND product_type ='gift' AND status = 'true'"; 
            $run=mysqli_query($con,$query);
            //   $number = mysqli_num_rows($run);
            //   echo $number ; die();
            if(mysqli_num_rows($run)==0){
                
              $sql="INSERT INTO `cart`(`user_id`, `idfr`, `p_id`, `name`, `image_path`, `quantity`, `unit`, `nop`, `purchase_price`, `selling_price`, `mrp`, `isvarient`, `status`, `date`,`vid` ,`product_type`) VALUES ('$userId','$idfr','$p_id','$name','$image_path','$quantity','$unit','$nop','$purchase_price','$selling_price','$mrp','$isvarient','true','$date','$vid' ,'gift')";
                 $run1=mysqli_query($con,$sql);
                 if($run1){ 
                     echo "success";
                 }else{ 
                     echo "error"; 
                 }
              
            }else{
                echo "Already added in your cart";
            }

        }
        
//          else if ($type =='addToCartInc'){

//     $userId = $_POST['userId'];
//     $idfr   = $_POST['idfr'];
//     $p_id   = $_POST['p_id']; 
//     $nop    = $_POST['nop'];
//     $vid    = $_POST['vid'] ?? '';

//     // Agar vid empty / null / 0 ho
//     if($vid === '' || $vid === null || $vid == 0){
        
//         $query = "UPDATE `cart` 
//                   SET `nop` = '$nop' 
//                   WHERE idfr = '$idfr' 
//                   AND user_id = '$userId' 
//                   AND status = 'true' 
//                   AND p_id = '$p_id' 
//                   AND (vid IS NULL OR vid = '' OR vid = '0')";

//     } 
//     // Agar proper vid mila ho
//     else {

//         $query = "UPDATE `cart` 
//                   SET `nop` = '$nop' 
//                   WHERE idfr = '$idfr' 
//                   AND user_id = '$userId' 
//                   AND status = 'true' 
//                   AND p_id = '$p_id' 
//                   AND vid = '$vid'";
//     }

//     $run = mysqli_query($con, $query);

//     if($run){
//         echo 'success';
//     }else{
//         echo 'error';
//     }
// }

      else if ($type == 'addToCartInc') {

            $userId = $_POST['userId'];
            $idfr   = $_POST['idfr'];
            $p_id   = $_POST['p_id'];
            $nop    = $_POST['nop'];
            $vid    = $_POST['vid'] ?? 0;
            
            // echo $nop; die();
        
            $p_limit = null;
        
            // ---------- GET p_limit ----------
            if (empty($vid) || $vid == 0) {
        
                // 🔹 Product table
                $limitRes = mysqli_query(
                    $con,
                    "SELECT p_limit FROM product WHERE p_id='$p_id' LIMIT 1"
                );
        
                if ($limitRes && mysqli_num_rows($limitRes) > 0) {
                    $limitRow = mysqli_fetch_assoc($limitRes);
                    $p_limit  = $limitRow['p_limit'];
                }
        
            } else {
        
                // 🔹 Variant table
                $limitRes = mysqli_query(
                    $con,
                    "SELECT v_p_limit FROM varient WHERE vid='$vid' AND product_id='$p_id' LIMIT 1"
                );
        
                if ($limitRes && mysqli_num_rows($limitRes) > 0) {
                    $limitRow = mysqli_fetch_assoc($limitRes);
                    $p_limit  = $limitRow['v_p_limit'];
                }
            }
        
            // ---------- CHECK LIMIT (ONLY if limit exists) ----------
            if ($p_limit !== null && $p_limit !== '' && $p_limit > 0) {
        
                if ($nop > $p_limit) {
                    echo "You have reached the purchase limit for this product .$p_limit";
                    exit;
                }
            }
            // 👉 yaha p_limit blank / null / 0 hoga to ignore ho jayega
        
            // ---------- UPDATE CART ----------
             // ---------- UPDATE CART ----------
            if (empty($vid) || $vid == 0) {
        
                // 🔥 No variant product
                $query = "
                    UPDATE cart 
                    SET nop='$nop' 
                    WHERE idfr='$idfr' 
                    AND user_id='$userId' 
                    AND status='true' 
                    AND p_id='$p_id'
                    AND (vid IS NULL OR vid = 0)
                ";
        
            } else {
        
                // 🔥 Variant product
                $query = "
                    UPDATE cart 
                    SET nop='$nop' 
                    WHERE idfr='$idfr' 
                    AND user_id='$userId' 
                    AND status='true' 
                    AND p_id='$p_id' 
                    AND vid='$vid'
                ";
            }
        
            $run = mysqli_query($con, $query);
        
            if ($run) {
                echo "success";
            } else {
                echo "error";
            }
        }

        
          else if ($type =='deleteCartItems'){
           
            $userId=$_POST['userId'];
            $idfr=$_POST['idfr'];
            $p_id=$_POST['p_id'];
            $vid=$_POST['vid']??null;
           
            $query="DELETE FROM `cart` WHERE idfr ='$idfr' AND user_id ='$userId' AND status ='true' AND p_id='$p_id' AND vid='$vid'";
            $run=mysqli_query($con,$query);
             
            if($run){
             
              echo 'success';
              
            }else{
                echo "error";
            }

        }
        
          else if ($type =='removeGiftProduct'){
           
            $id=$_POST['id'];
          
           
            $query="DELETE FROM `cart` WHERE id ='$id'";
            $run=mysqli_query($con,$query);
             
            if($run){
             
              echo 'success';
              
            }else{
                echo "error";
            }

        }
        
         else if ($type == 'loadCartCout') {

            $userId = $_POST['userId'] ?? null;
        
            // Check if userId is empty or null
            if (empty($userId)) {
                echo "error";
                exit();
            }
        
            $query = "SELECT * FROM `cart` WHERE user_id = '$userId' AND status = 'true' AND product_type != 'gift'";
            $run = mysqli_query($con, $query);
        
            if (mysqli_num_rows($run) > 0) {
                $number = mysqli_num_rows($run);
                echo $number;
            } else {
                echo "error";
            }
        }
        
       else if ($type == 'loadCartCount') {

    $userId = $_POST['userId'] ?? null;

    if (empty($userId)) {
        echo json_encode(["status" => "error", "message" => "User ID missing"]);
        exit();
    }

    $query = "SELECT p_id, vid ,nop FROM `cart` 
              WHERE user_id = '$userId' 
              AND status = 'true' 
              AND product_type != 'gift'";

    $run = mysqli_query($con, $query);

    if (mysqli_num_rows($run) > 0) {

        $products = [];

        while ($row = mysqli_fetch_assoc($run)) {
            $products[] = [
                "p_id" => $row['p_id'],
                "vid"  => $row['vid'],
                "nop" => $row['nop']
            ];
        }

        $response = [
            "status" => "success",
            "count"  => count($products),
            "products" => $products
        ];

        echo json_encode($response);

    } else {
        echo json_encode([
            "status" => "success",
            "count" => 0,
            "products" => []
        ]);
    }
}



        
          else if ($type =='loadCartItem'){ 
           
            $userId=$_POST['userId'];
            
            $idfr=$_POST['idfr'];
           
            $query="SELECT * FROM `cart` WHERE user_id ='$userId' AND status ='true'"; 
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
        
        //   else if ($type =='cartInc'){ 
           
        //     $id=$_POST['id'];
        //     $nop=$_POST['nop'];
           
        //     $query="UPDATE `cart` SET `nop`='$nop' WHERE id ='$id'";
        //     $run=mysqli_query($con,$query);
             
        //      if($run){ 
        //       echo "success";
        //     }else{
        //         echo "error";
        //     }

        // }
        
         else if ($type == 'cartInc') {

        $id  = $_POST['id'];   // cart id
        $nop = $_POST['nop'];  // quantity
    
        // 1️⃣ Cart se product_id lo
        $cartRes = mysqli_query($con, "SELECT p_id FROM cart WHERE id='$id' LIMIT 1");
        if (!$cartRes || mysqli_num_rows($cartRes) == 0) {
            echo "Cart not found";
            exit;
        }
    
        $cartRow   = mysqli_fetch_assoc($cartRes);
        $productId = $cartRow['p_id'];
    
        // 2️⃣ Product table se p_limit lo
        $prodRes = mysqli_query($con, "SELECT p_limit FROM product WHERE p_id='$productId' LIMIT 1");
        if (!$prodRes || mysqli_num_rows($prodRes) == 0) {
            echo "Product not found";
            exit;
        }
    
        $prodRow = mysqli_fetch_assoc($prodRes);
        $p_limit = $prodRow['p_limit'];
    
        // 3️⃣ CHECK LIMIT (sirf jab limit defined ho)
        if ($p_limit !== null && $p_limit !== '' && $p_limit > 0) {
    
            if ($nop > $p_limit) {
                echo "You have reached the purchase limit for this product"; 
                exit;
            }
        }
        // 👉 agar p_limit null / blank / 0 hua to yeh step skip ho jayega
    
        // 4️⃣ Update cart
        $query = "UPDATE cart SET nop='$nop' WHERE id='$id'";
        $run   = mysqli_query($con, $query);
    
        if ($run) {
            echo "success";
        } else {
            echo "error";
        }
    }
        
          else if ($type =='cartDelete'){ 
           
            $id=$_POST['id'];
            $nop=$_POST['nop'];
           
            $query="DELETE FROM `cart` WHERE id ='$id'";
            $run=mysqli_query($con,$query);
             if($run){ 
               echo "success";
            }else{
                echo "error";
            }

        }
        
          else if ($type =='checkGift'){ 
           
            // $total=$_POST['total'];
            $query="SELECT * FROM `other` WHERE type ='gift'";
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
        
          else if ($type =='loadGift'){ 
           
            // $total=$_POST['total'];
            $query="SELECT * FROM `gift`";
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
        
          else if ($type =='loadAddress'){ 
           
            $userId=$_POST['userId'];
            $query="SELECT * FROM `location` WHERE user_id ='$userId'";
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
        
         else if ($type =='loadCartTopProduct'){  

    $userId = $_POST['userId'];
    $idfr = $_POST['idfr'];

    // Step 1: Get product data from the cart
    $query = "SELECT * FROM `cart` WHERE user_id = '$userId' AND status = 'true' AND idfr = '$idfr'";
    $run = mysqli_query($con, $query);

    if(mysqli_num_rows($run) > 0) { 
        $rows = [];
        while($row = mysqli_fetch_assoc($run)){
            $rows[] = $row;
        }

        // Extract product IDs from the cart
        $productIds = array_column($rows, 'p_id'); // Assuming 'p_id' exists in the cart table
        $productIdsStr = implode(",", $productIds);

        // Step 2: Fetch subcategory IDs for these products
        $subcatQuery = "SELECT under_subcategory, COUNT(*) as product_count 
                        FROM `product` 
                        WHERE p_id IN ($productIdsStr) 
                        GROUP BY under_subcategory 
                        ORDER BY product_count DESC 
                        LIMIT 1";

        $subcatRun = mysqli_query($con, $subcatQuery);
        if(mysqli_num_rows($subcatRun) > 0) {
            $subcatData = mysqli_fetch_assoc($subcatRun);
            $topSubcategoryId = $subcatData['under_subcategory'];

            // Step 3: Fetch products from the top subcategory excluding those already in the cart
            $filteredQuery = "SELECT * FROM `product` 
                              WHERE under_subcategory = '$topSubcategoryId' AND status='true'
                              AND p_id NOT IN ($productIdsStr)"; // Exclude products already in cart
            $filteredRun = mysqli_query($con, $filteredQuery);

            if(mysqli_num_rows($filteredRun) > 0) {
                $filteredProducts = [];
                while($productRow = mysqli_fetch_assoc($filteredRun)) {
                    $filteredProducts[] = $productRow;
                }

                // Return the filtered product data
                echo json_encode($filteredProducts);
            } else {
                // echo "No products found in the top subcategory";
                echo "error";
            }
        } else {
            // echo "No subcategories found for these products";
               echo "error";
        }
    } else {
        echo "error";
    }
}


           
            
            else if ($type =='saveNewAddress'){ 
                
                $userId = $_POST['userId'];
                $username = $_POST['username'];
                $building_name = $_POST['building_name'];
                $floor = $_POST['floor'];
                $address = $_POST['address'];
                $city = $_POST['city'];
                $state = $_POST['state'];
                $pin_code = $_POST['pin_code'];
                $o_mobile = $_POST['o_mobile'];
                $forself = $_POST['forself'];
                $addressType = $_POST['addressType'];
                $o_mobile = $_POST['o_mobile'];
                $latitude = $_POST['latitude'] ?? null; 
                $longitude = $_POST['longitude'] ?? null; 
                 $date = date('Y-m-d H:i:s'); 
                $sql ="INSERT INTO `location`(`user_id`, `city`, `state`, `pin_code`, `o_username`, `o_mobile`, `street`, `o_floor`, `type`, `for`, `area`, `full_address`, `date`,`latitude`,`longitude`) VALUES ('$userId','$city','$state','$pin_code','$username','$o_mobile','$building_name','$floor','$addressType','$forself','$address','$address','$date','$latitude','$longitude')";
                 $run = mysqli_query($con, $sql);
                 
                     if ($run) {
                    $last_id = mysqli_insert_id($con);
                    echo json_encode([
                        "status" => "success",
                        "last_id" => $last_id
                    ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => mysqli_error($con)
                        ]);
                    }
 
            }
            
            
               else if ($type =='updateAddress'){ 
                
                $userId = $_POST['userId'];
                $username = $_POST['username'];
                $building_name = $_POST['building_name'];
                $floor = $_POST['floor'];
                $address = $_POST['address'];
                $city = $_POST['city'];
                $state = $_POST['state'];
                $pin_code = $_POST['pin_code'];
                $o_mobile = $_POST['o_mobile'];
                $forself = $_POST['forself'];
                $addressType = $_POST['addressType'];
                $o_mobile = $_POST['o_mobile'];
                $addressId = $_POST['addressId'];
             
                $sql ="UPDATE `location` SET `user_id`='$userId',`city`='$city',`state`='$state',`pin_code`='$pin_code',`o_username`='$username',`o_mobile`='$o_mobile',`street`='$building_name',`o_floor`='$floor',`type`='$addressType',`for`='$forself',`area`='$address',`full_address`='$address'WHERE `id` ='$addressId'";
                 $run = mysqli_query($con, $sql);
                 
                     if ($run) {
                   
                    echo json_encode([
                        "status" => "success",
                    ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => mysqli_error($con)
                        ]);
                    }
 
            }
            
             else if ($type =='deleteAddress'){
           
                $address_id=$_POST['address_id'];
                
                $query="DELETE FROM `location` WHERE id ='$address_id'";
                $run=mysqli_query($con,$query);
                if($run){
                  echo 'success';
                }else{
                    echo "error";
                }

        }
            
            
            // place order
            
            // else if ($type =='placeOrder'){ 
                
                
            //     $userId = $_POST['userId'];
            //     $address_id = $_POST['address_id'];
            //     $total = $_POST['total'];
            //     $date = $_POST['date'];
            //     $time = $_POST['time'];
            //     $orderType = $_POST['orderType'];
            //     $paymentMethode = $_POST['paymentMethode'];
            //     $idfr = $_POST['idfr'];
            //     $isGiftProduct = $_POST['isGiftProduct'];
            //     $deliveryCharge = $_POST['delCharge'];
            //     $handlingCharge = $_POST['handlingCharge']; 
            //     $coupon_id = $_POST['coupon_id'] ?? null; 
            //     $coupon_amount = $_POST['coupon_amount'] ?? null; 
              
               
                
            //     $dor = date('Y-m-d H:i:s'); 
                 
            //     $sql ="INSERT INTO `order`(`user_id`, `idfr`, `address_id`, `total`, `date`, `time`, `order_type`, `payment_methode`, `status`, `dor`,`del_charge`,`handling_charge`,`coupon_id`,`coupon_amount`) VALUES ('$userId','$idfr','$address_id','$total','$date','$time','$orderType','$paymentMethode','pending','$dor','$deliveryCharge','$handlingCharge','$coupon_id','$coupon_amount')"; 
            //      $run = mysqli_query($con, $sql);
                 
            //     // echo 'helo' ; die();
            //      if($run){ 
                     
            //           $last_id = mysqli_insert_id($con);
                      
            //         //   if coupon code is applied
                    
            //         if($coupon_id){
            //             $couponQuery="INSERT INTO `coupon_usages`(`user_id`, `order_id`, `coupon_id`, `used_at`, `usage_count`) VALUES ('$userId','$last_id','$coupon_id','$dor','1')";
            //               $couponRun = mysqli_query($con, $couponQuery);
            //         }
                      
            //         //  update the stock
                     
            //          $fetchCartData = "SELECT * FROM `cart` WHERE user_id = '$userId' AND idfr ='$idfr' AND product_type !='gift'";
            //           $getcartdata = mysqli_query($con, $fetchCartData);
                      
            //           while($productRow = mysqli_fetch_assoc($getcartdata)) {
            //                     $vid = $productRow['vid'];
            //                     $pid = $productRow['p_id'];
            //                     $nop = $productRow['nop'];
            //                     if($vid == null || $vid == '' || $vid =='0'){
            //                         $update ="UPDATE `product` SET stock = stock - $nop WHERE p_id ='$pid'";
            //                     }else{
            //                         $update ="UPDATE `varient` SET v_stock = v_stock - $nop WHERE vid ='$vid'";
            //                     }
            //                      $runupdate = mysqli_query($con, $update);
            //                   }
                      
                     
            //         if($isGiftProduct == 'false'){ 
            //             $deletequery ="DELETE FROM `cart` WHERE  user_id ='$userId' AND product_type ='gift' AND status = 'true'"; 
            //                 $rundeletequery = mysqli_query($con, $deletequery);
            //              }
                     
            //         $query ="UPDATE `cart` SET `status`='false' ,`idfr` = '$idfr' WHERE user_id='$userId' AND status ='true'";
            //         $run1 = mysqli_query($con, $query);
                      
                        
                         
            //           if($run1){
            //             echo "success";
            //           }
            //      }else{
            //           echo "error";
            //      }
 
            // }
            
             else if ($type == 'placeOrder') {

            $userId = $_POST['userId'];
            $address_id = $_POST['address_id'];
            $total = $_POST['total'];
            $date = $_POST['date'];
            $time = $_POST['time'];
            $orderType = $_POST['orderType'];
            $paymentMethode = $_POST['paymentMethode'];
            $idfr = $_POST['idfr'];
            $isGiftProduct = $_POST['isGiftProduct'];
            $deliveryCharge = $_POST['delCharge'];
            $handlingCharge = $_POST['handlingCharge'];
            $coupon_id = $_POST['coupon_id'] ?? null;
            $coupon_amount = $_POST['coupon_amount'] ?? null;
        
            $dor = date('Y-m-d H:i:s');
        
            // Step 1: Fetch cart items
            $fetchCartData = "SELECT * FROM `cart` WHERE user_id = '$userId' AND idfr ='$idfr' AND product_type !='gift'";
            $getcartdata = mysqli_query($con, $fetchCartData);
                        
                $isOutOfStock = false;
                $outOfStockProductName = '';
                
                $getcartdata = mysqli_query($con, $fetchCartData);
                while ($productRow = mysqli_fetch_assoc($getcartdata)) {
                    $vid = $productRow['vid'];
                    $pid = $productRow['p_id'];
                    $nop = $productRow['nop'];
                
                    if ($vid == null || $vid == '' || $vid == '0') {
                        $stockCheck = mysqli_query($con, "SELECT stock, name FROM `product` WHERE p_id = '$pid'");
                        $stockData = mysqli_fetch_assoc($stockCheck);
                        if ($stockData['stock'] < $nop) {
                            $isOutOfStock = true;
                            $outOfStockProductName = $stockData['name'];
                            break;
                        }
                    } else {
                        $stockCheck = mysqli_query($con, "SELECT v.v_stock, v.vid, p.name FROM `varient` v JOIN product p ON v.product_id = p.p_id WHERE v.vid = '$vid'");
                        $stockData = mysqli_fetch_assoc($stockCheck);
                        if ($stockData['v_stock'] < $nop) {
                            $isOutOfStock = true; 
                            $outOfStockProductName = $stockData['name']; // Product name + variant title
                            break;
                        }
                    }
                }
                
                if ($isOutOfStock) {
                    echo json_encode([
                        "status" => "out_of_stock",
                        "product" => $outOfStockProductName
                    ]);
                    return;
                }

        
            // Step 2: Insert order
            $sql = "INSERT INTO `order`(`user_id`, `idfr`, `address_id`, `total`, `date`, `time`, `order_type`, `payment_methode`, `status`, `dor`, `del_charge`, `handling_charge`, `coupon_id`, `coupon_amount` ,`new_order`) 
                    VALUES ('$userId','$idfr','$address_id','$total','$date','$time','$orderType','$paymentMethode','pending','$dor','$deliveryCharge','$handlingCharge','$coupon_id','$coupon_amount','true')"; 
            $run = mysqli_query($con, $sql);
        
            if ($run) {
                $last_id = mysqli_insert_id($con);
                $orderAutoId =$last_id;
                
              
                
                
                
                // if payment methode is wallet
                
                   if ($paymentMethode == 'Wallet') {

                // 1. Get current wallet balance
                $getQuery = "SELECT wallet_balance FROM user WHERE mobile = '$userId'";
                $getResult = mysqli_query($con, $getQuery);
            
                if (mysqli_num_rows($getResult) > 0) {
                    $row = mysqli_fetch_assoc($getResult);
                    $currentBalance = $row['wallet_balance'];
            
                    if ($currentBalance >= $amount) {
                        // 2. Minus amount from wallet
                        $newBalance = $currentBalance - $total;
                        $updateQuery = "UPDATE user SET wallet_balance = '$newBalance' WHERE mobile = '$userId'";
                        $updateResult = mysqli_query($con, $updateQuery);
            
                    
                        $historyQuery = "INSERT INTO wallet_history (order_id, user_id, amount, type , created_at) 
                                         VALUES ('$last_id','$userId', '$total', 'Debit', '$dor')";
                        $historyResult = mysqli_query($con, $historyQuery);
            
                        if ($updateResult && $historyResult) {
                            // echo "Wallet payment successful.";
                        } else {
                            // echo "Error updating wallet or recording history.";
                        }
                    } else {
                        // echo "Insufficient wallet balance.";
                    }
                } else {
                    // echo "User not found.";
                }
            }
                
        
                if ($coupon_id) {
                    $couponQuery = "INSERT INTO `coupon_usages`(`user_id`, `order_id`, `coupon_id`, `used_at`, `usage_count`) VALUES ('$userId','$last_id','$coupon_id','$dor','1')";
                    $couponRun = mysqli_query($con, $couponQuery);
                }
        
                // Re-fetch cart again for stock update
                $getcartdata = mysqli_query($con, $fetchCartData);
                while ($productRow = mysqli_fetch_assoc($getcartdata)) {
                    $vid = $productRow['vid'];
                    $pid = $productRow['p_id'];
                    $nop = $productRow['nop'];
                    if ($vid == null || $vid == '' || $vid == '0') {
                        $update = "UPDATE `product` SET stock = stock - $nop WHERE p_id = '$pid'";
                    } else {
                        $update = "UPDATE `varient` SET v_stock = v_stock - $nop WHERE vid = '$vid'";
                    }
                    mysqli_query($con, $update);
                }
        
                if ($isGiftProduct == 'false') {
                    $deletequery = "DELETE FROM `cart` WHERE user_id = '$userId' AND product_type = 'gift' AND status = 'true'";
                    mysqli_query($con, $deletequery);
                }
        
                $query = "UPDATE `cart` SET `status` = 'false', `idfr` = '$idfr' WHERE user_id = '$userId' AND status = 'true'";
                $run1 = mysqli_query($con, $query);
        
                if ($run1) {
                    echo "success";
                //   sendNewOrderMailToAdmin($orderAutoId);
                }
            } else {
                echo "error";
            }
            }
     


        
            //  load all order data 
            
            
            
            else if ($type =='loadOrder'){ 
                 $userId = $_POST['userId'];
                // echo $userId ; die();    
    // Order query
    $query = "SELECT * FROM `order` WHERE user_id = '$userId' ORDER BY `id` DESC";
    $run = mysqli_query($con, $query); 
    
    if (mysqli_num_rows($run) > 0) { 
        $orders = [];
        
        while ($orderRow = mysqli_fetch_assoc($run)) {
            $orderId = $orderRow['idfr']; // Assuming `id` is the primary key for the order table
 
                // echo $orderId ; die();   
            // Cart items for the current order
            $cartQuery = "SELECT * FROM `cart` WHERE idfr = '$orderId' AND status = 'false' AND user_id = '$userId'";
            $cartRun = mysqli_query($con, $cartQuery);
            $cartItems = [];

            if (mysqli_num_rows($cartRun) > 0) {
                while ($cartRow = mysqli_fetch_assoc($cartRun)) {
                    $cartItems[] = $cartRow;
                }
            }

            // Add cartItems to the order row
            $orderRow['cartItems'] = $cartItems;

            // Add the order row to the orders array
            $orders[] = $orderRow;
        }

        // Return all orders with cart items
        echo json_encode($orders);
    } else {
        echo 'error';
    }
}


        
        //  load all coupon 
        
          else if ($type =='loadCoupon'){ 
           
            $query="SELECT * FROM `coupon`";
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
             
            //  load all time slot 
          else if ($type =='loadTimeSlot'){ 
           
            $query="SELECT * FROM `time_slot`";
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
             
            //  load all delevery charge
          else if ($type =='loadDeliveryCharge'){ 
           
            $query="SELECT * FROM `delivery_charge`";
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
        
            //  load all handling bag charge
          else if ($type =='loadHandlingCharge'){ 
           
            $query="SELECT * FROM `other` WHERE `type` = 'handling_charge'";
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
             
            //  load min order value
          else if ($type =='loadMinOrderValue'){ 
           
            $query="SELECT * FROM `other` WHERE `type` = 'min_order_value'"; 
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
             
             
                 //  get brands of the day
         else if ($type =='getBrandsOfTheDay'){ 
           
            $query="SELECT a.*, b.id AS other_id, b.type, b.min_amount
                    FROM `brands` AS a
                    INNER JOIN `other` AS b ON a.id = b.min_amount
                    WHERE b.type = 'brands_of_the_day'"; 
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
             
            //  load user deatil
          else if ($type =='loadUserDetail'){ 
           
            $userid = $_POST['userid'];
           
            $query="SELECT * FROM `user` WHERE mobile ='$userid'";
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
             
            //  update user deatil
          else if ($type =='updateProfile'){ 
           
            $userid = $_POST['userid'];
            $fullName = $_POST['fullName'];
            $email = $_POST['email'];
            $mobileNumber = $_POST['mobileNumber'];
           
            $query="UPDATE `user` SET full_name ='$fullName' , email ='$email' , mobile ='$mobileNumber'  WHERE mobile ='$userid'";
            $run=mysqli_query($con,$query); 
            
             if($run){ 
                    echo 'success'; 
             }else{
                    echo 'error';
                }
             }
        
         else if($type=='getGooglemapApiKey'){
                 $platform = $_POST['platform'] ?? 'web';
                   $cordovaApiKey = 'AIzaSyA1Kj7UYcHrOCsN2R4U-MWGijJj_DPLpH8';
                    $webApiKey = 'AIzaSyA1Kj7UYcHrOCsN2R4U-MWGijJj_DPLpH8';
                
                    $apiKey = ($platform === 'cordova') ? $cordovaApiKey : $webApiKey;
                    echo json_encode(['apiKey' => $apiKey]);
             }
        
        //  check usages of coupon code 
        
        else if($type=='checkCouponUsages'){
             $userId = $_POST['userId'];
             $coupon_id = $_POST['coupon_id'];
             
            $query="SELECT * FROM `coupon_usages` WHERE user_id ='$userId' AND coupon_id ='$coupon_id'";
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
        
        
          else if($type=='checkStock'){
            
            $product_id = $_POST['product_id'];
            $nop = $_POST['nop'];
            $varient_id = $_POST['varient_id']; // optional
            
            $stock = 0; // default
            
            if (!empty($varient_id)) {
                // varient_id hai to varient table se stock nikalna
                $stockQuery = "SELECT v_stock FROM varient WHERE vid = '$varient_id'";
            } else {
                // varient_id nahi hai to product table se stock nikalna
                $stockQuery = "SELECT stock FROM product WHERE p_id = '$product_id'";
            }
            
            $stockResult = mysqli_query($con, $stockQuery);
            if ($stockResult && mysqli_num_rows($stockResult) > 0) {
                $stockRow = mysqli_fetch_assoc($stockResult);
                
                
                 if (!empty($varient_id)) {
                    $stock = $stockRow['v_stock']; 
                } else {
                    $stock = $stockRow['stock']; 
                }
                
            }
            
            // Return stock value
            echo json_encode([
                'stock' => $stock
            ]);

             
        } 
        
        else if($type=='shopDetails'){
            
             
            $query="SELECT * FROM `shop_info`";
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
        
        // get color code
        else if($type=='getColorCode'){
            
             
            $query="SELECT * FROM `color_code`"; 
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
        
        
        // get all title 
         else if ($type =='getAllTitle'){
          
            $query="SELECT * FROM `header_title`"; 

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
        
        // get all main title
         else if ($type =='getAllMainTitle'){
          
            $query="SELECT * FROM `main_header`"; 

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
        
         else if ($type =='getHeroBanner'){
             
            //  echo 'error'; die();
          
            $query="SELECT * FROM `hero_banner`"; 

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
        
         else if ($type =='getContactDetails'){
          
            $query="SELECT * FROM `contact_info`"; 

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
        
        
        else if ($type == 'orderstatus') {
            $order_id = $_POST['orderid'];
        
            $query = "SELECT status FROM order_table WHERE razorpay_order_id='$order_id' LIMIT 1";
            $result = mysqli_query($con, $query);
        
            $response = ["status" => false];
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $response["status"] = ($row['status'] === 'true');
            }
        
            echo json_encode($response);
        }
        
           // check wallet balance 
        
        else if($type == 'checkWalletBalance'){
            
            $userId = $_POST['userId'];
            $query="SELECT wallet_balance FROM user WHERE mobile = '$userId'";
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
        
          else if ($type == 'refreAndEarn') {

            $userId = $_POST['mobileNumber'];
        
            $query = "SELECT refrel_code FROM user WHERE mobile = '$userId' LIMIT 1";
            $run   = mysqli_query($con, $query);
        
            if ($run && mysqli_num_rows($run) > 0) {
        
                $row = mysqli_fetch_assoc($run);
        
                // 🔹 Referral reward amount
                $rewardAmount = 50;
        
                $response = [
                    "status" => "success",
                    "refrel_code" => $row['refrel_code'],
                    "message" => "Invite your friends and earn ₹$rewardAmount when they sign up using your referral code."
                ];
        
                echo json_encode($response);
        
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "User not found"
                ]);
            }
        }
        
         // fet app link
        else if($type=='loadAppLink'){
            
             
            $query="SELECT * FROM `shop_info`"; 
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
        
         else if($type=='loadOtherInfo'){
            
             
            $query="SELECT * FROM `other_info`"; 
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
        
             // check stock of the product
        
            else if ($type == 'checkCartItemStock') { 
            $userId = $_POST['user_id'];
         
            // cart aur product table join karte hain based on product_id
            $query = "
                SELECT 
            cart.id AS cart_id,
            cart.user_id,
            cart.p_id,
            cart.vid,
            cart.quantity,
            cart.unit,
            cart.nop,
            cart.status AS cart_status,
            cart.name,
            CASE 
                WHEN cart.vid = '' OR cart.vid = '0' THEN product.stock 
                ELSE varient.v_stock 
            END AS stock
        FROM cart
        LEFT JOIN product ON cart.p_id = product.p_id
        LEFT JOIN varient ON cart.vid = varient.vid
        WHERE cart.user_id = '$userId' 
          AND cart.status = 'true'"; 
        
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
        
        
        else if ($type == 'loadCartItem2') { 
        $userId = $_POST['userId'];
        $idfr = $_POST['idfr'];
    
        $query = "
            SELECT 
                cart.*,
                CASE 
                    WHEN cart.vid = '' OR cart.vid = '0' THEN product.stock
                    ELSE varient.v_stock
                END AS stock
            FROM cart
            LEFT JOIN product ON cart.p_id = product.p_id
            LEFT JOIN varient ON cart.vid = varient.vid
            WHERE cart.user_id = '$userId' AND cart.status = 'true'
        ";
    
        $run = mysqli_query($con, $query);
        
        if (mysqli_num_rows($run) > 0) { 
            while ($row = mysqli_fetch_assoc($run)) {
                $rows[] = $row;
            }	
            echo json_encode($rows);
        } else {
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
            
            else if($type=='loadMainBanner'){
                
                // echo 'error';die();
                
                     $banners = [
                    [
                        'id' => 1,
                        'banner_type' => 'main',
                        'img_path' => 'api/main2.webp'
                    ], 
                    [
                        'id' => 2,
                        'banner_type' => 'child',
                        'img_path' => 'api/child2.webp'
                    ]
                    ];
                    
                $query ="SELECT * FROM `main_banner`"; 
                $run=mysqli_query($con,$query);
                if(mysqli_num_rows($run)>0){
                    while($row=mysqli_fetch_assoc($run)){
                        $rows[]=$row;
                    }; 	
                    echo json_encode($rows);
                    }else{
                        echo "error";
                    }
            
                    // convert array to JSON and print
                    //   echo json_encode($rows);
            }
            
            else if ($type == 'checkUserBlockedOrNot') {

            $mobile = $_POST['mobileNumber'];
        
            $query = "SELECT status FROM user WHERE mobile = '$mobile' LIMIT 1";
            $run   = mysqli_query($con, $query);
        
            if (mysqli_num_rows($run) > 0) {
        
                $row = mysqli_fetch_assoc($run);
        
                if ($row['status'] == 'true') {
                    echo json_encode([
                        "status" => "notblocked"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "blocked"
                    ]);
                }
        
            } else {
                echo json_encode([
                    "status" => "user_not_found"
                ]);
            }
        }

            
            else if($type =='loadAllPincode'){
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
     

         elseif($type == "fetchAvailableSlots") {
             $query = "SELECT * FROM `time_slot` WHERE STR_TO_DATE(SUBSTRING_INDEX(slot, ' - ', 1),'%h:%i %p') > CURTIME() ORDER BY STR_TO_DATE(SUBSTRING_INDEX(slot, '-', 1),'%h:%i %p') ASC LIMIT 1";
             $run = mysqli_query($con,$query);
             if(mysqli_num_rows($run)) {
                 $row = mysqli_fetch_assoc($run);
                 echo json_encode($row);
             }else{
                 echo 0;
             }
         }     

?>