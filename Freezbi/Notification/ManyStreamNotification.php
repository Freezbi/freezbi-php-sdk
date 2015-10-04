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

    private $InvalidateConfigurations;

    public function __construct($name = null, $configurations = array(), $url = null, $format = 'html')
    {
        $this->Name = $name;
        $this->Url = $url;
        $this->Multiple = true;
        $this->Format = strtolower($format);
        $this->Urls = array();
        $this->Delays = array();
        $this->Configurations = array();
        $this->InvalidateConfigurations = array();

        if (empty($configurations)) {
            $configurations = $_POST;
        }

        foreach ($configurations as $pid => $configuration) {
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


    public function setNoCallPolicy() {
        $this->Multiple = false;
        $this->Url = '';

        return $this;
    }

    public function setSingleCallPolicy() {
        $this->Multiple = false;

        return $this;
    }

    public function setMultipleCallPolicy() {
        $this->Multiple = true;

        return $this;
    }

    public function getConfigurations() {
        return $this->Configurations;
    }

    public function getSpecificConfiguration($pid) {
        return isset($this->Configurations[$pid]) ? $this->Configurations[$pid] : null;
    }


    public function ignoreSpecificConfiguration($pid) {
        unset($this->Configurations[$pid]);

        return $this;
    }

    public function invalidateSpecificConfiguration($pid) {
        $this->InvalidateConfigurations[$pid] = true;

        return $this;
    }



    public function getSpecificConfigurationInvalidation($pid) {
        return isset($this->InvalidateConfigurations[$pid]) ? true : false;
    }

    public function setSpecificUrl($pid, $url) {
        $this->Urls[$pid] = $url;

        return $this;
    }

    public function setSpecificDelay($pid, $delay) {
        $this->Delays[$pid] = $delay;

        return $this;
    }

    public function setSpecificPostData($pid, $postData) {
        $this->PostDatas[$pid] = $postData;

        return $this;
    }


}
