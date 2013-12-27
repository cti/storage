<?php

namespace Integration;

use Exception;

class SapRfc implements SapRfcInterface
{
    /**
     * errors description
     * @var array
     */
    private $errors = array(
        SAPRFC_FAILURE => 'Error occurred',
        SAPRFC_EXCEPTION => 'Exception raised',
        SAPRFC_SYS_EXCEPTION => 'System exception raised, connection closed',
        SAPRFC_CALL => 'Call received',
        SAPRFC_INTERNAL_COM => 'Internal communication, repeat (internal use only)',
        SAPRFC_CLOSED => 'Connection closed by the other side',
        SAPRFC_RETRY => 'No data yet',
        SAPRFC_NO_TID => 'No Transaction ID available',
        SAPRFC_EXECUTED => 'Function already executed',
        SAPRFC_SYNCHRONIZE => 'Synchronous Call in Progress',
        SAPRFC_MEMORY_INSUFFICIENT => 'Memory insufficient',
        SAPRFC_VERSION_MISMATCH => 'Version mismatch',
        SAPRFC_NOT_FOUND => 'Function not found (internal use only)',
        SAPRFC_CALL_NOT_SUPPORTED => 'This call is not supported',
        SAPRFC_NOT_OWNER => 'Caller does not own the specified handle',
        SAPRFC_NOT_INITIALIZED => 'RFC not yet initialized.',
        SAPRFC_SYSTEM_CALLED => 'A system call such as RFC_PING for connectiontest is executed',
        SAPRFC_INVALID_HANDLE => 'An invalid handle was passed to an API call.',
        SAPRFC_INVALID_PARAMETER => 'An invalid parameter was passed to an API call.',
        SAPRFC_CANCELED => 'Internal use only',
    );

    /**
     * @param $config
     * @throws \Exception
     */
    function __construct($config)
    {
        if (!function_exists('saprfc_open')) {
            throw new Exception('no saprfc extension');
        }
        $this->connection = saprfc_open($config);

        if (!$this->connection) {
            throw new Exception(iconv('CP1251', 'UTF-8', saprfc_error()));
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
        $fce = saprfc_function_discover($this->connection, $name);

        if (!$fce) {
            throw new Exception("Error discovering " . $name, 1);
        }

        $export = array();
        foreach ($import as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $index => $row) {
                    if (is_object($row)) {
                        $row = get_object_vars($row);
                    }
                    foreach ($row as $row_key => $row_value) {
                        $row[$row_key] = iconv('UTF-8', 'CP1251', $row_value);
                    }
                    saprfc_table_insert($fce, $k, $row, $index + 1);
                }
            } else {
                saprfc_import($fce, $k, iconv('UTF-8', 'CP1251', $v));
            }
        }

        $result = saprfc_call_and_receive($fce);

        if ($result != SAPRFC_OK) {
            $message = isset($this->errors[$result]) ? $this->errors[$result] : 'Unknown error';
            throw new Exception($message);
        }

        $result = array();
        foreach ($export as $table) {

            $count = saprfc_table_rows($fce, $table);

            if ($count == -1) {
                // export param
                $data = iconv('CP1251', 'UTF-8', saprfc_export($fce, $table));

            } else {
                // export table
                $data = array();
                for ($i = 1; $i <= $count; $i++) {
                    $row = saprfc_table_read($fce, $table, $i);
                    foreach ($row as $k => $v) {
                        $row[$k] = iconv('CP1251', 'UTF-8', $v);
                    }
                    $data[] = (object)$row;
                }
            }
            $result[$table] = $data;
        }

        return (object)$result;
    }

    /**
     * Get debug information
     * @param $name
     * @return mixed
     */
    public function debug($name)
    {
        ob_start();
        $fce = saprfc_function_discover($this->connection, $name);
        saprfc_function_debug_info($fce);
        return ob_get_clean();
    }
}