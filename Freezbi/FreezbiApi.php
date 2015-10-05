<?php
namespace Freezbi;

include dirname(__FILE__).'/Libraries/phpQuery/phpQuery.php';
include dirname(__FILE__).'/Autoloader.php';

use \Freezbi\Util\StringTools;
use \Freezbi\Util\ExecutionTime;
use \Freezbi\Util\ExecutionDate;
use \Freezbi\Util\ExecutionInterval;
use \Freezbi\Response\Response;
use \Freezbi\Notification\ManyStreamNotification;

/**
 * Freezbi API main entry point.
 * Provides managers to develop a response payload
 */
class FreezbiApi
{
    public $Notification;

    public $Delay = 0;

    public $TemporaryFolder;

    public $NotificationFolder;

    public $LogFile;

    public $RemainingTime;

    public static $FLAG_HISTORY_MODE = true;
    public static $FLAG_SWITCH_MODE = false;


    public function __construct()
    {
    }


    public function prepare($notification)
    {

        // Init configuration variables
        $this->Notification = $notification;
        $temp = StringTools::endsWith($this->TemporaryFolder, '/') ? $this->TemporaryFolder : $this->TemporaryFolder.'/';
        $this->NotificationFolder = $temp.StringTools::clearFilename($this->Notification->Name);

        // Prepare notification folder
        if (!is_dir($this->NotificationFolder)) {
            mkdir($this->NotificationFolder, 0777, true);
        }

        // Create/Read log file
        $logFilePath = $this->NotificationFolder.'/logfile';
        $readtype =  !file_exists($logFilePath) ? 'w+' : 'a+';
        $this->LogFile = @fopen($logFilePath, $readtype);
    }


    public function execute()
    {
        // If the delay isn't elapsed, return a dummy false response
        if (!$this->delayExecutionExpired($this->Delay)) {
            $this->log("> Delay isn't over yet : ".$this->RemainingTime."s left", "\n");
            $response = new Response();
            return $response->renderJson();
        }

        $this->log("##################### START ####################", "\n\n");

        // Many stream case
        if ($this->Notification instanceof ManyStreamNotification) {
            return $this->renderManyStream();
        }

        return $this->renderSingleStream();
    }


    public function renderManyStream() {
        $renders = array();
        $content = $this->Notification->Multiple ? '' : $this->Notification->execute();

        foreach ($this->Notification->Configurations as $pid => $configuration) {

            if (isset($this->Notification->Delays[$pid]) && !$this->delayExecutionExpired($this->Notification->Delays[$pid],$pid)) {
                $response = new Response();
            } else {
                $inputContent = $this->Notification->Multiple ? $this->Notification->execute($pid) : $content;
                $response = $this->Notification->Action->__invoke($pid, $configuration, $inputContent);
                $response->InvalidateSubscription = $this->Notification->getSpecificConfigurationInvalidation($pid);
            }

            if (!$response instanceof Response) {
                throw new \InvalidArgumentException('Callback must return a Freezbi\\Response object.');
            }

            $renders[$pid] = $response->render();
        }

        $this->closeLog();
        return json_encode($renders);
    }


    public function renderSingleStream() {
        // Get remote url data
        $content = $this->Notification->execute();

        // Call process on the body response
        $response = $this->Notification->Action->__invoke($content);

        // Check and render the response
        if (!$response instanceof Response) {
            throw new \InvalidArgumentException('Callback must return a Freezbi\\Response object.');
        }

        $this->closeLog();
        return $response->renderJson();
    }

    public function delayExecutionExpired($time, $pid = '')
    {
        $now = new \DateTime();
        $nowTimestamp = (int) $now->format('U');
        $lastcheckTimestamp = 0;
        $lastCheckPath = $pid != '' ? $this->NotificationFolder.'/'.$pid.'/lastcheck' : $this->NotificationFolder.'/lastcheck';

        if ($pid != '') {
            if (!is_dir(dirname($lastCheckPath))) {
                mkdir(dirname($lastCheckPath),0777,true);
            }
        }

        if (!file_exists($lastCheckPath)) {
            file_put_contents($lastCheckPath, '0');
        } else {
            $lastcheckTimestamp = (int) file_get_contents($lastCheckPath);
        }

        $this->RemainingTime = $nowTimestamp - $lastcheckTimestamp;

        if (is_array($time)) {
            foreach($time as $executionPeriod) {
                if ($executionPeriod->validRange($now)) {
                    file_put_contents($lastCheckPath, $nowTimestamp);
                    return true;
                }
            }
        } else if ($time instanceof ExecutionTime) {
            if ($time->validRange($now)) {
                file_put_contents($lastCheckPath, $nowTimestamp);
                return true;
            }
        } else if ($time instanceof ExecutionDate) {
            if ($time->validRange($now)) {
                file_put_contents($lastCheckPath, $nowTimestamp);
                return true;
            }
        } else if ($time instanceof ExecutionInterval) {
            if ($time->validRange($this->RemainingTime)) {
                file_put_contents($lastCheckPath, $nowTimestamp);
                return true;
            }
        } else if ($this->RemainingTime >= $time) {
            file_put_contents($lastCheckPath, $nowTimestamp);
            return true;
        }

        return false;
    }

    /*
     * Return the DateTime of the last time you checked the notification
     *
     * @param (string) $pid = the pid of the task if there is not => ''
     * @return (DateTime) $lastcheckDateTime
     */
	public function getLastCheck($pid = ''){
		// Get folder to seek in
        $lastcheckDateTime = new \DateTime();
        $lastcheckPath = !empty($pid) ? $this->NotificationFolder.'/'.$pid.'/lastcheck' : $this->NotificationFolder.'/lastcheck';

		if(file_exists($lastcheckPath)){
            $lastcheckTimestamp = (int) file_get_contents($lastcheckPath);
            $lastcheckDateTime = $lastcheckDateTime->setTimestamp($lastcheckTimestamp);
		}

        return $lastcheckDateTime;
	}



    public function alreadyExists($keystring, $mode = true, $initValue = '-1', $identifier = null) {
        if ($mode == self::$FLAG_SWITCH_MODE) {
            return $this->alreadyExistsModeSwitch($keystring, $initValue, $identifier);
        }

        return $this->alreadyExistsModeHistory($keystring, $identifier);
    }

    public function alreadyExistsModeHistory($keystring, $identifier = null) {
        return $this->testSameAsBefore($keystring, array(
            'keep_history' => self::$FLAG_HISTORY_MODE,
            'identifier' => $identifier
        ));
    }

    public function alreadyExistsModeSwitch($keystring, $initValue = '-1', $identifier = null) {
        return $this->testSameAsBefore($keystring, array(
            'keep_history' => self::$FLAG_SWITCH_MODE,
            'init_value' => $initValue,
            'identifier' => $identifier
        ));
    }


    public function testSameAsBefore($keystring, $options = array())
    {
		$keystringHash = md5($keystring);
        $same = false;

        $keepHistory = isset($options['keep_history']) ? $options['keep_history'] : false;
        $identifier = isset($options['identifier']) && $options['identifier'] != null ? '/'.trim($options['identifier']) : '';


        if ($keepHistory) {
            $historyPath = $this->NotificationFolder.$identifier;

            if (!is_dir($historyPath)) {
                mkdir($historyPath, 0777, true);
            }

            $filePath = $historyPath.'/'.$keystringHash;
            $same = file_exists($filePath);

            if (!$same) {
                file_put_contents($filePath, $keystring);
            }
        } else {
            $historyPath = $this->NotificationFolder.$identifier;

            if (!is_dir($historyPath)) {
                mkdir($historyPath, 0777, true);
            }

            $filePath = $historyPath.'/old_trigger_state';

            if (!file_exists($filePath)) {
                file_put_contents($filePath,  isset($options['init_value']) ? $options['init_value'] : $keystringHash);
            }

            $oldKeystringHash = file_get_contents($filePath);
            $same = $oldKeystringHash == $keystringHash;
            $this->log($keystring." => now state => ". $oldKeystringHash, "\n> ");

            if (!$same) {
                file_put_contents($filePath, $keystringHash);
            }
        }

        return $same;
    }


    public function initPhpQueryOn($html)
    {
        \phpQuery::newDocumentHTML($html);
    }


    public function log($string, $prefix = '')
    {
        $date = new \DateTime();
        $prefix = sprintf("%s[%s] ", $prefix, $date->format('Y-m-d H:i:s'));
        $putString = $prefix.$string."\n";
        fputs($this->LogFile, $putString);
    }


    public function closeLog()
    {
        fclose($this->LogFile);
    }

    public function setTemporaryFolder($temporaryFolder)
    {
        $this->TemporaryFolder = $temporaryFolder;
    }

    public function getTemporaryFolder()
    {
        return $this->TemporaryFolder;
    }

    public function setGlobalDelay($delay)
    {
        $this->Delay = $delay;
    }

    public function getGlobalDelay()
    {
        return $this->Delay;
    }

    public function getNotification()
    {
        return $this->Notification;
    }

}
