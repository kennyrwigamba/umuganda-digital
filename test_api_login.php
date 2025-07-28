<?php
/**
 * API Login Test
 * Test the actual API endpoint
 */

// Simulate the API request environment
$_SERVER['REQUEST_METHOD']        = 'POST';
$_SERVER['REQUEST_URI']           = '/api/auth/login';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Simulate JSON input for the API
$loginData = [
    'email'    => 'admin@umuganda-digital.rw',
    'password' => 'password',
];

// Create a temporary file to simulate php://input
$tmpfile = tmpfile();
fwrite($tmpfile, json_encode($loginData));
rewind($tmpfile);

// Override php://input
stream_wrapper_unregister('php');
stream_wrapper_register('php', 'MockPhpInputStream');

class MockPhpInputStream
{
    private $position;
    private static $data;

    public static function setData($data)
    {
        self::$data = $data;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->position = 0;
        return true;
    }

    public function stream_read($count)
    {
        if ($this->position >= strlen(self::$data)) {
            return false;
        }
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof()
    {
        return $this->position >= strlen(self::$data);
    }

    public function stream_stat()
    {
        return [];
    }
}

MockPhpInputStream::setData(json_encode($loginData));

echo "Testing API endpoint /api/auth/login\n";
echo "Request data: " . json_encode($loginData) . "\n\n";

try {
    // Include the API router
    require_once 'routes/api.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
