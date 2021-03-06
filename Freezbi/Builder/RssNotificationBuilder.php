<?php
namespace Freezbi\Builder;

use Debril\RssAtomBundle\Driver\DriverUnreachableResourceException;
use Freezbi\FreezbiApi;
use Freezbi\Notification\ManyStreamNotification;
use Freezbi\Notification\SingleStreamNotification;
use Freezbi\Response\ResponseList;

class RssNotificationBuilder
{

    protected $UniqueName;

    protected $FeedUrl;

    protected $Delay = 0;

    protected $NotificationTitle;

    protected $NotificationTitleFilters;

    protected $NotificationData;

    protected $TemporaryFolder;

    protected $RssReaderService;

    protected $Configurations;

    private $DateLastCheck = null;

    private $Debug = false;

    private $TypeStream = 0;

    public static $FLAG_SINGLE_STREAM = 0;
    public static $FLAG_MANY_STREAM = 1;


    public function __construct($UniqueName)
    {
        $this->UniqueName = $UniqueName;
    }


    public static function create($UniqueName)
    {
        return new RssNotificationBuilder($UniqueName);
    }

    public function render() {
        return $this->getTypeStream() == self::$FLAG_SINGLE_STREAM ? $this->renderSingleStream() : $this->renderManyStream();
    }


    public function renderSingleStream()
    {
        $freezbiApi = new FreezbiApi();
        $freezbiApi->TemporaryFolder = $this->TemporaryFolder;
        $freezbiApi->Delay = $this->Delay;

        $notification = new SingleStreamNotification($this->UniqueName);
        $notification->setUrl($this->FeedUrl);

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

        try {
            return $freezbiApi->execute();
        } catch(\Debril\RssAtomBundle\Driver\DriverUnreachableResourceException $e) {
            return $freezbiApi->renderEmptyResponseList();
        }
    }


    public function renderManyStream()
    {
        $freezbiApi = new FreezbiApi();
        $freezbiApi->TemporaryFolder = $this->TemporaryFolder;
        $freezbiApi->Delay = $this->Delay;

        $notification = new ManyStreamNotification($this->UniqueName, $this->getConfigurations());

        $freezbiApi->prepare($notification);
        $this->setDateLastCheck($freezbiApi->getLastCheck());

        // Save a "this" reference for the closure
        $self = $this;

        // Result processing
        $notification->Action = function ($identifier, $configuration, $htmlContent) use ($freezbiApi, $self) {

            // Prepare a response
            $response = new ResponseList();
            $reader = $self->getRssReaderService();
            $newElements = array();

            $rawUrl = $this->getFeedUrl();
            $mixedUrl = preg_replace_callback('/{(.*?)[\|\|.*?]?}/', function($match) use ($configuration) {
                $match = explode('||',$match[1]);
                return isset($configuration[$match[0]]) ? $configuration[$match[0]] : $configuration[$match[1]] ;
            }, $rawUrl);

            $feed = $reader->getFeedContent($mixedUrl, $self->getDateLastCheck());

            foreach ($feed->getItems() as $item) {
                $newElements[] = array(
                    'title' => $item->getTitle(),
                    'data' => $item->getLink()
                );
            }


            if (!empty($newElements)) {
                // Update the response with new data
                $response->SendNotification = true;
                $response->Title = $self->getInflatedString($self->getNotificationTitle(), $configuration, $self->getNotificationTitleFilters());
                $response->Message = $newElements;
            }

            // Return the response
            return $response;
        };

        try {
            return $freezbiApi->execute();
        } catch(\Debril\RssAtomBundle\Driver\DriverUnreachableResourceException $e) {
            return $freezbiApi->renderEmptyResponseList();
        }
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
     * @param mixed $Configurations
     */
    public function setConfigurations($Configurations)
    {
        $this->Configurations = $Configurations;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfigurations()
    {
        if (empty($this->Configurations) || $this->Configurations == null) {
            return $_POST;
        }

        return $this->Configurations;
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
        if ($this->getDebug()) {
            $dateLastCheckDebug = new \DateTime();
            $dateLastCheckDebug->setDate(2015,01,01);
            return $dateLastCheckDebug;
        }

        return $this->DateLastCheck;
    }

    /**
     * @param boolean $Debug
     */
    public function setDebug($Debug)
    {
        $this->Debug = $Debug;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDebug()
    {
        return $this->Debug;
    }

    /**
     * @param int $TypeStream
     */
    public function setTypeStream($TypeStream)
    {
        $this->TypeStream = $TypeStream;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeStream()
    {
        return $this->TypeStream;
    }


    /**
     * @param mixed $NotificationTitleFilters
     */
    public function setNotificationTitleFilters($NotificationTitleFilters)
    {
        $this->NotificationTitleFilters = $NotificationTitleFilters;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationTitleFilters()
    {
        return $this->NotificationTitleFilters;
    }


    /**
     * @return mixed
     */
    private function getInflatedString($str, $configuration, $filters = array())
    {
        $mixedStr = preg_replace_callback('/{(.*?)[\|\|.*?]?}/', function($match) use ($configuration, $filters) {
            $match = explode('||',$match[1]);
            $replacement = isset($configuration[$match[0]]) ? $configuration[$match[0]] : $configuration[$match[1]] ;

            foreach($filters as $filter) {
                switch($filter) {
                    case "ucfirst":
                        $replacement = ucfirst($replacement);
                    break;
                    case "strtoupper":
                        $replacement = strtoupper($replacement);
                    break;
                }
            }

            return $replacement;
        }, $str);

        return $mixedStr;
    }


}
