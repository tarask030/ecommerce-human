<?php

if (!function_exists('curl_init')) {
    throw new Exception('LiveSpace needs the CURL PHP extension.');
}
if (!function_exists('json_encode')) {
    throw new Exception('LiveSpace needs the JSON PHP extension.');
}


/**
 * LiveSpace PHP SDK
 */
class LiveSpace
{
    /**
     * SDK Version
     */
    const VERSION = '0.1';

    /**
     *
     */
    const API_URL_PATTERN = '%s/api/public/%s/%s';


    /**
     * Default options for curl.
     */
    protected static $_CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'livespace-sdk-php-0.1',
        CURLOPT_POST => true,
        CURLOPT_FAILONERROR => true,
        CURLOPT_HEADER => false
    );

    /**
     * @var array
     */
    protected static $_OPTIONS_DEFAULT = array(
        'format' => 'json',
        'auth_method' => 'key',
        'return_raw' => false
    );

    /**
     * @var array
     */
    protected $_options = array();


    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->init($options);
    }

    /**
     * @param $options
     */
    public function init($options)
    {
        $this->setOptions($options);
    }

    /**
     * @param $options
     * @return LiveSpace
     * @throws LiveSpaceException
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            throw new LiveSpaceException('Options should be an array.');
        }
        $this->_options = $options;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return LiveSpace
     */
    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return null
     */
    public function getOption($key, $defaultValue = null)
    {
        return array_key_exists($key, $this->_options) ? $this->_options[$key] : $defaultValue;
    }

    /**
     * @param $action
     * @param array $params
     * @return LiveSpaceResponse
     * @throws LiveSpaceSerializeException
     */
    public function call($action, $params = array(), $additionalOptions = array())
    {
        if (!is_array($params)) {
            $params = array();
        }

        $oldOptions = $this->getOptions();
        if (!is_array($additionalOptions)) {
            $additionalOptions = array();
        }
        $this->setOptions(array_merge($oldOptions, $additionalOptions));

        $format = $this->getOption('format', static::$_OPTIONS_DEFAULT['format']);

        if ('none' != $this->getOption('auth_method', static::$_OPTIONS_DEFAULT['auth_method'])) {
            $className = 'LiveSpaceAuthorize' . ucfirst(strtolower($this->getOption('auth_method', static::$_OPTIONS_DEFAULT['auth_method'])));
            if (!class_exists($className)) {
                throw new LiveSpaceSerializeException('Authorize class for method \'' . $this->getOption('auth_method', 'none') . '\' does not exists.');
            }
            $auth = new $className($this);
            $result = $auth->call($action, $format, $params);
        } else {
            $result = $this->_call($action, $format, $params);
        }

        if (false === $this->getOption('return_raw', static::$_OPTIONS_DEFAULT['return_raw'])) {
            $result = new LiveSpaceResponse(LiveSpaceSerialize::unserialize($result, $format));
        }

        $this->setOptions($oldOptions);

        return $result;
    }

    /**
     * @param $action
     * @param $format
     * @param null $params
     * @return mixed
     * @throws LiveSpaceException
     */
    public function _call($action, $format, $params = array())
    {
        if (!$this->getOption('api_url') || !$this->getOption('api_url')) {
            throw new LiveSpaceException('Options \'api_url\' is required.');
        }

        $opts = static::$_CURL_OPTS;
        $opts[CURLOPT_URL] = sprintf(static::API_URL_PATTERN, $this->getOption('api_url'), $format, $action);
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params);

        if (isset($opts[CURLOPT_HTTPHEADER])) {
            $existing_headers = $opts[CURLOPT_HTTPHEADER];
            $existing_headers[] = 'Expect:';
            $opts[CURLOPT_HTTPHEADER] = $existing_headers;
        } else {
            $opts[CURLOPT_HTTPHEADER] = array('Expect:');
        }

        if (substr($this->getOption('api_url'), 0, 5) == 'https') {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 2;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);

        if (false === $result) {
            $m = 'Curl error #' . curl_errno($ch) . ' msg: ' . curl_error($ch);
            curl_close($ch);
            throw new LiveSpaceException($m);
        }

        curl_close($ch);

        return $result;
    }
}

/**
 *
 */
class LiveSpaceException extends Exception {}

/**
 *
 */
abstract class LiveSpaceAuthorize
{
    /**
     * @var LiveSpace|null
     */
    protected $_liveSpace = null;


    /**
     * @param LiveSpace $liveSpace
     */
    public function __construct(LiveSpace $liveSpace)
    {
        $this->_liveSpace = $liveSpace;
        $this->init();
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * @param $action
     * @param $format
     * @param array $params
     * @return mixed
     */
    abstract public function call($action, $format, $params = array());
}

/**
 *
 */
class LiveSpaceAuthorizeKey extends LiveSpaceAuthorize
{
    /**
     * @throws LiveSpaceException
     */
    public function init()
    {
        if (!$this->_liveSpace->getOption('api_key') || !$this->_liveSpace->getOption('api_secret')) {
            throw new LiveSpaceException('Options \'api_key\' and \'api_secret\' are required.');
        }
    }

    /**
     * @return mixed
     * @throws LiveSpaceException
     */
    protected function _getToken()
    {
        $result = $this->_liveSpace->_call('_Api/auth_call/_api_method/getToken', 'json', array(
            '_api_auth' => 'key',
            '_api_key' => $this->_liveSpace->getOption('api_key'),
        ));
        $result = LiveSpaceSerialize::factory('json')->unserialize($result);

        if (NULL === $result || !isset($result['data']) || !isset($result['data']['session_id']) || !isset($result['data']['token'])) {
            throw new LiveSpaceException('Cannot get token for authorization.');
        }
        return $result['data'];
    }

    /**
     * @param $token
     * @param $sessionId
     * @return array
     */
    protected function _getAuthParams($token, $sessionId)
    {
        return array(
            '_api_auth' => 'key',
            '_api_key' => $this->_liveSpace->getOption('api_key'),
            '_api_sha' => sha1($this->_liveSpace->getOption('api_key') . $token . $this->_liveSpace->getOption('api_secret')),
            '_api_session' => $sessionId,
        );
    }

    /**
     * @param $action
     * @param $format
     * @param array $params
     * @return mixed
     * @throws LiveSpaceException
     */
    public function call($action, $format, $params = array())
    {
        $tokenData = $this->_getToken();

        return $this->_liveSpace->_call($action, $format, array('data' => json_encode(array_merge($this->_getAuthParams($tokenData['token'], $tokenData['session_id']), $params))));
    }
}

/**
 *
 */
class LiveSpaceResponse
{
    /**
     *
     */
    const DATA_INDEX ='data';

    /**
     *
     */
    const RESULT_INDEX ='result';

    /**
     *
     */
    const STATUS_INDEX ='status';

    /**
     *
     */
    const ERROR_INDEX ='error';


    /**
     * @var array
     */
    protected $_responseData = array();


    /**
     * @param array $responseData
     */
    public function __construct(array $responseData = null)
    {
        if ($responseData) {
            $this->setResponseData($responseData);
        }
    }

    /**
     * @param $responseData
     * @return LiveSpaceResponse
     */
    public function setResponseData($responseData)
    {
        $this->_responseData = $responseData;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponseData()
    {
        return $this->_responseData;
    }

    /**
     * @return null|array
     */
    public function getData()
    {
        return $this->_getData(static::DATA_INDEX);
    }

    /**
     * @return null|int
     */
    public function getResult()
    {
        return $this->_getData(static::RESULT_INDEX);
    }

    /**
     * @return null|bool
     */
    public function getStatus()
    {
        return $this->_getData(static::STATUS_INDEX);
    }

    /**
     * @return null|array
     */
    public function getError()
    {
        return $this->_getData(static::ERROR_INDEX);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function _getData($key)
    {
        return array_key_exists($key, $this->_responseData) ? $this->_responseData[$key] : null;
    }
}

/**
 *
 */
class LiveSpaceSerializeException extends LiveSpaceException {};

/**
 *
 */
interface LiveSpaceSerializeInterface
{
    /**
     * @param $data
     * @return mixed
     */
    public function serialize($data);

    /**
     * @param $data
     * @return mixed
     */
    public function unserialize($data);
}

/**
 *
 */
class LiveSpaceSerializePhp //implements LiveSpaceSerializeInterface
{
    /**
     * @param $data
     * @return string
     */
    public function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws LiveSpaceSerializeException
     */
    public function unserialize($data)
    {
        $result = unserialize($data);
        if (false === $result) {
            throw new LiveSpaceSerializeException('Cannot unserialize data.');
        }
        return $result;
    }
}

/**
 *
 */
class LiveSpaceSerializeJson //implements LiveSpaceSerializeInterface
{
    /**
     * @param $data
     * @return string
     */
    public function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws LiveSpaceSerializeException
     */
    public function unserialize($data)
    {
        $result = json_decode($data, true);
        if (null === $result) {
            throw new LiveSpaceSerializeException('Cannot unserialize data.');
        }
        return $result;
    }
}

/**
 *
 */
class LiveSpaceSerialize
{
    /**
     * @param $format
     * @return mixed
     * @throws LiveSpaceSerializeException
     */
    public static function factory($format)
    {
        $className = 'LiveSpaceSerialize' . ucfirst(strtolower($format));
        if (!class_exists($className)) {
            throw new LiveSpaceSerializeException('Serialize class for format \'' . $format . '\' does not exists.');
        }
        return new $className();
    }

    /**
     * @param $data
     * @param $format
     * @return mixed
     */
    public static function serialize($data, $format)
    {
        return static::factory($format)->serialize($data);
    }

    /**
     * @param $data
     * @param $format
     * @return mixed
     */
    public static function unserialize($data, $format)
    {
        return static::factory($format)->unserialize($data);
    }
}