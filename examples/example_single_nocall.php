<?php

require_once './Freezbi/FreezbiApi.php';
use Freezbi\Response\Response;

// Init the Freezbi Api
$freezbiApi = new Freezbi\FreezbiApi();
$freezbiApi->TemporaryFolder = 'temp/';
$freezbiApi->Delay = 3600 * 24; // Each remote check will be separated by 24 hours

// Create a new Notification with its name, url, and body type
$notification = new \Freezbi\Notification\SingleStreamNotification('alan_turing_birthday');

// Prepare the api for that SingleStreamNotification
$freezbiApi->prepare($notification);

$notification->Action = function () use ($freezbiApi) {

    // Prepare a response
    $response = new Response();

    $datetime = new \DateTime();

    if ($datetime->format('m-d') == '06-23') {
        $response->SendNotification = true;
        $response->Title = 'Birthday';
        $response->Message = 'This is Alan Turing\'s birthday';
        $response->Data = 'https://fr.wikipedia.org/wiki/Alan_Turing';
    }

    return $response;
};

// Your script must return the output of the execute method
echo $freezbiApi->execute();
