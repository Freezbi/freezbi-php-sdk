<?php

require_once './Freezbi/FreezbiApi.php';
use Freezbi\Response\Response;

// Init the Freezbi Api
$freezbiApi = new Freezbi\FreezbiApi();
$freezbiApi->TemporaryFolder = 'temp/';
$freezbiApi->Delay = 300; // Each remote check will be separated by 300 seconds

// Create a new Notification with its name, url, and body type
$notification = new \Freezbi\Notification\SingleStreamNotification('freezbiblog', 'http://freezbi.com/blog/posts', 'html');

// Prepare the api for that SingleStreamNotification
$freezbiApi->prepare($notification);

$notification->Action = function($content) use ($freezbiApi) {
    // Prepare a response
    $response = new Response();

    // Use phpQuery lib for dirty crawling
    $freezbiApi->initPhpQueryOn($content);
    $article = pq('.blog article')->eq(0);

    // Extract a title and a link
    $data = $article->find('a')->attr('href');
    $title = trim($article->find('h1')->text());
    $title = trim(preg_replace("#(.+)\n(.+)#","$1",$title));

    // Check if the title is same as the previous call
    if (!empty($title) && !$freezbiApi->testSameAsBefore($title, array('keep_history' => true))) { // keep_history option store data history instead of "switch A<=>B" verification
        // Update the response with new data
        $response->SendNotification = true;
        $response->Title = 'Clubic';
        $response->Message = utf8_encode($title);
        $response->Data = 'freezbi.com/'.$data;
    }

    // Return the response
    return $response;
};

// Your script must return the output of the execute method
echo $freezbiApi->execute();
