<?php
namespace Freezbi\Builder;

use Freezbi\FreezbiApi;
use Freezbi\Notification\SingleStreamNotification;
use Freezbi\Response\ResponseList;

class RssNotificationBuilder
{

    protected $UniqueName;

    protected $FeedUrl;

    protected $Delay = 0;

    protected $NotificationTitle;

    protected $NotificationData;

    protected $TemporaryFolder;

    protected $RssReaderService;

    private $DateLastCheck = null;


    public function __construct($UniqueName)
    {
        $this->UniqueName = $UniqueName;
    }


    public static function create($UniqueName)
    {
        return new RssNotificationBuilder($UniqueName);
    }


    public function render()
    {
        $freezbiApi = new FreezbiApi();
        $freezbiApi->TemporaryFolder = $this->TemporaryFolder;

        $notification = new SingleStreamNotification($this->UniqueName);
        $notification->Url = $this->FeedUrl;
        $notification->Delay = $this->Delay;

        $freezbiApi->prepare($notification);
        $this->setDateLastCheck($freezbiApi->getLastCheck());

        // Save a "this" reference for the closure
        $self = $this;

        // Result processing
        $notification->Action = function ($htmlContent) use ($freezbiApi, $self) {

            // Prepare a response
            $response = new ResponseList();
            $reader = $self->getRssReaderService();
            $newElements = array();
            $feed = $reader->getFeedContent($self->getFeedUrl(), $self->getDateLastCheck());

            foreach ($feed->getItems() as $item) {

                $newElements[] = array(
                    'title' => $item->getTitle(),
                    'data' => $item->getLink()
                );
            }

            if (!empty($newElements)) {
                // Update the response with new data
                $response->SendNotification = true;
                $response->Title = $self->getNotificationTitle();
                $response->Message = $newElements;
            }

            // Return the response
            return $response;
        };

        return $freezbiApi->execute();
    }



    /**
     * @param mixed $UniqueName
     */
    public function setUniqueName($UniqueName)
    {
        $this->UniqueName = $UniqueName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniqueName()
    {
        return $this->UniqueName;
    }

    /**
     * @param mixed $FeedUrl
     */
    public function setFeedUrl($FeedUrl)
    {
        $this->FeedUrl = $FeedUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeedUrl()
    {
        return $this->FeedUrl;
    }

    /**
     * @param mixed $TemporaryFolder
     */
    public function setTemporaryFolder($TemporaryFolder)
    {
        $this->TemporaryFolder = $TemporaryFolder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemporaryFolder()
    {
        return $this->TemporaryFolder;
    }

    /**
     * @param mixed $NotificationTitle
     */
    public function setNotificationTitle($NotificationTitle)
    {
        $this->NotificationTitle = $NotificationTitle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationTitle()
    {
        return $this->NotificationTitle;
    }

    /**
     * @param mixed $NotificationData
     */
    public function setNotificationData($NotificationData)
    {
        $this->NotificationData = $NotificationData;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationData()
    {
        return $this->NotificationData;
    }

    /**
     * @param int $Delay
     */
    public function setDelay($Delay)
    {
        $this->Delay = $Delay;

        return $this;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->Delay;
    }

    /**
     * @param mixed $RssReaderService
     */
    public function setRssReaderService($RssReaderService)
    {
        $this->RssReaderService = $RssReaderService;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRssReaderService()
    {
        return $this->RssReaderService;
    }


    /**
     * @param null $DateLastCheck
     */
    public function setDateLastCheck($DateLastCheck)
    {
        $this->DateLastCheck = $DateLastCheck;

        return $this;
    }

    /**
     * @return null
     */
    public function getDateLastCheck()
    {
        return $this->DateLastCheck;
    }





}
