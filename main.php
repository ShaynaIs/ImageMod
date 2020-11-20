<?php 

// This function takes the src file, destination file, and required quality and creates the new image
function squarePicture($src_file,$destination_file,$jpeg_quality=90)
{
    // Check image type
    if (exif_imagetype($src_file) == IMAGETYPE_JPEG){$jpg = true;}
    else if (exif_imagetype($src_file) == IMAGETYPE_PNG){$jpg = false;}
    else{throw new Exception("SI: File not suppported.");}
    
    // Create img from file, depending on type
    $src_img = $jpg? imagecreatefromjpeg($src_file) : imagecreatefrompng($src_file);

    // Get width and length
    $x=imageSX($src_img);
    $y=imageSY($src_img);
    $square_dimensions = $x > $y? $x: $y;
    
    // "Copy and Paste" the $smaller_image_with_proportions in the center of a white image of the desired square dimensions

    // Create image of $square_dimensions x $square_dimensions in white color (white background)
    $final_image = imagecreatetruecolor($square_dimensions, $square_dimensions);
    $bg = imagecolorallocate ( $final_image, 255, 255, 255 );
    imagefilledrectangle($final_image,0,0,$square_dimensions,$square_dimensions,$bg);

    // need to center the small image in the squared new white image
    if($x>$y)
    {
        // more width than height we have to center height
        $dst_x=0;
        $dst_y=($square_dimensions-$y)/2;
    }
    elseif($y>$x)
    {
        // more height than width we have to center width
        $dst_x=($square_dimensions-$x)/2;
        $dst_y=0;

    }
    else
    {
        $dst_x=0;
        $dst_y=0;
    }

    $src_x=0; // we copy the src image complete
    $src_y=0; // we copy the src image complete

    $src_w=$x; // we copy the src image complete
    $src_h=$y; // we copy the src image complete

    $pct=100; // 100% over the white color ... here you can use transparency. 100 is no transparency.

    imagecopymerge($final_image,$src_img,$dst_x,$dst_y,$src_x,$src_y,$src_w,$src_h,$pct);
    
    // Check if right size
    if ($square_dimensions < 500){
        $final_image = imagescale( $final_image, 500 );
    }

    imagejpeg($final_image,$destination_file,$jpeg_quality);


    // destroy aux images (free memory)
    imagedestroy($src_img); 
    imagedestroy($final_image);
}

// Get file location from url
$q = $_SERVER['QUERY_STRING'];
parse_str($q, $query);
$location = $query['location'];
$client = $query['client'] .'1';
if(strpos("$client", "walmart") !== false){
    $location = str_replace(".jpg","",$location);
}
$fullLocation = $query['baseurl'] . $location;
$srcFile = 'https://' . $fullLocation;

// Set destination a name
if(!file_exists($client)){
    mkdir($client);
}

$fileName = str_replace('/','-',$fullLocation);
$destinationFile = $client . '/' . $fileName;

// Checks time
$lastModifiedTimestamp = file_exists($destinationFile)? filemtime($destinationFile) : time();
$yesterday = time() -86400;

// If the img has been editted in the past 24 hrs, return old copy
if(!file_exists($destinationFile) || $lastModifiedTimestamp < $yesterday){
    // Try to make it square    
    try{
        squarePicture($srcFile,$destinationFile);
        $getFile = $destinationFile;
        $lastModifiedTimestamp = time();
    }
    // If error, return original image
    catch(Exception $e){
        error_log($e->getMessage());
        $getFile = $srcFile;
    }
}
else{
    $getFile = $destinationFile;
}



// Return image
$image = file_get_contents($destinationFile);

$getFile = '/resizer/'. $destinationFile;
echo "<img src=$getFile>";
?> 
