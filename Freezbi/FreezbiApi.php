<?php
namespace Freezbi;
include './Freezbi/Libraries/phpQuery/phpQuery.php';
include './Freezbi/Autoloader.php';

use \Freezbi\Util\StringTools;
use \Freezbi\Response\Response;
use \Freezbi\Notification\ManyStreamNotification;

/**
 * Freezbi API main entry point.
 * Provides managers to develop a response payload
 */
class FreezbiApi {

    public $Notification;

    public $Delay = 0;

    public $TemporaryFolder;

    public $NotificationFolder;

    public $LogFile;

    public $RemainingTime;

    public function __construct() {

    }


    public function prepare($notification) {

        // Init configuration variables
        $this->Notification = $notification;
        $temp = StringTools::endsWith($this->TemporaryFolder, '/') ? $this->TemporaryFolder : $this->TemporaryFolder.'/';
        $this->NotificationFolder = $temp.StringTools::clearFilename($this->Notification->Name);

        // Prepare notification folder
        if (!is_dir($this->NotificationFolder)) {
            mkdir($this->NotificationFolder,0777,true);
        }

        // Create/Read log file
        $logFilePath = $this->NotificationFolder.'/logfile';
        $readtype =  !file_exists($logFilePath) ? 'w+' : 'a+';
        $this->LogFile = @fopen($logFilePath,$readtype);
        $this->log("##################### START ####################","\n\n");
    }


    public function execute() {

        // If the delay isn't elapsed, return a dummy false response
        if (!$this->delayExecutionExpired($this->Delay)) {
            $this->log("> Delay isn't over yet : ".$this->RemainingTime."s left","\n");
            $response = new Response();
            return $response->renderJson();
        }


        // Many stream case
        if ($this->Notification instanceof ManyStreamNotification) {
            $renders = array();

            foreach($this->Notification->Configurations as $pid => $configuration) {
                $content = $this->Notification->execute($pid);
                $response = $this->Notification->Action->__invoke($pid, $configuration, $content);

                if (!$response instanceof Response) {
                    throw new \InvalidArgumentException('Callback must return a Freezbi\\Response object.');
                }

                $renders[$pid] = $response->render();
            }

            $this->closeLog();
            return json_encode($renders);
        }

        // Single stream case

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


    public function delayExecutionExpired($seconds) {
        $nowTimestamp = (int) time();
        $lastcheckTimestamp = 0;
        $lastCheckPath = $this->NotificationFolder.'/lastcheck';

        if (!file_exists($lastCheckPath)) {
            file_put_contents($lastCheckPath,'0');
        } else {
            $lastcheckTimestamp = (int) file_get_contents($lastCheckPath);
        }

        $this->RemainingTime = $nowTimestamp - $lastcheckTimestamp;
        
        if ($this->RemainingTime >= $seconds) {
            file_put_contents($lastCheckPath, $nowTimestamp);
            return true;
        }

        return false;
    }


    public function testSameAsBefore($keystring, $options = array()) {
        $keystringHash = md5($keystring);
        $same = false;

        $keepHistory = isset($options['keep_history']) ? $options['keep_history'] : false;
        $identifier = isset($options['identifier']) ? '/'.trim($options['identifier']) : '';

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
            $this->log($keystring." => now state => ". $oldKeystringHash,"\n> ");

            if (!$same) {
                file_put_contents($filePath, $keystringHash);
            }
        }

        return $same;
    }


    public function initPhpQueryOn($html) {
        \phpQuery::newDocumentHTML($html);
    }


    public function log($string, $prefix = '') {
        $date = new \DateTime();
        $prefix = sprintf("%s[%s] ", $prefix, $date->format('Y-m-d H:i:s'));
        $putString = $prefix.$string."\n";
        fputs($this->LogFile, $putString);
    }


    public function closeLog() {
        fclose($this->LogFile);
    }
}




	