<?php
namespace Freezbi\Util;

class ExecutionTime
{

    public $Hour;

    public $Minute;

    public $Second;


    public function __construct($Hour = null, $Minute = null, $Second = null)
    {
        $this->Hour = $Hour;
        $this->Minute = $Minute;
        $this->Second = $Second;
    }
    

    public function validRange(\DateTime $date) {
        $valid = true;

        if ($this->Hour != null && (int)$date->format('H') != (int)$this->Hour) {
            $valid = false;
        }

        if ($this->Minute != null && (int)$date->format('i') != (int)$this->Minute) {
            $valid = false;
        }

        if ($this->Second != null && (int)$date->format('s') != (int)$this->Second) {
            $valid = false;
        }

        return $valid;
    }

}
