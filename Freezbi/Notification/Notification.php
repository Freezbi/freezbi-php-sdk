<?php
namespace Freezbi\Notification;

abstract class Notification
{
    public $Name;

    public $Url;

    public $Format;

    public $Action;

    public $RandomAgents = true;

    public $PostData = null;


    public function setName($name) {
        $this->Name = $name;

        return $this;
    }

    public function setUrl($url) {
        $this->Url = $url;

        return $this;
    }


    public function setHtmlOutput() {
        $this->Format = 'html';

        return $this;
    }

    public function setJsonOutput() {
        $this->Format = 'json';

        return $this;
    }

    public function setEnableRandomAgents() {
        $this->RandomAgents = true;

        return $this;
    }

    public function setDisableRandomAgents() {
        $this->RandomAgents = false;

        return $this;
    }

    public function setPostData($postData) {
        $this->PostData = $postData;

        return $this;
    }
}
