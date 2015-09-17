<?php
namespace Freezbi\Response;

class Response
{
    public $SendNotification = false;

    public $Title;

    public $Message;

    public $Data;


    public function __construct($title = null, $message = null, $data = null)
    {
        $this->Title = $title;
        $this->Message = $message;
        $this->Data = $data;
    }

    public function render()
    {
        $output = array('result' => $this->SendNotification);

        if (!empty($this->Title) && $this->Title != null) {
            $output['title'] = $this->Title;
        }

        if (!empty($this->Message) && $this->Message != null) {
            $output['message'] = $this->Message;
        }

        if (!empty($this->Data) && $this->Data != null) {
            $output['data'] = $this->Data;
        }

        return $output;
    }


    public function renderJson()
    {
        return json_encode($this->render());
    }
}
