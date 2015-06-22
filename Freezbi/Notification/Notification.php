<?php
namespace Freezbi\Notification;

abstract class Notification {

    public $Name;

    public $Url;

    public $Format;

    public $Action;

    public $RandomAgents = true;

    public function execute() {
        throw new \RuntimeException('You must implement an \'execute\' method for that Notification implementation.');
    }


}