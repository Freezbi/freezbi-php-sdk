<?php
namespace Freezbi\Notification;

use \Freezbi\Http\HttpLoader;

class ManyStreamNotification extends Notification
{
    public $Configurations;

    public $Urls;


    public function __construct($name = null, $url = null, $format = 'html')
    {
        $this->Name = $name;
        $this->Url = $url;
        $this->Format = strtolower($format);
        $this->Urls = array();

        foreach ($_POST as $pid => $configuration) {
            $this->Configurations[$pid] = (array) json_decode($configuration);
        }
    }

    public function execute($pid)
    {
        $url = null;

        if (isset($this->Urls[$pid])) {
            $url = $this->Urls[$pid];
        }


        $content = HttpLoader::get($url, $this->RandomAgents);

        switch ($this->Format) {
            case "json":
                $content = json_decode($content);
                break;
        }

        return $content;
    }
}
