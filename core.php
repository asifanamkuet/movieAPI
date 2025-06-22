<?php

// Set headers to allow cross-origin requests (CORS)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Get the search parameter from the query string
if (isset($_GET['search'])) {
    $searchValue = $_GET['search'];
} else {
    echo json_encode(["error" => "Search parameter is missing."]);
    exit;
}

// URL for the POST request
$url = 'https://fibwatch.art/aj/search';

// Data to be sent in the POST request
$data = [
    'hash' => '2f1dbc42f02c71cf2a7495138fd5abc55b333687',
    'search_value' => $searchValue
];

// Headers for the request
$headers = [
    'Host: fibwatch.art',
    'Cookie: PHPSESSID=n6rm6po5i119binjjg5er4bgki;',
    'Origin: https://fibwatch.art',
    'Referer: https://fibwatch.art',
    'Accept-Encoding: gzip, deflate, br'
];

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Encode the data as query string
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the request and get the response
$response = curl_exec($ch);

// Check if the cURL request was successful
if (curl_errno($ch)) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
    exit;
}

// Close the cURL session
curl_close($ch);

// Check if response is gzipped and decode accordingly
if (strpos($response, "\x1f\x8b\x08") === 0) {
    // If gzip is detected, decode the response
    $response = gzdecode($response);
}

// Attempt to decode JSON
$json = json_decode($response, true);

// Check if the response is valid JSON
if ($json === null) {
    echo json_encode(["error" => "JSON Decode Error: " . json_last_error_msg()]);
    exit;
}

if (isset($json['html'])) {
    $html = $json['html'];

    // Load the HTML content into DOMDocument
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    // Get all the <a> tags
    $links = $doc->getElementsByTagName('a');
    $result = [];

    // Loop through the links and collect the href and text content
    foreach ($links as $link) {
        $href = $link->getAttribute('href'); // Get the href attribute
        $text = $link->textContent; // Get the link text
        $result[] = ["text" => $text, "url" => $href];

        // Initialize cURL session to get the content of the linked page
        $ch = curl_init($href);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Execute the request and get the response
        $response = curl_exec($ch);
        if ($response === false) {
            echo json_encode(['error' => "cURL error: " . curl_error($ch)]);
            exit;
        }

        // Load the linked page HTML content
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($response);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $meta_nodes = $xpath->query("//meta[@name='thumbnail']");
        $thumbnail_url = null;

        if ($meta_nodes->length > 0) {
            $thumbnail_url = $meta_nodes->item(0)->getAttribute('content');
        }

        // Get the video source URL
        $source_nodes = $xpath->query("//source");
        $video_url = null;

        if ($source_nodes->length > 0) {
            $video_url = $source_nodes->item(0)->getAttribute('src');
        }

        // Add the data to the result
        $result[count($result) - 1]['thumbnail_url'] = $thumbnail_url;
        $result[count($result) - 1]['video_url'] = $video_url;
    }

    // Return the result as JSON
    echo json_encode(['data' => $result]);

} else {
    echo json_encode(["error" => "Not found"]);
}
?>
