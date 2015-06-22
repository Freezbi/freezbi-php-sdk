<?php

require_once './Freezbi/FreezbiApi.php';
use Freezbi\Response\Response;

// Init the Freezbi Api
$freezbiApi = new Freezbi\FreezbiApi();
$freezbiApi->TemporaryFolder = './temp/';

// Create a new Notification with its name, url, and body type
$notification = new \Freezbi\Notification\ManyStreamNotification('youtube');
$notification->Url = 'https://www.googleapis.com/youtube/v3/search?channelId={channel}&part=snippet&order=date&maxResults=1';
$notification->Format = 'json';

// Prepare the api for that ManyStreamNotification
$freezbiApi->prepare($notification);

// Update fetch urls for each configurations
foreach($notification->Configurations as $identifier => $configuration) {
    $customUrl = str_replace('{channel}',$configuration['channel'],$notification->Url);
    $notification->Urls[$identifier] = $customUrl;
}

// Result processing
$notification->Action = function($identifier, $configuration, $jsonContent) use ($freezbiApi) {
    // Prepare a response
    $response = new Response();

    // Extract data from body
    $video = $jsonContent->items[0]->snippet;
    $title = $video->title;
    $channelName = $video->channelTitle;

    // Check if the title is same as the previous call
    if (!empty($title) && !$freezbiApi->testSameAsBefore($title, array('identifier' => $identifier))) { // testSameAsBefore needs the identifier to store data for that configuration
        // Update the response with new data
        $response->SendNotification = true;
        $response->Title = 'Youtube - '.$channelName;
        $response->Message = $title;
        $response->Data = 'http://www.youtube.com/channel/'.$configuration['channel'];
    }

    // Return the response
    return $response;
};


// Your script must return the output of the execute method
echo $freezbiApi->execute();
