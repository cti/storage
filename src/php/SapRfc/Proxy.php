<?php

namespace SapRfc;

use Exception;

class Proxy implements GatewayInterface
{
    /**
     * proxy url
     * @var string
     */
    public $proxy;

    /**
     * @var array
     */
    public $extra = array();

    /**
     * timeout seconds
     * @var int
     */
    public $timeout = 1800;

    /**
     * @param SapRfc $sap
     * @param array $params
     */
    public function processRequest(SapRfc $sap, $params = null)
    {
        try {

            if (!$params) {
                $params = $_POST;
            }

            $method = $params['method'];
            if (!in_array($method, array('execute', 'debug'))) {
                throw new Exception("Unknown method $method");
            }

            $request = json_decode($params['request']);
            if (!$request) {
                throw new Exception("No valid request found");
            }

            $data = $sap->$method($request->name, $request->import, $request->export);
            echo json_encode(array('data' => $data));

        } catch (Exception $e) {
            echo json_encode(array('exception' => $e->getMessage()));
        }
    }

    /**
     * @param $name
     * @param $import
     * @param $export
     * @throws \Exception
     * @return object
     */
    public function execute($name, $import, $export)
    {
        $text = file_get_contents($this->proxy, false, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'timeout' => $this->timeout,
                'content' => http_build_query(array(
                    'method' => 'execute',
                    'request' => json_encode(array(
                        'name' => $name,
                        'import' => $import,
                        'export' => $export,
                    )),
                    'extra' => json_encode($this->extra)
                )),
            ),
        )));
        $result = json_decode($text);
        if (!$result) {
            throw new Exception("Error Processing Request.<br/>" . $text);
        } elseif (isset($result->exception)) {
            throw new Exception($result->exception);
        }

        return $result->data;
    }

    /**
     * Get debug information
     * @param $name
     * @throws \Exception
     * @return mixed
     */
    public function debug($name)
    {
        $text = file_get_contents($this->proxy, false, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'timeout' => $this->timeout,
                'content' => http_build_query(array(
                    'method' => 'debug',
                    'request' => json_encode(array(
                        'name' => $name,
                    )),
                    'extra' => json_encode($this->extra)
                )),
            ),
        )));
        $result = json_decode($text);
        if (!$result) {
            throw new Exception("Error Processing Request: " . $text);

        } elseif (isset($result->exception)) {
            throw new Exception($result->exception);
        }

        return $result->data;
    }
}