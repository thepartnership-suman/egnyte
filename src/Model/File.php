<?php

namespace Suman\Egnyte\Model;


use GuzzleHttp\Psr7\Response;
use Suman\Egnyte\Client;

/**
 * ALL the functions to do with Filesystem should go here
 */
class File
{
    /**
     * @var \GuzzleHttp\Client Guzzle client
     */
    private $guzzleClient;

    /**
     * @var string URL of Egnyte endpoint
     */
    private $egnyteURL;

    /**
     * @param string $domain
     * @param string $oauth_token
     */
    public function __construct(string $domain, string $oauth_token)
    {
        $client             = (new Client($domain, $oauth_token));
        $this->guzzleClient = $client->guzzleClient;
        $this->egnyteURL    = $client->base_url;
    }

    /**
     * @param string $path location of the file in Egnyte eg, \Shared\foo\bar
     * @param array $params extra query params
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function getMetadata(string $path, array $params = []): Response
    {

        $path = $this->pathEncode($path);

        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }

        return $this->guzzleClient->get($this->egnyteURL . '/fs/' . $path);
    }

    /**
     * @param string $path
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function createFolder(string $path): Response
    {
        $path = $this->pathEncode($path);

        return $this->guzzleClient->post($this->egnyteURL . '/fs/' . $path, [
            'json' => ['action' => 'add_folder']
        ]);
    }

    /**
     * @param string $remote_path Path the file should be uploaded
     * @param string $file_contents Contents of the file
     * @param string|null $file_name name of the file
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function upload(string $remote_path, string $file_contents, string $file_name = null) : Response
    {

        $path = $remote_path . ($file_name ? "/$file_name" : "");

        $path = $this->pathEncode($path);

        return $this->guzzleClient->post($this->egnyteURL . '/fs-content' . $path, [
            'header' => ['Content-Type' => 'application/octet-stream'],
            'body'   => $file_contents
        ]);
    }

    /**
     * @param string $remote_path Path the file should be uploaded
     * @param string $file_contents Contents of the file
     * @param string|null $file_name name of the file
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function uploadChunked(string $remote_path, string $file_contents, string $file_name = null) : Response
    {
        return $this->upload($remote_path, $file_contents, $file_name);
    }

    /**
     * @param string $path original path of the file
     * @param string $destination new path of the file
     * @param string|null $permissions permissions for Egnyte
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function move(string $path, string $destination, string $permissions = null): Response
    {
        return $this->guzzleClient->post($this->egnyteURL . '/fs' . $this->pathEncode($path), [
            'header' => ['Content-Type' => 'application/json'],
            'json'   => [
                'action'      => 'move',
                'destination' => $destination,
                'permissions' => $permissions,
            ]
        ]);
    }

    /**
     * @param string $path path of the file needs to be deleted
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function delete(string $path) : Response
    {
        return $this->guzzleClient->delete($this->egnyteURL . '/fs' . $this->pathEncode($path));
    }

    /**
     * @param string $path path of the file needs to be copied
     * @param string $destination destination
     * @param string|null $permissions permissions for Egnyte
     * @return Response GuzzleHttp\Psr7\Response
     */
    public function copy(string $path, string $destination, string $permissions = null) : Response
    {
        return $this->guzzleClient->post($this->egnyteURL . '/fs' . $this->pathEncode($path), [
            'header' => ['Content-Type' => 'application/json'],
            'json'   => [
                'action'      => 'copy',
                'destination' => $destination,
                'permissions' => $permissions,
            ]
        ]);
    }

    /**
     * Download file from Egnyte.
     *
     * @param string $path Remote file path
     * @param string $output Local output directory and file name
     * @return
     */
    public function download(string $path, $output = null): string
    {
        // path names are passed in the URL, so they need encoding
        $path = $this->pathEncode($path);

        $response = $this->guzzleClient->get($this->egnyteURL.'/fs-content' . $path);

        if ($response->getStatusCode() == 200) {
            return $response->getBody();
        }
        return '';
    }

    /**
     * List a file/directory
     * @param string $path The full path to the remote file/directory
     * @param false $recursive
     * @return Response
     */
    public function listFolder(string $path, $recursive = false): Response
    {

        $path .= '?'.http_build_query(['list_content' => $recursive ? 'true' : 'false']);

        return $this->guzzleClient->get($this->egnyteURL . '/fs' . $path);
    }

    /**
     * Move function alias.
     */
    public function mv()
    {
        return call_user_func_array('self::move', func_get_args());
    }

    /**
     * Delete function alias.
     */
    public function rm()
    {
        return call_user_func_array('self::delete', func_get_args());
    }

    /**
     * Create directory function alias.
     */
    public function mkdir()
    {
        return call_user_func_array('self::createFolder', func_get_args());
    }


    /**
     * Encodes resource path, so it can be used in URLs.
     *
     * @param string $path Resource path
     *
     * @return string The url encoded path
     */
    public function pathEncode($path) : string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

}