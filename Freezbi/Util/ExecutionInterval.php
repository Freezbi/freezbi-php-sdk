<?php
namespace Freezbi\Util;

class ExecutionInterval
{


    public $Interval;


    public function __construct($Interval)
    {
        $this->Interval = $Interval;
    }


    public function validRange($remainingTime) {
        $valid = true;

        if ($remainingTime < $this->Interval) {
            $valid = false;
        }

        return $valid;
    }
    
}
