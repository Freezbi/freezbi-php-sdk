<?php
namespace Freezbi\Notification;

use \Freezbi\Http\HttpLoader;

class SingleStreamNotification extends Notification {



    public function __construct($name = null, $url = null, $format = 'json') {
        $this->Name = $name;
        $this->Url = $url;
        $this->Format = strtolower($format);
    }

    public function execute() {
        if ($this->Url == null) {
            return null;
        }

        $content = HttpLoader::get($this->Url, $this->RandomAgents);

        switch ($this->Format) {
            case "json":
                $content = json_decode($content);
            break;
        }

        return $content;
    }




}