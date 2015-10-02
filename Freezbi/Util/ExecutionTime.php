<?php
namespace Freezbi\Util;

class ExecutionTime
{

    public $Hour;

    public $Minute;

    public $Second;

    public $Timezone;


    public function __construct($Hour = null, $Minute = null, $Second = null, $Timezone = null)
    {
        $this->Hour = $Hour;
        $this->Minute = $Minute;
        $this->Second = $Second;
        $this->Timezone = $Timezone;
    }
    

    public function validRange(\DateTime $date) {
        $valid = true;

        $baseDate = clone $date;

        if ($this->Timezone != null && $this->validTimezone($this->Timezone)) {
            $baseDate->setTimezone(new \DateTimeZone($this->Timezone));
        }

        if ($this->Hour != null && (int)$baseDate->format('H') != (int)$this->Hour) {
            $valid = false;
        }

        if ($this->Minute != null && (int)$baseDate->format('i') != (int)$this->Minute) {
            $valid = false;
        }

        if ($this->Second != null && (int)$baseDate->format('s') != (int)$this->Second) {
            $valid = false;
        }

        return $valid;
    }


    public function validTimezone($timezone){
        $validTimezones = array();
        $availableTimezones = timezone_abbreviations_list();

        foreach ($availableTimezones as $zone) {
            foreach ($zone as $item) {
                $validTimezones[$item['timezone_id']] = true;
            }
        }

        unset($validTimezones['']);
        return isset($validTimezones[$timezone]);
    }

}
