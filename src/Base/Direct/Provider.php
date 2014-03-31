<?php

namespace Base\Direct;

use Application\Web;
use Di\Manager;

class Provider
{
    /**
     * @var string
     */
    public $url = 'direct';

    function getIndex(Web $application, Service $service)
    {
        echo 'Ext.Direct.addProvider({
            type: "remoting",
            url: "'. $application->base . $this->url . '",
            actions: '.json_encode($service->getActions()).'
        });';
    }

    function postIndex(Manager $manager, Service $service)
    {
        $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
        if(!is_array($data)) {
            $response = $service->handle($manager, Request::create($data));

        } else {
            $response = array();
            foreach($data as $request) {
                $response[] = $service->handle($manager, Request::create($request));
            }
        }

        echo json_encode($response);
    }
}