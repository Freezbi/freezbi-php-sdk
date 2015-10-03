<?php
namespace Freezbi\Notification;

use \Freezbi\Http\HttpLoader;

class ManyStreamNotification extends Notification
{
    public $Configurations;

    public $Urls;

    public $Delays;

    public $Multiple;

    public $PostDatas = null;

    public function __construct($name = null, $post = array(), $url = null, $format = 'html')
    {
        $this->Name = $name;
        $this->Url = $url;
        $this->Multiple = true;
        $this->Format = strtolower($format);
        $this->Urls = array();
        $this->Delays = array();
        $this->Configurations = array();

        if (empty($post)) {
            $post = $_POST;
        }
		
        foreach ($post as $pid => $configuration) {
            $this->Configurations[$pid] = (array) json_decode($configuration);
        }
    }

    public function execute($pid = null)
    {
        $url = null;
        $postData = null;

        if ($pid != null) {
            if (isset($this->Urls[$pid])) {
                $url = $this->Urls[$pid];
            }

            if (isset($this->PostDatas[$pid])) {
                $postData = $this->PostDatas[$pid];
            }
        } else {
            $url = $this->Url;
        }


        $content = HttpLoader::get($url, $this->RandomAgents, $postData);

        switch ($this->Format) {
            case "json":
                $content = json_decode($content);
                break;
        }

        return $content;
    }



}
