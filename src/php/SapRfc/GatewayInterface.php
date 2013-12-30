<?php

namespace SapRfc;

interface GatewayInterface
{
    /**
     * Execute function method
     * @param $name
     * @param $import
     * @param $export
     * @return object
     */
    public function execute($name, $import, $export);

    /**
     * Get debug information
     * @param $name
     * @return mixed
     */
    public function debug($name);

} 