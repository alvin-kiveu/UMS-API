<?php
include 'config.php';
$ResultCode = '';
$EncodeData = file_get_contents('php://input');
$submitedData = json_decode($EncodeData, true);
$getData = $_POST['requestData'];
//Get top ten storie
$getStories = mysqli_query($connect, "SELECT * FROM software_products WHERE poststatus='posted' ORDER BY ID DESC LIMIT 10");
if (mysqli_num_rows($getStories) > 0) {
    $blogs = array();
    while ($storyData = mysqli_fetch_array($getStories)) {
        $postid = $storyData['postid'];
        $blogImg = 'https://umeskiasoftwares.com/products/' . $storyData['software_image'];
        $blogDescription = strip_tags($storyData['software_desc']);
        $blogTital = $storyData['software_name'];
        $blogPost = $storyData['software_desc'];
        $postedTime = gmdate("M d Y", $storyData['timeposted']);
        $blog = array(
            'postId' => $postid,
            'blogPhoto' => $blogImg,
            'blogTital' => $blogTital,
            'blogDescription' => $blogDescription,
            'postedTime' => $postedTime,
        );
        array_push($blogs, $blog);
    }
    $response = array("ResultCode" => "200", "blogs" => $blogs);
    print(json_encode($response, JSON_UNESCAPED_SLASHES, JSON_PRETTY_PRINT));
} else {
    $ResultCode = "NO STORY AVAILABLE";
    $massage = "there are no stories availbale please trie later";
    $response = array(
        'ResultCode' => $ResultCode,
        'massage' => $massage,
    );
}
if (!$ResultCode == '') {
    echo json_encode($response);
}