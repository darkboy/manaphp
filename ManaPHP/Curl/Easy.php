<?php
namespace ManaPHP\Curl;

use ManaPHP\Component;
use ManaPHP\Curl\Exception as ClientException;

/**
 * Class ManaPHP\Curl\Easy
 *
 * @package Curl
 */
class Easy extends Component implements EasyInterface
{
    /**
     * @var array
     */
    protected $_headers = [];

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var int
     */
    protected $_responseCode;

    /**
     * @var array
     */
    protected $_responseHeaders;

    /**
     * @var string
     */
    protected $_responseBody;

    /**
     * @var array
     */
    protected $_curlInfo;

    /**
     * @var bool
     */
    protected $_peek = false;

    /**
     * @var string
     */
    protected $_proxy = '';

    /**
     * Client constructor.
     *
     * @param array $options
     *    - `timeout`: How long should we wait for a response?
     *    (integer, seconds, default: 10)
     *    (string, default: '')
     *    - `ssl_certificates`: Should we verify SSL certificates? Allows passing in a custom
     *    certificate file as a string. (Using true uses the system-wide root
     *    certificate store instead, but this may have different behaviour
     *    across transports.)
     *    (string, default: 'xxx/ca.pem')
     *    - `verify_host`: Should we verify the common name in the SSL certificate?
     *    (bool: default, true)
     *
     * @param array $headers
     *
     * - `User-Agent`: User Agent to send to the server
     *   (string, default: php-requests/$version)
     *
     * @throws \ManaPHP\Curl\Exception
     */
    public function __construct($options = [], $headers = [])
    {
        if (!function_exists('curl_init')) {
            throw new ClientException('curl extension is not loaded: http://php.net/curl'/**m01df15300bf1482df*/);
        }

        $this->_options = $options + [
                'timeout' => 10,
                'proxy' => '',
                'ssl_certificates' => '@manaphp/Curl/https/ca.pem',
                'verify_host' => true,
            ];

        $this->_headers = $headers + ['User-Agent' => 'ManaPHP/httpClient'];
    }

    /**
     * @param string $proxy
     * @param bool   $peek
     *
     * @return static
     */
    public function setProxy($proxy = '127.0.0.1:8888', $peek = true)
    {
        if (strpos($proxy, '://') === false) {
            $this->_proxy = 'http://' . $proxy;
        } else {
            $this->_proxy = $proxy;
        }

        $this->_peek = $peek;

        return $this;
    }

    /**
     * @param string       $type
     * @param string|array $url
     * @param string|array $data
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    protected function request($type, $url, $data, $headers, $options)
    {
        $this->_responseBody = false;

        if (is_array($url)) {
            if (count($url) > 1) {
                $uri = $url[0];
                unset($url[0]);
                $url = $uri . (strpos($uri, '?') !== false ? '&' : '?') . http_build_query($url);
            } else {
                $url = $url[0];
            }
        }

        if (preg_match('/^http(s)?:\/\//i', $url) !== 1) {
            throw new ClientException(['only HTTP requests can be handled: `:url`'/**m06c8af26e23f01884*/, 'url' => $url]);
        }

        $headers = array_merge($this->_headers, $headers);
        $options = array_merge($this->_options, $options);

        $eventData = ['type' => $type, 'url' => &$url, 'headers' => &$headers, 'data' => &$data, 'options' => &$options];
        $this->fireEvent('httpClient:beforeRequest', $eventData);
        $httpCode = $this->_request($type, $url, $data, $headers, $options);
        $eventData = [
            'type' => $type,
            'url' => $url,
            'headers' => $headers,
            'data' => $data,
            'options' => $options,
            'httpCode' => &$httpCode,
            'responseBody' => &$this->_responseBody
        ];
        $this->fireEvent('httpClient:afterResponse', $eventData);
        return $httpCode;
    }

    /**
     * @param string       $type
     * @param string       $url
     * @param string|array $data
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    public function _request($type, $url, $data, $headers, $options)
    {
        $this->_responseCode = null;
        $this->_responseHeaders = null;
        $this->_responseBody = null;

        $this->_curlInfo = [];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 8);

        if (isset($headers['Cookie'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $headers['Cookie']);
        }

        if (is_array($data)) {
            $hasFiles = false;
            /** @noinspection ForeachSourceInspection */
            foreach ($data as $k => $v) {
                if (is_string($v) && strlen($v) > 1 && $v[0] === '@' && is_file(substr($v, 1))) {
                    $hasFiles = true;
                    if (class_exists('CURLFile')) {
                        $file = substr($v, 1);

                        $parts = explode(';', $file);

                        if (count($parts) === 1) {
                            $data[$k] = new \CURLFile($file);
                        } else {
                            $file = $parts[0];
                            $types = explode('=', $parts[1]);
                            if ($types[0] !== 'type' || count($types) !== 2) {
                                throw new ClientException(['`:file` file name format is invalid'/**m05efb8755481bd2eb*/, 'file' => $v]);
                            } else {
                                $data[$k] = new \CURLFile($file, $types[1]);
                            }
                        }
                    }
                } elseif (is_object($v)) {
                    $hasFiles = true;
                }
            }

            if (!$hasFiles) {
                $data = http_build_query($data);
            }
        }

        switch ($type) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PATCH':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'HEAD':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $options['timeout']);
        curl_setopt($curl, CURLOPT_REFERER, isset($headers['Referer']) ? $headers['Referer'] : $url);
        curl_setopt($curl, CURLOPT_USERAGENT, $headers['User-Agent']);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        unset($headers['Referer'], $headers['User-Agent'], $headers['Cookie']);

        $formatted_headers = [];
        foreach ($headers as $k => $v) {
            if (is_int($k)) {
                $formatted_headers[] = $v;
            } else {
                $formatted_headers[] = $k . ': ' . $v;
            }
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $formatted_headers);

        if ($this->_proxy) {
            $parts = parse_url($this->_proxy);
            $scheme = $parts['scheme'];
            if ($scheme === 'http') {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            } elseif ($scheme === 'sock4') {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            } elseif ($scheme === 'sock5') {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            } else {
                throw new ClientException(['`:scheme` scheme of `:proxy` proxy is unknown', 'scheme' => $scheme, 'proxy' => $this->_proxy]);
            }

            curl_setopt($curl, CURLOPT_PROXYPORT, $parts['port']);
            curl_setopt($curl, CURLOPT_PROXY, $parts['host']);
            if (isset($parts['user'], $parts['pass'])) {
                curl_setopt($curl, CURLOPT_PROXYUSERNAME, $parts['user']);
                curl_setopt($curl, CURLOPT_PROXYPASSWORD, $parts['pass']);
            }
        }

        if ($options['ssl_certificates']) {
            if ($this->_peek && $this->_options['proxy'] !== '') {
                curl_setopt($curl, CURLOPT_CAINFO, $this->alias->resolve('@manaphp/Http/Client/fiddler.cer'));
            } else {
                curl_setopt($curl, CURLOPT_CAINFO, $this->alias->resolve($options['ssl_certificates']));
            }
        } else {
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $options['verify_host'] ? 2 : 0);

        $content = curl_exec($curl);

        $err = curl_errno($curl);
        if ($err === 23 || $err === 61) {
            curl_setopt($curl, CURLOPT_ENCODING, 'none');
            $content = curl_exec($curl);
        }

        if (curl_errno($curl)) {
            throw new ClientException(['cURL `:url` error: :message'/**m0d2c9a60b72a0362f*/, 'url' => $url, 'message' => curl_error($curl)]);
        }

        $header_length = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $this->_responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->_responseBody = substr($content, $header_length);
        $this->_responseHeaders = explode("\r\n", substr($content, 0, $header_length - 4));

        $this->_curlInfo = curl_getinfo($curl);

        curl_close($curl);

        return $this->_responseCode;
    }

    /**
     * @param array|string $url
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    public function get($url, $headers = [], $options = [])
    {
        return $this->request('GET', $url, null, $headers, $options);
    }

    /**
     * @param array|string $url
     * @param string|array $data
     * @param array        $headers
     * @param array        $options
     *
     * @return mixed
     * @throws \ManaPHP\Curl\Exception
     */
    public function post($url, $data = [], $headers = [], $options = [])
    {
        return $this->request('POST', $url, $data, $headers, $options);
    }

    /**
     * @param array|string $url
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    public function delete($url, $headers = [], $options = [])
    {
        return $this->request('DELETE', $url, null, $headers, $options);
    }

    /**
     * @param array|string $url
     * @param string|array $data
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    public function put($url, $data = [], $headers = [], $options = [])
    {
        return $this->request('PUT', $url, $data, $headers, $options);
    }

    /**
     * @param array|string $url
     * @param string|array $data
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    public function patch($url, $data = [], $headers = [], $options = [])
    {
        return $this->request('PATCH', $url, $data, $headers, $options);
    }

    /**
     * @param array|string $url
     * @param string|array $data
     * @param array        $headers
     * @param array        $options
     *
     * @return int
     * @throws \ManaPHP\Curl\Exception
     */
    public function head($url, $data = [], $headers = [], $options = [])
    {
        return $this->request('HEAD', $url, $data, $headers, $options);
    }

    /**
     * @param string $url
     * @param string $file
     * @param array  $options
     *
     * @return static
     * @throws \ManaPHP\Curl\Exception
     */
    public function downloadFile($url, $file, $options = [])
    {
        $file = $this->alias->resolve($file);
        if (!is_file($file)) {
            $this->filesystem->dirCreate(dirname($file));

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, isset($options['timeout']) ? $options['timeout'] : 3);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, isset($options['timeout']) ? $options['timeout'] : 3);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko');
            curl_setopt($curl, CURLOPT_HEADER, 0);

            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            if (($fp = fopen($file, 'wb+')) === false) {
                curl_close($curl);
                throw new ClientException(['open download `:file` file failed for `:url`', 'file' => $file, 'url' => $url]);
            }

            curl_setopt($curl, CURLOPT_FILE, $fp);
            curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);

            foreach ($options as $k => $v) {
                if (is_int($k)) {
                    curl_setopt($curl, $k, $v);
                }
            }

            curl_exec($curl);
            fclose($fp);
            if (curl_errno($curl)) {
                curl_close($curl);
                throw new ClientException(['cURL `:url` error: :message'/**m0d2c9a60b72a0362f*/, 'url' => $url, 'message' => curl_error($curl)]);
            }

            curl_close($curl);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     * @param bool $assoc
     *
     * @return array
     */
    public function getResponseHeaders($assoc = true)
    {
        if (!$assoc) {
            return $this->_responseHeaders;
        }

        $headers = [];
        foreach ($this->_responseHeaders as $i => $header) {
            if ($i === 0) {
                continue;
            }

            list($name, $value) = explode(': ', $header, 2);
            if (isset($headers[$name])) {
                if (!is_array($headers[$name])) {
                    $headers[$name] = [$headers[$name]];
                }

                $headers[$name][] = $value;

            } else {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return $this->_responseBody;
    }
}