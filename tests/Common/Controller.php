<?php

namespace Common;

class Controller
{

    function getIndex()
    {
        return 'index page';
    }

    function postUpload()
    {
        return 'uploading';
    }

    function processChain($chain)
    {
        return json_encode($chain);
    }
    
}