<?php
namespace WapplerSystems\Cleverreach\Tools;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


class Rest
{

    public bool $data = false;
    private ?string $token = null;
    public ?string $url = null;

    public bool $authModeSettings = false;


    public function __construct($url)
    {
        $this->url = rtrim($url, '/');
    }

    public function setToken($token): void
    {
        $this->token = $token;
    }


    public function get(string $path, mixed $data = null, string $mode = "get"): mixed
    {
        if (is_string($data)) {
            if (!$data = json_decode($data)) {
                throw new \Exception("data is string but no JSON");
            }
        }

        $url = sprintf("%s?%s", $this->url . $path, ($data ? http_build_query($data) : ""));

        $curl = curl_init($url);
        $this->setupCurl($curl);

        switch ($mode) {
            case 'delete':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($mode));
                break;
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $headers = curl_getinfo($curl);
        curl_close($curl);

        return $this->returnResult($curl_response, $headers);
    }

    public function delete($path, $data = null): mixed
    {
        return $this->get($path, $data, "delete");
    }

    public function put($path, $data = null)
    {
        return $this->post($path, $data, "put");
    }

    public function post($path, $data, $mode = "post")
    {
        if (is_string($data)) {
            if (!$data = json_decode($data)) {
                throw new \Exception("data is string but no JSON");
            }
        }
        $curl_post_data = $data;

        $curl = curl_init($this->url . $path);
        $this->setupCurl($curl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        switch ($mode) {
            case 'put':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                break;

            default:
                curl_setopt($curl, CURLOPT_POST, true);
                break;
        }


        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($curl_post_data));
        $curl_response = curl_exec($curl);
        $headers = curl_getinfo($curl);
        curl_close($curl);

        return $this->returnResult($curl_response, $headers);

    }


    /**
     */
    private function setupCurl(&$curl)
    {

        $header = [];
        $header['token'] = 'Authorization: Bearer ' . $this->token;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }

    /**
     * returns formated based on given obj settings
     */
    private function returnResult($in, $header): mixed
    {

        if (isset($header["http_code"])) {
            if ($header["http_code"] < 200 || $header["http_code"] >= 300) {
                //error!?
                $message = var_export($in, true);
                if ($tmp = json_decode($in)) {
                    if (isset($tmp->error->message)) {
                        $message = $tmp->error->message;
                    }
                }
                throw new \Exception($message , (int)$header["http_code"]);
            }

        }

        return json_decode($in);
    }

}
