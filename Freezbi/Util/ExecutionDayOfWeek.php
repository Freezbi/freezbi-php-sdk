<?php
namespace Freezbi\Util;

class ExecutionDayOfWeek
{

    public $DayOfWeek;

    public function __construct($DayOfWeek)
    {
        $this->DayOfWeek = $DayOfWeek;
    }


    public function validRange(\DateTime $date) {
        $valid = true;

        if ($this->DayOfWeek != null && (int)$date->format('w') != (int)$this->DayOfWeek) {
            $valid = false;
        }

        return $valid;
    }
    
}
