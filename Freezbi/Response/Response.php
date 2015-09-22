<?php
namespace Freezbi\Response;

class Response
{
    public $SendNotification = false;

    public $Title;

    public $Message;

    public $Data;

    public $IsList = false;

    public $InvalidateSubscription = false;


    public function __construct($title = null, $message = null, $data = null, $IsList = false)
    {
        $this->Title = $title;
        $this->Message = $message;
        $this->Data = $data;
        $this->IsList = $IsList;
    }

    public function render()
    {
        $output = array('result' => $this->SendNotification);

        if (!empty($this->Title) && $this->Title != null) {
            $output['title'] = $this->Title;
        }

        if (!empty($this->Message) && $this->Message != null) {
            $output['message'] = $this->Message;
            $output['list'] = $this->IsList;
        }

        if (!empty($this->Data) && $this->Data != null) {
            $output['data'] = $this->Data;
        }

        if (!empty($this->InvalidateSubscription) && $this->InvalidateSubscription != null && $this->InvalidateSubscription) {
            $output['invalidateSubscription'] = $this->InvalidateSubscription;
        }

        return $output;
    }


    public function renderJson()
    {
        return json_encode($this->render());
    }
}
