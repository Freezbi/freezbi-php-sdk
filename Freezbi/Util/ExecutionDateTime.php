<?php
namespace Freezbi\Util;

class ExecutionDateTime
{

    public $ExecutionDate;

    public $ExecutionTime;


    public function __construct($Year = null, $Month = null, $Day = null, $Hour = null, $Minute = null, $Second = null)
    {
        $this->ExecutionDate = new ExecutionDate($Year, $Month, $Day);
        $this->ExecutionTime = new ExecutionTime($Hour, $Minute, $Second);
    }


    public function validRange(\DateTime $date) {
        return $this->ExecutionDate->validRange($date) && $this->ExecutionTime->validRange($date);
    }
    
}
