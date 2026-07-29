<?php
date_default_timezone_set("Asia/Kolkata");
header("Access-Control-Allow-Origin:*"); 

 $con=mysqli_connect("localhost","root","","u907727509_Krishanthmart");
  if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL The Eroor is : " . mysqli_connect_error();
  }
    mysqli_query($con, "SET time_zone = '+05:30'");
    $type=$_REQUEST['type'];  
    
  if ($type == "handleLoginOtp") {

    $phone = $_POST['phone'] ?? '';

    $selectQuery = "SELECT * FROM `user` WHERE `mobile`='$phone'";
    $res = mysqli_query($con, $selectQuery);

    if (mysqli_num_rows($res) > 0) {

        $userData = mysqli_fetch_assoc($res);

        echo json_encode([
            "status"  => "success",
            "message" => "login successfully",
            "userId"  => $userData['user_id']
        ]);

    } else {

        $query = "INSERT INTO `user`
        (`full_name`,`mobile`,`email`,`password`,
        `refrel_code`,`wallet_balance`,`device_id`,
        `user_mode`,`status`,`date`)
        VALUES
        ('','$phone','','',
        '','','','','true',NOW())";

        $result = mysqli_query($con, $query);

        if ($result) {

            echo json_encode([
                "status"  => "success",
                "message" => "registered successfully",
                "userId"  => mysqli_insert_id($con)
            ]);

        } else {

            echo json_encode([
                "status"  => "failed",
                "message" => mysqli_error($con)
            ]);
        }
    }
 }
    else if($type == "getCategory"){
         $query = "SELECT * FROM `category` WHERE `status`='true'";
         $res = mysqli_query($con,$query);
         if(mysqli_num_rows($res)>0){
            $data=[];

            while($row= mysqli_fetch_assoc($res)){
                   $data[]=$row;
            }

         echo json_encode([
                "status" => "success",
                "message" => "category fetched successfully !", 
                "data" => $data
            ]);
         }
         else{
              echo json_encode([
                "status" => "failed",
                "message" => "something wents wrong on api : getCategory", 
                "data" => []
            ]);
         }
         
    }
     else if($type == "getTopHeroBanner"){
        $id=$_POST['categoryId'];
        $query = "SELECT * FROM `banner` WHERE `type`='main' AND `status`='true' AND `under_category`='$id'";
         $res = mysqli_query($con,$query);
       if(mysqli_num_rows($res)>0){
            $data=[];

            while($row= mysqli_fetch_assoc($res)){
                   $data[]=$row;
            }

         echo json_encode([
                "status" => "success",
                "message" => "Banner fetched successfully !", 
                "data" => $data
            ]);
         }
         else{
              echo json_encode([
                "status" => "failed",
                "message" => "something wents wrong on api : getTopHeroBanner", 
                "data" => []
            ]);
         }
    }
    else if($type == "getFlashSalePrd"){
                $id=$_POST['categoryId'];
// 9,8,7,20
        
        $query = "SELECT * FROM `product` WHERE `flash_sale`='true' AND `status`='true' AND `under_category`='$id'";
         $res = mysqli_query($con,$query);
       if(mysqli_num_rows($res)>0){
            $data=[];

            while($row= mysqli_fetch_assoc($res)){
                   $data[]=$row;
            }

         echo json_encode([
                "status" => "success",
                "message" => "Banner fetched successfully !", 
                "data" => $data
            ]);
         }
         else{
              echo json_encode([
                "status" => "failed",
                "message" => "something wents wrong on api : getTopLeftBanner", 
                "data" => []
            ]);
         }
    }
    else if($type == "getTopRightBanner"){
        $query = "SELECT * FROM `banner` WHERE `type`='topRight' AND `status`='true'";
         $res = mysqli_query($con,$query);
       if(mysqli_num_rows($res)>0){
            $data=[];

            while($row= mysqli_fetch_assoc($res)){
                   $data[]=$row;
            }

         echo json_encode([
                "status" => "success",
                "message" => "Banner fetched successfully !", 
                "data" => $data
            ]);
         }
         else{
              echo json_encode([
                "status" => "failed",
                "message" => "something wents wrong on api : getTopRightBanner", 
                "data" => []
            ]);
         }
    }

  else if($type == "getArrivalsData") {

        $categoryId = $_POST['categoryId'];

        $query = "SELECT * FROM `subcategory`
                WHERE `title1` = 'true'
                AND `status` = 'true'
                AND `under_category` = '$categoryId'";

        $res = mysqli_query($con, $query);

        // Data Found
        if (mysqli_num_rows($res) > 0) {

            $data = [];

            while ($row = mysqli_fetch_assoc($res)) {
                $data[] = $row;
            }

            echo json_encode([
                "status" => "success",
                "message" => "Data fetched successfully!",
                "data" => $data
            ]);

        } else {

            echo json_encode([
                "status" => "failed",
                "message" => "No data found",
                "data" => []
            ]);
        }
}
 else if($type == "getSubCategories"){

    $categoryId = $_POST['categoryId'];

    $query = "SELECT *
              FROM `subcategory`
              WHERE `status` = 'true'
              AND `under_category` = '$categoryId'";

    $res = mysqli_query($con, $query);

    $titles = [];

    while($row = mysqli_fetch_assoc($res)){

        foreach($row as $key => $value){

            // Check all title columns dynamically
            if(strpos($key, "title") === 0 && $value == "true"){

                if(!isset($titles[$key])){
                    $titles[$key] = [];
                }

                $titles[$key][] = $row;
            }
        }
    }

    echo json_encode([
        "status"  => "success",
        "message" => "All subcategory data fetched successfully!",
        "data"    => $titles
    ]);
}
else if($type=="getAllProduct"){

   $query = "SELECT * FROM product";
   $res=mysqli_query($con,$query);
   if(mysqli_num_rows($res)>0){
    $data=[];
    while ($row=mysqli_fetch_assoc($res)) {
         $data[]=$row;
    }
    echo json_encode([
        "status"=>"success",
        "message"=>"product fetched successfully !",
        "data"=>$data
    ]);
   }else{
    echo json_encode([
        "status"=>"success",
        "message"=>"something wents wrong !",
        "data"=>[]
    ]);
   }

}
  else if($type == "getProducts"){
    $categoryId = $_POST['categoryId'];

     $query1 = "SELECT 
            p.*,
            (
                SELECT COUNT(*)
                FROM `varient` v
                WHERE v.product_id = p.p_id
            ) AS varient_count
          FROM `product` p
          WHERE p.`status` = 'true'
          AND p.`under_category` = '$categoryId'
          AND (
                p.`title1` = 'true'
                OR p.`title2` = 'true'
                OR p.`title3` = 'true'
                OR p.`title4` = 'true'
                OR p.`title5` = 'true'
                OR p.`title6` = 'true'
    )";
    $query2 = "SELECT * FROM product";
      $categoryId = $_POST['categoryId'];

   $res1 = mysqli_query($con, $query1);
    $res2 = mysqli_query($con, $query2);

    $title1 = [];
    $title2 = [];
    $title3 = [];
    $title4 = [];
    $title5 = [];
    $title6 = [];
    $allData = [];

    while ($row = mysqli_fetch_assoc($res1)) {

        if ($row['title1'] === 'true') $title1[] = $row;
        if ($row['title2'] === 'true') $title2[] = $row;
        if ($row['title3'] === 'true') $title3[] = $row;
        if ($row['title4'] === 'true') $title4[] = $row;
        if ($row['title5'] === 'true') $title5[] = $row;
        if ($row['title6'] === 'true') $title6[] = $row;
    }

    while ($row = mysqli_fetch_assoc($res2)) {
        $allData[] = $row;
    }

    echo json_encode([
    "status" => "success",
    "message" => "Products fetched successfully.",
    "data" => [
        "title1" => $title1,
        "title2" => $title2,
        "title3" => $title3,
        "title4" => $title4,
        "title5" => $title5,
        "title6" => $title6,
        "allData" => $allData
    ]
    ]);
 
  }
else if($type == "getBestSellingPrd"){

    $categoryId = $_POST['categoryId'];

    // Header Titles
    $headerQuery = "SELECT * FROM `header_title`
                    WHERE `title_type`='best_selling_grocery'";
    $headerRes = mysqli_query($con, $headerQuery);

    $headers = [];
    while($row = mysqli_fetch_assoc($headerRes)){
        $headers[] = $row;
    }

    // Products
    $productQuery = "SELECT
                        p.*,
                        (
                            SELECT COUNT(*)
                            FROM `varient` v
                            WHERE v.product_id = p.p_id
                        ) AS varient_count
                    FROM `product` p
                    WHERE p.`status`='true'
                    AND p.`under_category`='$categoryId'";

    $productRes = mysqli_query($con, $productQuery);

    $titles = [];

    while($row = mysqli_fetch_assoc($productRes)){

        foreach($row as $key => $value){

            // All btitle columns
            if(strpos($key, 'btitle') === 0 && $value == 'true'){

                if(!isset($titles[$key])){
                    $titles[$key] = [];
                }

                $titles[$key][] = $row;
            }
        }
    }

    echo json_encode([
        "status"  => "success",
        "message" => "Best selling products fetched successfully!",
        "header"  => $headers,
        "data"    => $titles
    ]);
}
else if($type == "getNewFindPrd"){
    $categoryId = $_POST['categoryId'];

    // Header Titles
    $headerQuery = "SELECT * FROM `header_title`
                    WHERE `title_type`='new_finds'";
    $headerRes = mysqli_query($con, $headerQuery);

    $headers = [];
    while($row = mysqli_fetch_assoc($headerRes)){
        $headers[] = $row;
    }

    // Products
    $productQuery = "SELECT
                        p.*,
                        (
                            SELECT COUNT(*)
                            FROM `varient` v
                            WHERE v.product_id = p.p_id
                        ) AS varient_count
                    FROM `product` p
                    WHERE p.`status`='true'
                    AND p.`under_category`='$categoryId'";

    $productRes = mysqli_query($con, $productQuery);

    $titles = [];

    while($row = mysqli_fetch_assoc($productRes)){

        foreach($row as $key => $value){

            // All dtitle columns
            if(strpos($key, 'dtitle') === 0 && $value == 'true'){

                if(!isset($titles[$key])){
                    $titles[$key] = [];
                }

                $titles[$key][] = $row;
            }
        }
    }

    echo json_encode([
        "status"  => "success",
        "message" => "new_finds products fetched successfully!",
        "header"  => $headers,
        "data"    => $titles
    ]);
}
  else if($type == "getAllHeading"){

    $query = "SELECT * FROM `header_title`
        WHERE `title_type` IN ('top_subcategory', 'top_product','new_finds')";
    $res = mysqli_query($con, $query);

    $categoryHeading = [];
    $productHeading = [];
    $newFindHeading = [];

    while($row = mysqli_fetch_assoc($res)){

        if($row['title_type']=="top_subcategory"){
            $categoryHeading[] = $row;
        }

        if($row['title_type']=="top_product"){
            $productHeading[] = $row;
        }


        if($row['title_type']=="new_finds"){
            $newFindHeading[] = $row;
        }
    }
     echo json_encode([
        "status" => "success",
        "message" => "All heading data fetched successfully!",
        "data" => [
            "categoryHeading" => $categoryHeading,
            "productHeading" => $productHeading,
            "newFindHeading" => $newFindHeading,
        ]
    ]);
             
  }
 else if($type == "handleIncrement"){

    $user_id        = $_POST['user_id'] ?? '';
    $p_id           = $_POST['p_id'] ?? '';
    $vid            = $_POST['vid'] ?? '';
    $name           = $_POST['name'] ?? '';
    $image_path     = $_POST['image_path'] ?? '';
    $quantity       = $_POST['quantity'] ?? '';
    $unit           = $_POST['unit'] ?? '';
    $nop            = $_POST['nop'] ?? '';
    $purchase_price = $_POST['purchase_price'] ?? '';
    $selling_price  = $_POST['selling_price'] ?? '';
    $mrp            = $_POST['mrp'] ?? '';
    $isvarient      = $_POST['isvarient'] ?? '';
    $product_type   = $_POST['product_type'] ?? '';
    $status         = $_POST['status'] ?? '';
    $idfr           = $_POST['idfr'] ?? '';

    if (!empty($vid)) {

        $checkQuery = "SELECT * FROM `cart`
                    WHERE `user_id`='$user_id'
                    AND `p_id`='$p_id'
                    AND `vid`='$vid' 
                    AND `status` = 'true'";

    } else {

        $checkQuery = "SELECT * FROM `cart`
                    WHERE `user_id`='$user_id'
                    AND `p_id`='$p_id'
                     AND `status` = 'true'";
    }

    $checkRes = mysqli_query($con, $checkQuery);



    $checkRes = mysqli_query($con, $checkQuery);

    if(mysqli_num_rows($checkRes) > 0){

      if (!empty($vid)) {

    $updateQuery = "UPDATE `cart`
                    SET `nop`='$nop'
                    WHERE `user_id`='$user_id'
                    AND `p_id`='$p_id'
                    AND `vid`='$vid'
                     AND `status` = 'true'";

   } else {

    $updateQuery = "UPDATE `cart`
                    SET `nop`='$nop'
                    WHERE `user_id`='$user_id'
                    AND `p_id`='$p_id'
                     AND `status` = 'true'";
  }

        $updateRes = mysqli_query($con, $updateQuery);

        if($updateRes){
            echo json_encode([
                "status" => "success",
                "message" => "quantity updated"
            ]);
        }else{
            echo json_encode([
                "status" => "failed",
                "message" => mysqli_error($con)
            ]);
        }

    }else{

        // Insert new product
        $insertQuery = "INSERT INTO `cart`
        (
            `user_id`,
            `idfr`,
            `p_id`,
            `vid`,
            `name`,
            `image_path`,
            `quantity`,
            `unit`,
            `nop`,
            `purchase_price`,
            `selling_price`,
            `mrp`,
            `isvarient`,
            `product_type`,
            `status`,
            `date`
        )
        VALUES
        (
            '$user_id',
            '$idfr',
            '$p_id',
            '$vid',
            '$name',
            '$image_path',
            '$quantity',
            '$unit',
            '$nop',
            '$purchase_price',
            '$selling_price',
            '$mrp',
            '$isvarient',
            '$product_type',
            '$status',
            NOW()
        )";

        $insertRes = mysqli_query($con, $insertQuery);

        if($insertRes){
            echo json_encode([
                "status" => "success",
                "message" => "product added to cart",
                "data"=>$idfr
            ]);
        }else{
            echo json_encode([
                "status" => "failed",
                "message" => mysqli_error($con)
            ]);
        }
    }
 }
 else if($type == "handleDecrement"){

    $user_id  = $_POST['user_id'] ?? '';
    $p_id     = $_POST['p_id'] ?? '';
    $vid      = $_POST['varId'] ?? '';
    $nop = $_POST['nop'] ?? '';

    if($nop <= 0){

        if (!empty($vid)) {
        $updateQuery = "UPDATE `cart`
                        SET `status` = 'false'
                        WHERE `user_id` = '$user_id'
                        AND `p_id` = '$p_id'
                        AND `vid` = '$vid'
                        AND `status` = 'true'";
    } else {
        $updateQuery = "UPDATE `cart`
                        SET `status` = 'false'
                        WHERE `user_id` = '$user_id'
                        AND `p_id` = '$p_id'
                        AND `status` = 'true'";
    }

        $res = mysqli_query($con, $updateQuery);

        if($res){
            echo json_encode([
                "status"=>"success",
                "message"=>"product removed from cart"
            ]);
        }

    }else{

        if(!empty($vid)){
            $updateQuery = "UPDATE `cart`
                            SET `nop`='$nop'
                            WHERE `user_id`='$user_id'
                            AND `p_id`='$p_id'
                            AND `vid`='$vid'
                             AND `status` = 'true'";
        }else{
            $updateQuery = "UPDATE `cart`
                            SET `nop`='$nop'
                            WHERE `user_id`='$user_id'
                            AND `p_id`='$p_id'
                             AND `status` = 'true'";
        }

        $res = mysqli_query($con, $updateQuery);

        if($res){
            echo json_encode([
                "status"=>"success",
                "message"=>"quantity updated"
            ]);
        }
    }
}
  else if($type == "getSingleVarientId"){
    $id=$_POST['id'];
    $query = "SELECT * FROM `varient` WHERE `product_id`='$id'";
    $res = mysqli_query($con,$query);
    
    if (mysqli_num_rows($res)>0) {
      $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
           $data[]=$row;
    }
    echo json_encode([
        "status"=>"success",
        "message"=>"product get successfully !",
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
  else if($type == "getSingleProduct"){

    $id = $_POST['id'];

    // Product Data
    $productQuery = "SELECT * FROM `product`
                     WHERE `p_id`='$id'";

    $productRes = mysqli_query($con,$productQuery);

    if(mysqli_num_rows($productRes) > 0){

        $productData = mysqli_fetch_assoc($productRes);

        // Product Images
        $imageQuery = "SELECT * FROM `product_img`
                       WHERE `product_id`='$id'";

        $imageRes = mysqli_query($con,$imageQuery);

        $images = [];

        while($row = mysqli_fetch_assoc($imageRes)){
            $images[] = $row;
        }

        // Product Variants
        $variantQuery = "SELECT * FROM `varient`
                         WHERE `product_id`='$id'";

        $variantRes = mysqli_query($con,$variantQuery);

        $variants = [];

        while($row = mysqli_fetch_assoc($variantRes)){
            $variants[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Product fetched successfully",
            "product" => $productData,
            "images" => $images,
            "variants" => $variants
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => "Product not found"
        ]);
    }
 }
 else if($type == "getCart"){
    $userId = $_POST['userId'];
    $query="SELECT * FROM `cart` WHERE `user_id` ='$userId' AND `status`='true'";


    $res= mysqli_query($con,$query);

    // Data Found
    if (mysqli_num_rows($res) > 0) {

        $data = [];

        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Cart Data fetched successfully!",
            "data" => $data
        ]);

    } else {

        echo json_encode([
            "status" => "failed",
            "message" => "No data found on : getCart",
            "data" => []
        ]);
    }
 }
 else if($type == "getAllVarient"){
    $query = "SELECT * FROM `varient`";
    $res= mysqli_query($con,$query);

    // Data Found
    if (mysqli_num_rows($res) > 0) {

        $data = [];

        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "varient Data fetched successfully!",
            "data" => $data
        ]);

    } else {

        echo json_encode([
            "status" => "failed",
            "message" => "No data found on : getAllVarient",
            "data" => []
        ]);
    }

 }
 else if($type == "getAllOtherDetail"){
    $query = "SELECT * FROM `other`";
       $res= mysqli_query($con,$query);

    // Data Found
    if (mysqli_num_rows($res) > 0) {

        $data = [];

        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "other Data fetched successfully!",
            "data" => $data
        ]);

    } else {

        echo json_encode([
            "status" => "failed",
            "message" => "No data found on : getAllOtherDetail",
            "data" => []
        ]);
    }
 }
else if($type == "handleAddress"){

    $user_id    = $_POST['userId'] ?? '';
    $city       = $_POST['city'] ?? '';
    $state      = $_POST['state'] ?? '';
    $pin_code   = $_POST['pincode'] ?? '';

    $o_username = $_POST['name'] ?? '';
    $o_mobile   = $_POST['number'] ?? '';

    $street     = $_POST['houseNo'] ?? '';
    $o_floor    = $_POST['floor'] ?? '';

    $address_type = $_POST['addressType'] ?? '';

    $area       = $_POST['area'] ?? '';

    $latitude   = $_POST['latitude'] ?? '';
    $longitude  = $_POST['longitude'] ?? '';

    // Default Value
    $for = "Self";

    // Full Address
    $full_address = $street;

    if(!empty($o_floor)){
        $full_address .= ", Floor: ".$o_floor;
    }

    $full_address .= ", ".$area.", ".$city.", ".$state." - ".$pin_code;

    $query = "INSERT INTO `location`
    (
        `user_id`,
        `city`,
        `state`,
        `pin_code`,
        `o_username`,
        `o_mobile`,
        `street`,
        `o_floor`,
        `type`,
        `for`,
        `area`,
        `full_address`,
        `latitude`,
        `longitude`,
        `date`
    )
    VALUES
    (
        '$user_id',
        '$city',
        '$state',
        '$pin_code',
        '$o_username',
        '$o_mobile',
        '$street',
        '$o_floor',
        '$address_type',
        '$for',
        '$area',
        '$full_address',
        '$latitude',
        '$longitude',
        NOW()
    )";

    $res = mysqli_query($con, $query);

    if($res){

        echo json_encode([
            "status" => "success",
            "message" => "Address added successfully!"
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => mysqli_error($con)
        ]);

    }
}
else if($type == "getAddress"){

    $userId = $_POST['userId'] ?? '';

    $query = "SELECT * FROM `location`
              WHERE `user_id`='$userId'";

    $res = mysqli_query($con, $query);

    if(mysqli_num_rows($res) > 0){

        $data = [];

        while($row = mysqli_fetch_assoc($res)){
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Location data fetched successfully!",
            "data" => $data
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => "No address found.",
            "data" => []
        ]);

    }
}
else if($type == "getCurrentAddress"){
    $userId=$_POST['userId'];
    $addressId=$_POST['addressId'];
    $query = "SELECT * FROM `location` WHERE `user_id`='$userId' AND `id`='$addressId'";
    // SELECT * FROM `location` WHERE `user_id`='8' AND `id`='24';

    // echo $query; exit();
    $res=mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"get currentAddress successfully !",
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
 else if($type == "updateAddress"){

    $address_id = $_POST['addressId'] ?? '';
    $user_id    = $_POST['userId'] ?? '';

    $city       = $_POST['city'] ?? '';
    $state      = $_POST['state'] ?? '';
    $pin_code   = $_POST['pincode'] ?? '';

    $o_username = $_POST['name'] ?? '';
    $o_mobile   = $_POST['number'] ?? '';

    $street     = $_POST['houseNo'] ?? '';
    $o_floor    = $_POST['floor'] ?? '';

    $address_type = $_POST['addressType'] ?? '';

    $area       = $_POST['area'] ?? '';

    $latitude   = $_POST['latitude'] ?? '';
    $longitude  = $_POST['longitude'] ?? '';

    // Default Value
    $for = "Self";

    // Full Address
    $full_address = $street;

    if(!empty($o_floor)){
        $full_address .= ", Floor: ".$o_floor;
    }

    $full_address .= ", ".$area.", ".$city.", ".$state." - ".$pin_code;

    $query = "UPDATE `location`
              SET
                `city`='$city',
                `state`='$state',
                `pin_code`='$pin_code',
                `o_username`='$o_username',
                `o_mobile`='$o_mobile',
                `street`='$street',
                `o_floor`='$o_floor',
                `type`='$address_type',
                `for`='$for',
                `area`='$area',
                `full_address`='$full_address',
                `latitude`='$latitude',
                `longitude`='$longitude'
              WHERE `id`='$address_id'
              AND `user_id`='$user_id'";

    $res = mysqli_query($con, $query);

    if($res){

        echo json_encode([
            "status" => "success",
            "message" => "Address updated successfully!"
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => mysqli_error($con)
        ]);

    }

}
else if($type == "deleteAddress"){

    $address_id = $_POST['addressId'] ?? '';
    $user_id    = $_POST['userId'] ?? '';

    $query = "DELETE FROM `location`
              WHERE `id`='$address_id'
              AND `user_id`='$user_id'";

    $res = mysqli_query($con, $query);

    if($res){

        echo json_encode([
            "status" => "success",
            "message" => "Address deleted successfully!"
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => mysqli_error($con)
        ]);

    }

}
else if($type == "getCoupons"){

    $today = date("Y-m-d");

   $query = "SELECT *
          FROM `coupon`
          WHERE '$today' BETWEEN `start_date` AND `end_date`
          AND `limit` > 0
          ORDER BY `id` DESC";

    $res = mysqli_query($con, $query);

    if(mysqli_num_rows($res) > 0){

        $data = [];

        while($row = mysqli_fetch_assoc($res)){
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Coupon data fetched successfully!",
            "data" => $data
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => "No active coupon found.",
            "data" => []
        ]);

    }

}
else if($type == "updateCoupon"){
    $id=$_POST['id'];
    $limit=$_POST['limit'];
    $query = "UPDATE `coupon` SET `limit` = '$limit' WHERE `coupon`.`id` = '$id'";
    $res =mysqli_query($con,$query);
    if($res){

        echo json_encode([
            "status" => "success",
            "message" => "Coupons updated successfully!"
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => mysqli_error($con)
        ]);

    }
}
else if($type == "usedCoupons"){
      $today = date("Y-m-d");
    $query = "SELECT *
          FROM `coupon`
          WHERE `end_date` < '$today'
          ORDER BY `id` DESC";
     $res = mysqli_query($con, $query);

    if(mysqli_num_rows($res) > 0){

        $data = [];

        while($row = mysqli_fetch_assoc($res)){
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Used Coupon data fetched successfully!",
            "data" => $data
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => "No expired coupon found.",
            "data" => []
        ]);

    }

}
else if ($type == "handleOrder") {

    $user_id        = $_POST['user_id'] ?? '';
    $payment_method = $_POST['payMethod'] ?? '';
    $address_id     = $_POST['selectAddress'] ?? '';
    $order_type     = $_POST['orderType'] ?? '';
    $selected_slot  = $_POST['selectedSlot'] ?? '';
    $coupon_id      = $_POST['couponId'] ?? '';
    $total          = $_POST['totalAmount'] ?? 0;
    $handling_charge = $_POST['handlingCharge'] ?? 0;
    $coupon_amount   = $_POST['couponAmt'] ?? 0;

    $delivery_charge = $_POST['deliveryCharge'] ?? 0 ;

    $status = "pending";
    $new_order = "true";

    $date = date("Y-m-d");
    $time = $selected_slot;
    $dor  = date("Y-m-d H:i:s");

    // Unique Order Id
    $idfr = $_POST['idfr'];

    // Insert Order
    $query1 = "INSERT INTO `order`
    (
        `user_id`,
        `idfr`,
        `address_id`,
        `total`,
        `date`,
        `time`,
        `order_type`,
        `payment_method`,
        `del_charge`,
        `handling_charge`,
        `coupon_id`,
        `coupon_amount`,
        `status`,
        `new_order`,
        `dor`
    )
    VALUES
    (
        '$user_id',
        '$idfr',
        '$address_id',
        '$total',
        '$date',
        '$time',
        '$order_type',
        '$payment_method',
        '$delivery_charge',
        '$handling_charge',
        '$coupon_id',
        '$coupon_amount',
        '$status',
        '$new_order',
        '$dor'
    )";
    
    $query2 = "UPDATE varient v
    JOIN cart c ON v.vid = c.vid
    SET v.v_stock = v.v_stock - c.nop
    WHERE c.idfr = '$idfr'";  
    $query3 = "UPDATE `cart` SET `status` = 'false' WHERE `cart`.`idfr` = '$idfr'";
    
    // echo $query3; exit();

   $result1 = mysqli_query($con, $query1);
   $result2 = mysqli_query($con, $query2);
   $result3 = mysqli_query($con, $query3);

   if ($result1 && $result2 && $result3) {
    echo json_encode([
        "status" => "success",
        "message" => "Order placed successfully!"
    ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => mysqli_error($con)
        ]);
    }
}
else if($type == "getOrder"){

    $userId = $_POST['userId'];
    $query = "SELECT * FROM `order` WHERE `user_id`='$userId'";
    $res = mysqli_query($con,$query);

      if(mysqli_num_rows($res) > 0){

        $data = [];

        while($row = mysqli_fetch_assoc($res)){
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "message" => "orders data fetched successfully!",
            "data" => $data
        ]);

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => "No active order found.",
            "data" => []
        ]);

    }
}
else if ($type == "getSingleOrder") {

    $idfr = $_POST['idfr'];

    $query1 = "SELECT * FROM `cart` WHERE `idfr`='$idfr'";
    $query2 = "SELECT * FROM `order` WHERE `idfr`='$idfr'";

    $res1 = mysqli_query($con, $query1);
    $res2 = mysqli_query($con, $query2);

    $cartData = [];
    $data = [];

    // Cart Data
    while ($row = mysqli_fetch_assoc($res1)) {
        $cartData[] = $row;
    }

    // Order Data
    if (mysqli_num_rows($res2) > 0) {
        $data = mysqli_fetch_assoc($res2);
    }

    if (!empty($data) || !empty($cartData)) {

        echo json_encode([
            "status" => "success",
            "message" => "Single order detail fetched successfully!",
            "data" => $data,
            "singleOrder" => $cartData
        ]);

    } else {

        echo json_encode([
            "status" => "failed",
            "message" => "Failed to fetch single order detail!",
            "data" => [],
            "singleOrder" => []
        ]);

    }
}
else if($type == "getSingleCategory"){

    $catId = $_POST['cid'];

    $query1 = "SELECT * FROM `subcategory` WHERE `under_category`='$catId' AND `status`='true'";
    $query2 = "  SELECT 
            p.*,
            (
                SELECT COUNT(*)
                FROM `varient` v
                WHERE v.product_id = p.p_id
            ) AS varient_count
          FROM `product` p
          WHERE p.`status` = 'true'
          AND p.`under_category` = '$catId'";
    

    $res1 = mysqli_query($con, $query1);
    $res2 = mysqli_query($con, $query2);

    $subCategory = [];
    $allProduct = [];

    while ($row1 = mysqli_fetch_assoc($res1)) {
        $subCategory[] = $row1;
    }

    while ($row2 = mysqli_fetch_assoc($res2)) {
        $allProduct[] = $row2;
    }

    if (!empty($subCategory) || !empty($allProduct)) {

        echo json_encode([
            "status" => "success",
            "message" => "Category data fetched successfully!",
            "data" => $allProduct,
            "subCategory" => $subCategory
        ]);

    } else {

        echo json_encode([
            "status" => "failed",
            "message" => "No category data found!",
            "data" => [],
            "subCategory" => []
        ]);

    }
}
else if($type == "handleSearch"){
    $qry = $_POST['query'];

    $query =  "SELECT p.*,
            (
                SELECT COUNT(*)
                FROM `varient` v
                WHERE v.product_id = p.p_id
            ) AS varient_count
          FROM `product` p
          WHERE p.`status` = 'true'
          AND p.`name`LIKE '$qry%'";




    $result = mysqli_query($con,$query);
    if(mysqli_num_rows($result)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($result)) {
             $data[]=$row;
        }
      echo json_encode([
            "status" => "success",
            "message" => "search data fetched successfully",
            "data"=>$data
        ]);
        
    }else{
         echo json_encode([
            "status" => "failed",
            "message" => "something wents wrong on api type : handleSearch ",
            "data"=>[]
        ]);
    }

}
else if($type == "getRelatedPrd"){
    $cid = $_POST['cid'] ?? '';
    $sid = $_POST['sid'] ?? '';

    if (empty($sid)) {

        $query = "SELECT p.*,
                    (
                        SELECT COUNT(*)
                        FROM `varient` v
                        WHERE v.product_id = p.p_id
                    ) AS varient_count
                FROM `product` p
                WHERE p.`status` = 'true'
                AND p.`under_category` = '$cid'
                LIMIT 10";

    } else {

        $query = "SELECT p.*,
                    (
                        SELECT COUNT(*)
                        FROM `varient` v
                        WHERE v.product_id = p.p_id
                    ) AS varient_count
                FROM `product` p
                WHERE p.`status` = 'true'
                AND p.`under_subcategory` = '$sid'
                LIMIT 10";
    }

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        
    while ($row = mysqli_fetch_assoc($res)) {
           $data[]=$row;
    }
    echo json_encode([
        "status"=>"success",
        "message"=>"found related data",
        "data"=>$data
    ]);

    
    }else{
         echo json_encode([
        "status"=>"failed",
        "message"=>"sonthing wents wrong ! on realted Data",
        "data"=>[]
    ]);
    }


}
else if($type == "getAllProductData"){
    $id=$_POST['id'];
    $typename=$_POST['typeName'];
     $query = "SELECT 
            p.*,
            (
                SELECT COUNT(*)
                FROM `varient` v
                WHERE v.product_id = p.p_id
            ) AS varient_count
          FROM `product` p
          WHERE p.`status` = 'true'
          AND p.`under_category` = '$id'
          AND p.`$typename` = 'true'
              ";

    $res = mysqli_query($con, $query);

    $data = [];

    while($row = mysqli_fetch_assoc($res)){

        $data[]=$row;
    }

    echo json_encode([
        "status" => "success",
        "message" => "All productData data  fetched successfully!",
        "data" =>$data
    ]);

    
}
else if($type == "getAllProductBrandData"){
    $id=$_POST['id'];
    $brandId=$_POST['typeName'];
     $query = "SELECT 
            p.*,
            (
                SELECT COUNT(*)
                FROM `varient` v
                WHERE v.product_id = p.p_id
            ) AS varient_count
          FROM `product` p
          WHERE p.`status` = 'true'
          AND p.`under_category` = '$id'
          AND p.`brand_name` = '$brandId'
              ";

    $res = mysqli_query($con, $query);

    $data = [];

    while($row = mysqli_fetch_assoc($res)){

        $data[]=$row;
    }

    echo json_encode([
        "status" => "success",
        "message" => "All brands productData data  fetched successfully!",
        "data" =>$data
    ]);

    
}
else if($type == "getCurrentUser"){
    $id = $_POST['userId'];

    $query ="SELECT * FROM `user` WHERE `user_id`='$id'";

      $result = mysqli_query($con,$query);
    if(mysqli_num_rows($result)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($result)) {
             $data[]=$row;
        }
      echo json_encode([
            "status" => "success",
            "message" => "get current user fetched successfully",
            "data"=>$data
        ]);
        
    }else{
         echo json_encode([
            "status" => "failed",
            "message" => "something wents wrong on api type : getCurrentUser ",
            "data"=>[]
        ]);
    }
}
else if($type == "handleUpdateProfile"){

    $userId = mysqli_real_escape_string($con, $_POST['userId']);
    $name   = mysqli_real_escape_string($con, $_POST['name']);
    $phone  = mysqli_real_escape_string($con, $_POST['phone']);
    $email  = mysqli_real_escape_string($con, $_POST['email']);

    $query = "UPDATE `user`
              SET
                  `full_name` = '$name',
                  `mobile`    = '$phone',
                  `email`     = '$email'
              WHERE `user_id` = '$userId'";

    if (mysqli_query($con, $query)) {

        if (mysqli_affected_rows($con) > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Profile updated successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "warning",
                "message" => "No changes were made."
            ]);
        }

    } else {
        echo json_encode([
            "status" => "error",
            "message" => mysqli_error($con)
        ]);
    }

}
else if($type == "getcategory99store1"){
    $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title1` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getCategory99store2"){
    $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title2` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    //    echo $query; exit();
              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getCategory99store3"){
    $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title3` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getCategory99store4"){
    $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title4` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getCategory99store5"){
    $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title5` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getcategoryBeauty1"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title1` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getcategoryBeauty2"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title2` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getcategoryBeauty3"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title3` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getcategoryBeauty4"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title4` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getcategoryBeauty5"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title5` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "bannerBeauty1"){
          $id = $_POST['categoryId'];

            $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";


              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "bannerBeauty2"){
          $id = $_POST['categoryId'];

            $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";


              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "bannerBeauty3"){
          $id = $_POST['categoryId'];

            $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";


              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getfashionCategory1"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title1` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getfashionCategory2"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title2` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getfashionCategory3"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title3` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getfashionCategory4"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title4` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "getfashionCategory5"){
       $id = $_POST['categoryId'];

     $query = "SELECT * FROM `subcategory`
              WHERE `title5` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "bannerFashion1"){
          $id = $_POST['categoryId'];

            $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";


              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "bannerFashion2"){
          $id = $_POST['categoryId'];

            $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";


              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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
else if($type == "bannerFashion3"){
          $id = $_POST['categoryId'];

            $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";


              $res=mysqli_query($con,$query);

              if(mysqli_num_rows($res)>0){

              $data=[];

              while ($row=mysqli_fetch_assoc($res)) {
                  $data[]=$row;
              }
                echo json_encode([
                    "status"=>"success",
                    "message"=>"data fetched successfully !",
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

else if($type == "getSubcategoryWithProduct") {

    $categoryId = $_POST['categoryId'] ?? '';

    // Step 1: Get first 4 subcategories
    $subQuery = mysqli_query($con,"
        SELECT id, name, image_path
        FROM subcategory
        WHERE under_category='$categoryId'
        AND status='true'
        ORDER BY id
        LIMIT 4
    ");

    if(mysqli_num_rows($subQuery) == 0){
        echo json_encode([
            "status"=>"failed",
            "message"=>"No subcategories found.",
            "data"=>[]
        ]);
        exit;
    }

    $subcategories = [];
    $subIds = [];

    while($sub = mysqli_fetch_assoc($subQuery)){
        $sub['products'] = [];
        $subcategories[$sub['id']] = $sub;
        $subIds[] = $sub['id'];
    }

    // Step 2: Get all products of those subcategories
    $ids = implode(",", $subIds);

    $productQuery = mysqli_query($con,"
        SELECT
            p.p_id,
            p.name,
            p.image_path,
            p.selling_price,
            p.mrp,
            p.under_subcategory
        FROM product p
        WHERE p.status='true'
        AND p.under_subcategory IN ($ids)
        ORDER BY p.under_subcategory, p.p_id
    ");

    // Step 3: Attach only first 4 products to each subcategory
    while($product = mysqli_fetch_assoc($productQuery)){

        $subId = $product['under_subcategory'];

        if(count($subcategories[$subId]['products']) < 4){
            $subcategories[$subId]['products'][] = $product;
        }
    }

    echo json_encode([
        "status"=>"success",
        "message"=>"Data fetched successfully.",
        "data"=>array_values($subcategories)
    ]);
}
else if($type == "getPharmacyCategory1"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title1` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyBanner1"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyBanner2"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyBanner3"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyCategory2"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title2` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyCategory3"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title3` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyCategory4"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title4` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getPharmacyCategory5"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title5` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getCategoryKids1"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title1` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type=="getCategoryKids2"){
     $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title2` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type=="getCategoryKids3"){
     $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title3` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type=="getCategoryKids4"){
     $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title4` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type=="getCategoryKids5"){
     $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title5` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getKidsBanner1"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getKidsBanner2"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getKidsBanner3"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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


else if($type == "get99storeBanner1"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "get99storeBanner2"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "get99storeBanner3"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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



else if($type == "getCategoryElectricity1"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title1` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getElectricityBanner1"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getElectricityBanner2"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getElectricityBanner3"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getCategoryElectricity2"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title2` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getCategoryElectricity3"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title3` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getCategoryElectricity4"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title4` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getCategoryElectricity5"){
    $id = $_POST['categoryId'];
     $query = "SELECT * FROM `subcategory`
              WHERE `title5` = 'true'
              AND `status` = 'true'
              AND `under_category` = '$id'";

    $res = mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getAllbrands"){
    $categoryId = $_POST['categoryId'];
    $query = "SELECT * FROM `brands` WHERE `status`='true' AND `categoryId` = '$categoryId'";
    $res = mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;

        }

            echo json_encode([
                "status"=>"success",
                "message"=>"data fetched successfully !",
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
else if($type == "getBrandProducts"){

   $categoryId = $_POST['categoryId'];
  $query = "SELECT
            b.id AS brand_id,
            b.name AS b_name,
            b.logo_path,
            b.product_path,
            p.*
          FROM brands b
          JOIN (
              SELECT
                  pr.*,
                  (
                      SELECT COUNT(*)
                      FROM varient v
                      WHERE v.product_id = pr.p_id
                  ) AS varient_count,
                  ROW_NUMBER() OVER(PARTITION BY pr.brand_name ORDER BY pr.p_id DESC) AS rn
              FROM product pr
              WHERE pr.status = 'true'
          ) p
          ON p.brand_name = b.id
          WHERE b.status = 'true'
            AND b.categoryId = '$categoryId'
            AND b.promotion = 'true'
            AND p.rn <= 6
          ORDER BY b.id, p.p_id DESC";

     $res = mysqli_query($con, $query);

    $data = [];
    $count = 1;
    $brandMap = [];

    while ($row = mysqli_fetch_assoc($res)) {

        $brandId = $row['brand_id'];

        if (!isset($brandMap[$brandId])) {

            $key = "b" . $count++;

            $brandMap[$brandId] = $key;

            $data[$key] = [
                "id"       => $brandId,
                "name"     => $row['b_name'],
                "img"      => $row['logo_path'],
                "products" => []
            ];
        }

        unset(
            $row['brand_id'],
            $row['brand_name'],
            $row['logo_path'],
            $row['product_path'],
            $row['rn']
        );

        $data[$brandMap[$brandId]]['products'][] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data"   => $data
    ]);
}
else if($type == "getGroceryBanner1"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec1' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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

else if($type == "getGroceryBanner2"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec2' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getGroceryBanner3"){
    $id = $_POST['categoryId'];
    $query = "SELECT * FROM `banner` WHERE `type`='bannerSec3' AND `status`='true' AND `under_category`='$id'";
    $res=mysqli_query($con,$query);
    $data=[];
    if(mysqli_num_rows($res)>0){
        while($row=mysqli_fetch_assoc($res)){
            $data[]=$row;
        }
         echo json_encode([
        "status"=>"success",
        "message"=>"data fetched successfully !",
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
else if($type == "getRecentOrder"){
    $user_id=$_POST['userId'];

  $query = "SELECT
    p.*,
    (
        SELECT COUNT(*)
        FROM varient v
        WHERE v.product_id = p.p_id
    ) AS varient_count
    FROM product p
    WHERE p.status = 'true'
    AND p.p_id IN (
        SELECT c.p_id
        FROM cart c
        WHERE c.status = 'false'
        AND c.idfr IN (
            SELECT o.idfr
            FROM `order` o
            WHERE o.user_id = '$user_id'
        )
    )
    ORDER BY p.p_id DESC";


    $res = mysqli_query($con,$query);
    //   echo $query; exit();
  if(mysqli_num_rows($res)>0){
    $data=[];

    while ($row=mysqli_fetch_assoc($res)) {
          $data[]=$row;
    }
    echo json_encode([
        "status"=>"success",
        "message"=>"recent order successfully !",
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
else if($type=="getBrandOfTheDay"){

    $query1 = "SELECT `min_amount` FROM `other` WHERE `type`='brands_of_the_day'";
    $res = mysqli_query($con, $query1);

    if(mysqli_num_rows($res) > 0){

        $brand = mysqli_fetch_assoc($res);
        $brandId = $brand['min_amount'];

        $query2 = "SELECT * FROM `brands` WHERE `id`='$brandId'";
        $res2 = mysqli_query($con, $query2);

        $data = [];

        if(mysqli_num_rows($res2) > 0){

            while($row = mysqli_fetch_assoc($res2)){
                $data[] = $row;
            }

            echo json_encode([
                "status" => "success",
                "message" => "Brand Of The Day fetched successfully!",
                "data" => $data
            ]);

        }else{

            echo json_encode([
                "status" => "failed",
                "message" => "Brand not found!",
                "data" => []
            ]);
        }

    }else{

        echo json_encode([
            "status" => "failed",
            "message" => "Setting not found!",
            "data" => []
        ]);
    }
}
else if($type=="getSingleBrandOfTheDay"){
    $brandId=$_POST['brandId'];
    $query = "SELECT p.*, ( SELECT COUNT(*) FROM varient v WHERE v.product_id = p.p_id ) 
    AS varient_count FROM product p WHERE p.brand_name = '$brandId'";
    // echo $query; die();
    $res=mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"getSingleBrandOfTheDay fetched successfully !",
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