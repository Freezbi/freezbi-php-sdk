<?php
namespace Freezbi\Response;

class ResponseList extends Response
{


    public function render()
    {
        $output = array('result' => $this->SendNotification);

        if (!empty($this->Title) && $this->Title != null) {
            $output['title'] = $this->Title;
        }

        if (!empty($this->Message) && $this->Message != null) {
			if (!is_array($this->Message)) {
				throw new \InvalidArgumentException('Message of ResponseList must be an array');
			}
			
            $output['message'] = json_encode($this->Message);
        }

        if (!empty($this->Data) && $this->Data != null) {
            $output['data'] = $this->Data;
        }

        return $output;
    }


}
