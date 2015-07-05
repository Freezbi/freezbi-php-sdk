<?php
namespace Freezbi\Notification;

use \Freezbi\Http\HttpLoader;

class ManyStreamNotification extends Notification
{
    public $Configurations;

    public $Urls;

    public $Delays;

    public $Multiple;

    public function __construct($name = null, $post = array(), $url = null, $format = 'html')
    {
        $this->Name = $name;
        $this->Url = $url;
        $this->Multiple = true;
        $this->Format = strtolower($format);
        $this->Urls = array();
        $this->Delays = array();
        $this->Configurations = array();

		
        foreach ($post as $pid => $configuration) {
            $this->Configurations[$pid] = (array) json_decode($configuration);
        }
    }

    public function execute($pid = null)
    {
        $url = null;

        if ($pid != null) {
            if (isset($this->Urls[$pid])) {
                $url = $this->Urls[$pid];
            }
        } else {
            $url = $this->Url;
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
