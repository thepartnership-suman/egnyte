<?php

namespace Suman\Egnyte;


use GuzzleHttp\Psr7\Response;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Suman\Egnyte\Model\File as EgnyteClient;

class EgnyteAdapter extends AbstractAdapter
{
    /**
     * @var
     */
    protected $client;

    /**
     * @param EgnyteClient $client
     * @param string $prefix
     */
    public function __construct(EgnyteClient $client, string $prefix = '')
    {
        $this->client = $client;
        $this->setPathPrefix($prefix);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://developers.egnyte.com/docs/read/File_System_Management_API_Documentation#Upload-a-File
     */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents);
    }

    /**
     * {@inheritdoc}
     *
     * @https://developers.egnyte.com/docs/read/File_System_Management_API_Documentation#Chunked-Upload
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->uploadChunked($path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return $this->upload($path, $contents);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->uploadChunked($path, $resource);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://developers.egnyte.com/docs/read/File_System_Management_API_Documentation#Move-File-or-Folder
     */
    public function rename($path, $newPath): bool
    {

        $path    = $this->applyPathPrefix($path);
        $newPath = $this->applyPathPrefix($newPath);

        $res = $this->client->move($path, $newPath);

        return $this->isSuccess($res);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://developers.egnyte.com/docs/read/File_System_Management_API_Documentation#Copy-File-or-Folder
     */
    public function copy($path, $newPath)
    {
        $path    = $this->applyPathPrefix($path);
        $newPath = $this->applyPathPrefix($newPath);

        $res = $this->client->copy($path, $newPath);

        return $this->isSuccess($res);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://developers.egnyte.com/docs/read/File_System_Management_API_Documentation#Delete-a-File-or-Folder
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);
        $res      = $this->client->delete($location);

        return $this->isSuccess($res);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return $this->delete($dirname);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://developers.egnyte.com/docs/read/File_System_Management_API_Documentation#Create-a-Folder
     */
    public function createDir($dirname, Config $config): bool
    {
        $path = $this->applyPathPrefix($dirname);
        $res  = $this->client->createFolder($path);
        return $this->isSuccess($res, 201);
    }

    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    /**
     * {@inheritdoc}
     */
    public function has($path): bool
    {
        try{
            return $this->isSuccess($this->getMetadata($path));
        } catch (\Exception $e){
            return false;
        }

    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (!$object = $this->readStream($path)) {
            return false;
        }

        $object['contents'] = $object['stream'];
        unset($object['stream']);
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $path = $this->applyPathPrefix($path);
        try {

            $stream = $this->client->download($path);

        } catch (\Exception $e) {
            return false;
        }
        return compact('stream');
    }

    public function listContents($directory = '', $recursive = false): array
    {
        $path   = $this->applyPathPrefix($directory);
        $result = $this->client->listFolder($path, $recursive);
        $result = json_decode($result->getBody(), true);

        return array_merge(
            array_map([$this, 'removePathPrefixForFile'], $result['folders']),
            array_map([$this, 'removePathPrefixForFile'], $result['files'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $path = $this->applyPathPrefix($path);

        return $this->client->getMetadata($path);

    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        //return $this->getMetadata($path);
        throw new \LogicException(get_class($this) . ' does not support mimetype. Path: ' . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function applyPathPrefix($path): string
    {
        $path = parent::applyPathPrefix($path);
        return '/' . trim($path, '/');
    }

    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }


    /**
     * @param string $path
     * @param resource|string $contents
     * @param string $mode
     *
     * @return array|false file metadata
     */
    protected function upload(string $path, $contents) : bool
    {
        $path = $this->applyPathPrefix($path);

        $response = $this->client->upload($path, $contents);

        return $this->isSuccess($response);

    }

    /**
     * @param string $path
     * @param resource|string $contents
     * @param string $mode
     *
     * @return array|false file metadata
     */
    protected function uploadChunked(string $path, $contents) : bool
    {
        $path = $this->applyPathPrefix($path);

        $response = $this->client->uploadChunked($path, $contents);

        return $this->isSuccess($response);
    }


    public function isSuccess(Response $response, int $code = 200): bool
    {
        return $response->getStatusCode() === $code;
    }

}