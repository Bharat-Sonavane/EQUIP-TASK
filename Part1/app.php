<?php

// Include the Composer autoloader
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Set up the S3 client
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'ap-south-1',  // Change to your S3 region
]);

$bucketName = 'equipassesment';  // Replace with your bucket name

// Get the path from the URL if it exists
$path = isset($_GET['path']) ? $_GET['path'] : '';

// Set the response header to application/json
header('Content-Type: application/json');

try {
    // Define the parameters to list the objects in the bucket
    $params = [
        'Bucket' => $bucketName,
        'Prefix' => $path,
        'Delimiter' => '/',
    ];

    // Get the contents of the bucket
    $result = $s3Client->listObjectsV2($params);
    $content = [];

    // List subdirectories (CommonPrefixes)
    if (isset($result['CommonPrefixes'])) {
        foreach ($result['CommonPrefixes'] as $prefix) {
            $content[] = basename(rtrim($prefix['Prefix'], '/'));
        }
    }

    // List files (Contents)
    if (isset($result['Contents'])) {
        foreach ($result['Contents'] as $object) {
            $content[] = basename($object['Key']);
        }
    }

    // Return the content as JSON
    echo json_encode(['content' => $content]);

} catch (AwsException $e) {
    // If there's an error, return an error message
    echo json_encode(['error' => $e->getMessage()]);
}

?>