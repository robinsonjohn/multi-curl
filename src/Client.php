<?php

namespace Bayfront\MultiCurl;

use Bayfront\MimeTypes\MimeType;

class Client extends ClientParent
{

    private const DEFAULT_HANDLE = 'default';

    /**
     * Constructor
     *
     * @param string $base_url
     *
     */
    public function __construct(string $base_url = '')
    {

        parent::__construct($base_url);

        /*
         * Create handle for this client
         * Essentially mimicking Async create() method
         */

        $this->handles[self::DEFAULT_HANDLE] = curl_init();

        $this->current_handle = self::DEFAULT_HANDLE;

        $this->setDefaultOptions();

    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Execute the given cURL session.
     *
     * @return void
     */
    private function execute(): void
    {

        $id = self::DEFAULT_HANDLE;

        $handle = $this->handles[self::DEFAULT_HANDLE];

        $this->curlSetOpt($id, $handle);

        // cURL response

        if (isset($this->requests[$id]['options'][CURLOPT_HEADER]) && true === $this->requests[$id]['options'][CURLOPT_HEADER]) { // If headers are part of the response

            $response = curl_exec($handle);

            $this->curlProcessResponse($id, $handle, $response);

        } else {

            $this->responses[$id]['body'] = curl_exec($handle);

        }

        $this->curlSetResponseInfo($id, $handle);

        $this->reset(); // Reset all request settings

    }

    private bool $has_closed = false;

    /**
     * Reset all settings and close the cURL handle.
     *
     * NOTE: This method is called in the class destructor.
     *
     * @return self
     */
    public function close(): self
    {

        if (false === $this->has_closed) {

            $this->reset(); // Reset all request settings

            // Reset all response settings

            $this->responses = [];

            // Close the handles

            curl_close($this->handles[self::DEFAULT_HANDLE]);

            $this->handles = [];

            $this->has_closed = true;

        }

        return $this;

    }

    /*
     * ############################################################
     * Request
     *
     * NOTE:
     * All these requests must be passed to the parent ClientClass
     * then are executed immediately
     * ############################################################
     */

    public function get(string $url, array $data = []): ClientParent
    {
        parent::get($url, $data);
        $this->execute();
        return $this;
    }

    public function connect(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::connect($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function delete(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::delete($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function head(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::head($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function options(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::options($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function patch(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::patch($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function post(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::post($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function put(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::put($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    public function trace(string $url, array $data = [], bool $json_encode = false): ClientParent
    {
        parent::trace($url, $data, $json_encode);
        $this->execute();
        return $this;
    }

    /**
     * Initiates file download in the browser.
     *
     * @param string $url
     * @param int $memory_limit (In MB)
     * @return void
     */
    public function download(string $url, int $memory_limit = 128): void
    {

        ini_set('memory_limit', $memory_limit . 'M');

        $url = $this->getRequestUrl($url);

        $this->setOptions([
            CURLOPT_URL => $url
        ]);

        $this->requests[$this->current_handle]['url'] = $url;

        $this->execute();

        header('Content-Disposition: attachment; filename=' . basename($url));
        header('Content-Type: ' . MimeType::fromFile(basename($url)));
        header('Content-Length: ' . $this->getHeader('Content-Length'));

        echo $this->getBody();

    }

}