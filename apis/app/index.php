<?php
date_default_timezone_set("Asia/Kolkata");
header("Access-Control-Allow-Origin:*"); 

 $con=mysqli_connect("localhost","root","","ITS_GROCERY_BRANCH");
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
        // $query = "SELECT * FROM `banner` WHERE `type`='main' AND `status`='true' AND `under_category`='$id'";
        $query="SELECT * FROM `main_banner` WHERE `category_Id`='$id'";
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
        $categoryId = $_POST['categoryId'];
         $branchId = $_POST['branchId'];
      // 9,8,7,20
        
        // $query = "SELECT * FROM `product` WHERE `flash_sale`='true' AND `status`='true' AND `under_category`='$id'";


         $query1 = "SELECT 
            p.*,

            (
                SELECT COUNT(*)
                FROM varient vc
                WHERE vc.product_id = p.p_id
            ) AS varient_count,

            v.vid,
            v.product_id,
            v.v_unit,
            v.v_mrp,
            v.v_seliing_price,
            v.v_purchase_price,
            v.v_quantity,

            IFNULL(bs.stock, 0) AS stock

        FROM product p

        LEFT JOIN varient v 
            ON v.vid = (
                SELECT v2.vid
                FROM varient v2
                WHERE v2.product_id = p.p_id
                ORDER BY v2.vid ASC
                LIMIT 1
            )

        LEFT JOIN branch_stock bs
            ON bs.product_id = p.p_id
            AND bs.varient_id = v.vid
            AND bs.branch_id = '$branchId'

        WHERE p.status = 'true'
        AND p.under_category = '$categoryId'
        AND p.flash_sale = 'true'
        AND IFNULL(bs.stock, 0) > 0";

         $query2 = "SELECT * FROM `banner`
           WHERE `type`='flashSale'
           AND `status`='true'
           AND `under_category`='$categoryId'";

$res1 = mysqli_query($con, $query1);
$res2 = mysqli_query($con, $query2);

if ($res1 && $res2 && mysqli_num_rows($res1) > 0 && mysqli_num_rows($res2) > 0) {

    $data = [];

    // Main banners
    while ($row = mysqli_fetch_assoc($res1)) {
        $data[] = $row;
    }

    // Flash sale background banner - only first record
    $bgData = mysqli_fetch_assoc($res2);

    echo json_encode([
        "status"  => "success",
        "message" => "Banner fetched successfully!",
        "data"    => $data,
        "bg"      => $bgData
    ]);

} else {

    echo json_encode([
        "status"  => "failed",
        "message" => "Something went wrong on API: getTopLeftBanner",
        "data"    => [],
        "bg"      => []
    ]);
}
    }
    else if($type == "getTopRightBanner"){
        $id=$_POST['categoryId'];
        $query = "SELECT * FROM `hero_banner` WHERE `under_category`='$id'";
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

        $query = "SELECT * FROM `middle_category`
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
              FROM `middle_category`
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
    $branchId = $_POST['branchId'];

     $query1 = "SELECT  p.*,
     (
        SELECT COUNT(*)
        FROM varient vc
        WHERE vc.product_id = p.p_id
    ) AS varient_count,

    v.vid,
    v.product_id,
    v.v_unit,
    v.v_quantity,
    bs.v_mrp,
    bs.v_seliing_price,
    bs.v_purchase_price,

    IFNULL(bs.stock, 0) AS stock

FROM product p

LEFT JOIN varient v 
    ON v.vid = (
        SELECT v2.vid
        FROM varient v2
        WHERE v2.product_id = p.p_id
        ORDER BY v2.vid ASC
        LIMIT 1
    )

LEFT JOIN branch_stock bs
    ON bs.product_id = p.p_id
    AND bs.varient_id = v.vid
    AND bs.branch_id = '$branchId'

WHERE p.status = 'true'
AND p.under_category = '$categoryId'
AND (
    p.title1 = 'true'
    OR p.title2 = 'true'
    OR p.title3 = 'true'
    OR p.title4 = 'true'
    OR p.title5 = 'true'
    OR p.title6 = 'true'
)";
// echo $query1; exit();

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
                    WHERE `title_type`='best_selling'";
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
                    WHERE `title_type`='new_finds' AND `category_Id`='$categoryId'";
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
    $categoryId=$_POST['categoryId'];

    $query = "SELECT * FROM `header_title`
        WHERE `title_type` IN ('top_subcategory', 'top_product','new_finds') AND `category_Id`='$categoryId'";
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
    $branch_id     = $_POST['branch_id'] ?? '';
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
                    AND `branch_id`='$branch_id'
                    AND `status` = 'true'";

    } else {

        $checkQuery = "SELECT * FROM `cart`
                    WHERE `user_id`='$user_id'
                    AND `p_id`='$p_id'
                    AND `branch_id`='$branch_id'
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
                    AND `branch_id`='$branch_id'
                     AND `status` = 'true'";

   } else {

    $updateQuery = "UPDATE `cart`
                    SET `nop`='$nop'
                    WHERE `user_id`='$user_id'
                    AND `p_id`='$p_id'
                    AND `branch_id`='$branch_id'
                     AND `status` = 'true'";
  }

        //   echo $updateQuery; exit();

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
            `branch_id`,
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
            '$branch_id',
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
        // echo $insertQuery; exit();

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
        $branch_id     = $_POST['branch_id'] ?? '';


    if($nop <= 0){
        $updateQuery = "DELETE FROM cart 
                        WHERE `user_id` = '$user_id'
                         AND `p_id` = '$p_id'
                         AND `vid` = '$vid'
                         AND `branch_id` = '$branch_id'";
    //     if (!empty($vid)) {
    //     $updateQuery = "UPDATE `cart`
    //                     SET `status` = 'false'
    //                     WHERE `user_id` = '$user_id'
    //                     AND `p_id` = '$p_id'
    //                     AND `vid` = '$vid'
    //                     AND `branch_id` = '$branch_id'
    //                     AND `status` = 'true'";
    // } else {
    //     $updateQuery = "UPDATE `cart`
    //                     SET `status` = 'false'
    //                     WHERE `user_id` = '$user_id'
    //                     AND `p_id` = '$p_id'
    //                     AND `branch_id` = '$branch_id'
    //                     AND `status` = 'true'";
    // }

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
                            AND `branch_id`='$branch_id'
                             AND `status` = 'true'";
        }else{
            $updateQuery = "UPDATE `cart`
                            SET `nop`='$nop'
                            WHERE `user_id`='$user_id'
                            AND `p_id`='$p_id'
                            AND `branch_id`='$branch_id'
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
        $branch_id = $_POST['branchId'] ?? '';
    $id=$_POST['id'];
    // $query = "SELECT * FROM `varient` WHERE `product_id`='$id'";
    $query = "SELECT 
             v.vid,
            v.product_id,
            v.v_unit,
            v.v_quantity,
            bs.v_mrp,
            bs.v_seliing_price,
            bs.v_purchase_price,
            bs.stock
          FROM `varient` v
          LEFT JOIN `branch_stock` bs
            ON bs.varient_id = v.vid
            AND bs.branch_id = '$branch_id'
             WHERE v.product_id = '$id'";
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
    $branchId = $_POST['branchId'];

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
       $variantQuery = "SELECT 
                    v.vid,
                    v.product_id,
                    v.v_unit,
                    v.v_quantity,
                    bs.v_mrp,
                    bs.v_seliing_price,
                    bs.v_purchase_price,
                    COALESCE(bs.stock, 0) AS stock
                FROM varient v
                LEFT JOIN branch_stock bs
                    ON bs.product_id = v.product_id
                    AND bs.varient_id = v.vid
                    AND bs.branch_id = '$branchId'
                WHERE v.product_id = '$id'";

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
    $branchId = $_POST['branchId'];
    $query="SELECT * FROM `cart` WHERE `user_id` ='$userId' AND `branch_id`='$branchId' AND `status`='true'";


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
    $branch_id = $_POST['branchId'] ?? '';
    $query = "SELECT 
            v.vid,
            v.product_id,
            v.v_unit,
            v.v_quantity,
            bs.v_mrp,
            bs.v_seliing_price,
            bs.v_purchase_price,
            bs.stock
          FROM `varient` v
          LEFT JOIN `branch_stock` bs
            ON bs.varient_id = v.vid
            AND bs.branch_id = '$branch_id'";
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
    $branch_id       = $_POST['branchId'] ?? '';
    $user_id         = $_POST['user_id'] ?? '';
    $payment_method  = $_POST['payMethod'] ?? '';
    $address_id      = $_POST['selectAddress'] ?? '';
    $order_type      = $_POST['orderType'] ?? '';
    $selected_slot   = $_POST['selectedSlot'] ?? '';
    $coupon_id       = $_POST['couponId'] ?? '';
    $total           = $_POST['totalAmount'] ?? 0;
    $handling_charge = $_POST['handlingCharge'] ?? 0;
    $coupon_amount   = $_POST['couponAmt'] ?? 0;
    $delivery_charge = $_POST['deliveryCharge'] ?? 0;
    $subTotal = $_POST['subTotal'] ?? 0;

    $status    = "pending";
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
        `branch_id`,
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
        `dor`,
        `sub_total`
    )
    VALUES
    (
        '$user_id',
        '$idfr',
        '$branch_id',
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
        '$dor',
        '$subTotal'
    )";
    // echo $query; exit();

    /*
     * Reduce branch stock
     *
     * cart.idfr identifies all products of this order.
     * product_id + vid identifies the exact branch_stock row.
     */
    $query2 = "UPDATE `branch_stock` bs
    INNER JOIN `cart` c
        ON bs.product_id = c.p_id
        AND bs.varient_id = c.vid
    SET bs.stock = bs.stock - c.nop
    WHERE bs.branch_id = '$branch_id'
    AND c.idfr = '$idfr'";
    // echo $query2; exit();

    // Mark cart items as ordered
    $query3 = "UPDATE `cart`
    SET `status` = 'false'
    WHERE `idfr` = '$idfr'";

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
   $query = "SELECT * FROM `order` WHERE `user_id`='$userId' ORDER BY id DESC";
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
else if($type == "getBranch"){
    $branchId =$_POST['branchId'];
    $query="SELECT * FROM `branch` WHERE `id`='$branchId'";
    $res = mysqli_query($con,$query);

    $data=[];
    if(mysqli_num_rows($res)>0){
        while ($row=mysqli_fetch_assoc($res)) {
             $data[]=$row;
        }
         echo json_encode([
            "status" => "success",
            "message" => "get branch data!",
            "data"=>$data
        ]);

    }else{
         echo json_encode([
            "status" => "failed",
            "message" => "something wents wrong !",
            "data"=>[]
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
    $addressData = [];

    // Cart Data
    while ($row = mysqli_fetch_assoc($res1)) {
        $cartData[] = $row;
    }

    // Order Data
    if (mysqli_num_rows($res2) > 0) {
        $data = mysqli_fetch_assoc($res2);

        $addressId = $data['address_id'];

        $queryAddress = "SELECT * FROM `location` WHERE `id`='$addressId'";
        $resAddress = mysqli_query($con, $queryAddress);

        if (mysqli_num_rows($resAddress) > 0) {
            $addressData = mysqli_fetch_assoc($resAddress);
        }

    }

    if (!empty($data) || !empty($cartData) || !empty($addressData)) {

        echo json_encode([
            "status" => "success",
            "message" => "Single order detail fetched successfully!",
            "data" => $data,
            "singleOrder" => $cartData,
            "address"=>$addressData
        ]);

    } else {

        echo json_encode([
            "status" => "failed",
            "message" => "Failed to fetch single order detail!",
            "data" => [],
            "singleOrder" => [],
            "address"=>[]

        ]);

    }
}
else if($type == "getSingleCategory"){

    $catId = $_POST['cid'];
    $middleCatId = $_POST['mid'] ?? '';
    $branch_id = $_POST['branchId'] ?? '';
    $query1 = "SELECT * FROM `subcategory` WHERE `middle_category`='$middleCatId' AND `status`='true'";
    // echo $query1; exit();
   $query2 = "SELECT
            p.*,

            (
                SELECT COUNT(*)
                FROM `varient` v1
                WHERE v1.product_id = p.p_id
            ) AS varient_count,

            v.vid,
            v.product_id AS variant_product_id,
            v.v_unit,
            v.v_quantity,

            bs.v_mrp,
            bs.v_seliing_price,
            bs.v_purchase_price,
            bs.stock

          FROM `product` p

          LEFT JOIN `varient` v
            ON v.vid = (
                SELECT v1.vid
                FROM `varient` v1
                WHERE v1.product_id = p.p_id
                ORDER BY v1.vid ASC
                LIMIT 1
            )

          LEFT JOIN `branch_stock` bs
            ON bs.product_id = p.p_id
            AND bs.varient_id = v.vid
            AND bs.branch_id = '$branch_id'

          WHERE p.`status` = 'true'
          AND p.`under_category` = '$catId'
          AND p.`under_middle_category`='$middleCatId'";
        // echo $query2; exit();

     $query3 = "SELECT * FROM `middle_category` WHERE `id`='$middleCatId'";


    $res1 = mysqli_query($con, $query1);
    $res2 = mysqli_query($con, $query2);
    $res3 = mysqli_query($con, $query3);

    $subCategory = [];
    $allProduct = [];
    $middleCatData=[];

    while ($row1 = mysqli_fetch_assoc($res1)) {
        $subCategory[] = $row1;
    }

    while ($row2 = mysqli_fetch_assoc($res2)) {
        $allProduct[] = $row2;
    }
    while ($row3 = mysqli_fetch_assoc($res3)) {
        $middleCatData[] = $row3;
    }
    if (!empty($subCategory) || !empty($allProduct) || !empty($middleCatData)) {

        echo json_encode([
            "status" => "success",
            "message" => "Category data fetched successfully!",
            "data" => $allProduct,
            "subCategory" => $subCategory,
            "middleCategoryName"=>$middleCatData
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
    $branchId = $_POST['branchId'];

    $query = "SELECT 
        p.*,

        (
            SELECT COUNT(*)
            FROM `varient` v1
            WHERE v1.product_id = p.p_id
        ) AS varient_count,

        v.vid,
        v.product_id AS variant_product_id,
        v.v_unit,
        v.v_quantity,

        bs.v_mrp,
        bs.v_seliing_price,
        bs.v_purchase_price,

        COALESCE(bs.stock, 0) AS stock

    FROM `product` p

    LEFT JOIN `varient` v
        ON v.product_id = p.p_id

    LEFT JOIN `branch_stock` bs
        ON bs.product_id = p.p_id
        AND bs.varient_id = v.vid
        AND bs.branch_id = '$branchId'

    WHERE p.`status` = 'true'
    AND p.`name` LIKE '$qry%'";




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
    $branchId = $_POST['branchId'] ?? '';
if (empty($sid)) {

    $query = "SELECT 
                p.*,

                (
                    SELECT COUNT(*)
                    FROM `varient` v1
                    WHERE v1.product_id = p.p_id
                ) AS varient_count,

                v.vid,
                v.product_id AS variant_product_id,
                v.v_unit,
                v.v_quantity,

                bs.v_mrp,
                bs.v_seliing_price,
                bs.v_purchase_price,
                COALESCE(bs.stock, 0) AS stock

            FROM `product` p

            LEFT JOIN `varient` v
                ON v.product_id = p.p_id

            LEFT JOIN `branch_stock` bs
                ON bs.product_id = v.product_id
                AND bs.varient_id = v.vid
                AND bs.branch_id = '$branchId'

            WHERE p.`status` = 'true'
            AND p.`under_category` = '$cid'

            LIMIT 10";

} else {

    $query = "SELECT 
                p.*,

                (
                    SELECT COUNT(*)
                    FROM `varient` v1
                    WHERE v1.product_id = p.p_id
                ) AS varient_count,

                v.vid,
                v.product_id AS variant_product_id,
                v.v_unit,
                v.v_quantity,

                bs.v_mrp,
                bs.v_seliing_price,
                bs.v_purchase_price,
                COALESCE(bs.stock, 0) AS stock

            FROM `product` p

            LEFT JOIN `varient` v
                ON v.product_id = p.p_id

            LEFT JOIN `branch_stock` bs
                ON bs.product_id = v.product_id
                AND bs.varient_id = v.vid
                AND bs.branch_id = '$branchId'

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
        $branch_id = $_POST['branchId'] ?? '';

       $query = "SELECT p.*,

    (
        SELECT COUNT(*)
        FROM `varient` v1
        WHERE v1.product_id = p.p_id
    ) AS varient_count,

    v.vid,
    v.product_id AS variant_product_id,
    v.v_unit,
    v.v_quantity,

    bs.v_mrp,
    bs.v_seliing_price,
    bs.v_purchase_price,
    COALESCE(bs.stock, 0) AS stock

    FROM `product` p

    LEFT JOIN `varient` v
        ON v.product_id = p.p_id

    LEFT JOIN `branch_stock` bs
        ON bs.product_id = v.product_id
        AND bs.varient_id = v.vid
        AND bs.branch_id = '$branch_id'

    WHERE p.`status` = 'true'
    AND p.`under_category` = '$id'
    AND p.`$typename` = 'true'";

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
    //  $query = "SELECT 
    //         p.*,
    //         (
    //             SELECT COUNT(*)
    //             FROM `varient` v
    //             WHERE v.product_id = p.p_id
    //         ) AS varient_count
    //       FROM `product` p
    //       WHERE p.`status` = 'true'
    //       AND p.`under_category` = '$id'
    //       AND p.`brand_name` = '$brandId'
    //           ";

    $branch_id = $_POST['branchId'] ?? '';

    $query = "SELECT
            p.*,
            (
                SELECT COUNT(*)
                FROM `varient` v1
                WHERE v1.product_id = p.p_id
            ) AS varient_count,
            v.vid,
            v.product_id AS variant_product_id,
            v.v_unit,
            v.v_quantity,
            bs.v_mrp,
            bs.v_seliing_price,
            bs.v_purchase_price,
            bs.stock
          FROM `product` p
          LEFT JOIN `varient` v
            ON v.vid = (
                SELECT v1.vid
                FROM `varient` v1
                WHERE v1.product_id = p.p_id
                ORDER BY v1.vid ASC
                LIMIT 1
            )
          LEFT JOIN `branch_stock` bs
            ON bs.product_id = p.p_id
            AND bs.varient_id = v.vid
            AND bs.branch_id = '$branch_id'
          WHERE p.`status` = 'true'
          AND p.`under_category` = '$id'
          AND p.`brand_name` = '$brandId'";

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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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

     $query = "SELECT * FROM `middle_category`
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
    // $subQuery = mysqli_query($con,"SELECT id, name
    //     FROM middle_category
    //     WHERE under_category='$categoryId'
    //     AND status='true'
    //     ORDER BY id
    //     LIMIT 4
    // ");
    $query ="SELECT id, name
        FROM middle_category
        WHERE under_category='$categoryId'
        AND status='true'
        ORDER BY id
        LIMIT 4
    ";
    $subQuery = mysqli_query($con,$query);
    
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
                
                $productQuery = mysqli_query($con,"SELECT
            p.p_id,
            p.name,
            p.image_path,
            p.under_middle_category
        FROM product p
        WHERE p.status='true'
        AND p.under_middle_category IN ($ids)
        ORDER BY p.under_middle_category, p.p_id
    ");
  
// echo $productQuery; exit();

    // Step 3: Attach only first 4 products to each subcategory
    while($product = mysqli_fetch_assoc($productQuery)){

        $subId = $product['under_middle_category'];

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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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
     $query = "SELECT * FROM `middle_category`
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

   $branch_id = $_POST['branch_id'];
   $categoryId = $_POST['categoryId'];
    $query = "SELECT
        b.id AS brand_id,
        b.name AS b_name,
        b.logo_path,
        b.product_path,

        p.*,

        v.vid,
        v.product_id AS variant_product_id,
        v.v_unit,
        v.v_quantity,

        bs.v_mrp,
        bs.v_seliing_price,
        bs.v_purchase_price,
        COALESCE(bs.stock, 0) AS stock

    FROM brands b

    JOIN (
        SELECT
            pr.*,

            (
                SELECT COUNT(*)
                FROM varient v1
                WHERE v1.product_id = pr.p_id
            ) AS varient_count,

            ROW_NUMBER() OVER (
                PARTITION BY pr.brand_name
                ORDER BY pr.p_id DESC
            ) AS rn

        FROM product pr

        WHERE pr.status = 'true'
    ) p
        ON p.brand_name = b.id

    LEFT JOIN varient v
        ON v.product_id = p.p_id

    LEFT JOIN branch_stock bs
        ON bs.product_id = v.product_id
        AND bs.varient_id = v.vid
        AND bs.branch_id = '$branch_id'

    WHERE b.status = 'true'
    AND b.categoryId = '$categoryId'
    AND b.promotion = 'true'
    AND p.rn <= 6

    ORDER BY b.id, p.p_id DESC, v.vid ASC";
        //   echo $query; exit();

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
    $branchId=$_POST['branchId'];

 $query = "SELECT
    p.*,

    (
        SELECT COUNT(*)
        FROM varient vc
        WHERE vc.product_id = p.p_id
    ) AS varient_count,

       v.vid,
            v.product_id,
            v.v_unit,
            v.v_quantity,
            bs.v_mrp,
            bs.v_seliing_price,
            bs.v_purchase_price,

    IFNULL(bs.stock, 0) AS stock

FROM product p

LEFT JOIN varient v
    ON v.vid = (
        SELECT v2.vid
        FROM varient v2
        WHERE v2.product_id = p.p_id
        ORDER BY v2.vid ASC
        LIMIT 1
    )

LEFT JOIN branch_stock bs
    ON bs.product_id = p.p_id
    AND bs.varient_id = v.vid
    AND bs.branch_id = '$branchId'

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


  $res=mysqli_query($con,$query);
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
    $branch_id=$_POST['branchId'];
    // $query = "SELECT p.*, ( SELECT COUNT(*) FROM varient v WHERE v.product_id = p.p_id ) 
    // AS varient_count FROM product p WHERE p.brand_name = '$brandId'";
    $query = "SELECT
            p.*,

            (
                SELECT COUNT(*)
                FROM `varient` v1
                WHERE v1.product_id = p.p_id
            ) AS varient_count,

            v.vid,
            v.product_id AS variant_product_id,
            v.v_unit,
            v.v_quantity,

            bs.v_mrp,
            bs.v_seliing_price,
            bs.v_purchase_price,
            bs.stock

          FROM `product` p

          LEFT JOIN `varient` v
            ON v.vid = (
                SELECT v1.vid
                FROM `varient` v1
                WHERE v1.product_id = p.p_id
                ORDER BY v1.vid ASC
                LIMIT 1
            )

          LEFT JOIN `branch_stock` bs
            ON bs.product_id = p.p_id
            AND bs.varient_id = v.vid
            AND bs.branch_id = '$branch_id'

          WHERE p.brand_name = '$brandId'";
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
else if($type=="findNearestBranch"){
    $userLat = $_POST['lat'] ?? null;
    $userLng = $_POST['lng'] ?? null;

    if ($userLat === null || $userLng === null) {

        echo json_encode([
            "status" => false,
            "message" => "Latitude and longitude required"
        ]);

        exit;
    }

        $userLat = (float) $userLat;
        $userLng = (float) $userLng;
        $query = "SELECT
    id,
    name,
    address,
    city,
    state,
    pincode,
    latitude,
    longitude,
    coverage,

    (
        6371 * ACOS(
            COS(RADIANS($userLat))
            * COS(RADIANS(latitude))
            * COS(RADIANS(longitude) - RADIANS($userLng))
            + SIN(RADIANS($userLat))
            * SIN(RADIANS(latitude))
        )
    ) AS distance

FROM branch

WHERE status = 'true'
AND isOpen = 'true'
AND latitude IS NOT NULL
AND longitude IS NOT NULL
AND coverage IS NOT NULL

HAVING distance <= coverage

ORDER BY distance ASC

LIMIT 1";

// $res = mysqli_query($con, $query);

// if (!$res) {
//     die("SQL ERROR: " . mysqli_error($con));
// }

// while ($row = mysqli_fetch_assoc($res)) {
//     echo "<pre>";
//     print_r($row);
//     echo "</pre>";
// }

// echo "<pre>";
// echo "USER LAT: " . $userLat . "\n";
// echo "USER LNG: " . $userLng . "\n";
// echo $query;
// echo "</pre>";
// exit();

        $result = mysqli_query($con, $query);


        if (!$result) {

            echo json_encode([
                "status" => false,
                "message" => mysqli_error($con)
            ]);

            exit;
        }


        $branch = mysqli_fetch_assoc($result);


        if (!$branch) {

            echo json_encode([
                "status" => false,
                "message" => "No branch found"
            ]);

            exit;
        }


        echo json_encode([
            "status" => "success",
            "branch" => $branch
        ]);

}

else if($type=="getCurrentDeliveryBranch"){
    $branchId = $_POST['branchId'];
    $totalSellingPrice = $_POST['totalSellingPrice'];
    // $query = "SELECT *
    //       FROM `delivery_charge`
    //       WHERE `branch_Id` = '$branchId'
    //       AND `min_amount` >= '$totalSellingPrice'
    //       ORDER BY `min_amount` DESC
    //       LIMIT 1";
          $query = "SELECT *
            FROM delivery_charge
            WHERE branch_Id = '$branchId'
            AND CAST(min_amount AS DECIMAL(10,2)) <= '$totalSellingPrice'
            ORDER BY CAST(min_amount AS DECIMAL(10,2)) DESC
            LIMIT 1";
        //   echo $query; exit();

    $res = mysqli_query($con,$query);
    if(mysqli_num_rows($res)>0){
        $data=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $data[]=$row;
        }
        echo json_encode([
            "status"=>"success",
            "data"=>$data,
            "message"=>"current branch delivery charge fetched successfully !"
        ]);
    }else{
         echo json_encode([
            "status"=>"failed",
            "data"=>[],
            "message"=>"something wents wrong !"
        ]);
    }
}

  ?>