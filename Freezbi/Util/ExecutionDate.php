<?php
namespace Freezbi\Util;

class ExecutionDate
{

    public $Year;

    public $Month;

    public $Day;


    public function __construct($Year = null, $Month = null, $Day = null)
    {
        $this->Year = $Year;
        $this->Month = $Month;
        $this->Day = $Day;
    }


    public function validRange(\DateTime $date) {
        $valid = true;

        if ($this->Year != null && (int)$date->format('Y') != (int)$this->Year) {
            $valid = false;
        }

        if ($this->Month != null && (int)$date->format('m') != (int)$this->Month) {
            $valid = false;
        }

        if ($this->Day != null && (int)$date->format('d') != (int)$this->Day) {
            $valid = false;
        }

        return $valid;
    }
    
}
