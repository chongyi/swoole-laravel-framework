<?php
/**
 * Request.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/07 14:50
 */

namespace Swoole\Laravel\Http;

use Illuminate\Http\Request as LaravelRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Swoole\Http\Request as SwooleRequest;

/**
 * Class Request
 *
 * @package Swoole\Laravel\Http
 */
class Request extends LaravelRequest
{
    /**
     * @var
     */
    protected $convertedFiles;

    /**
     * Create a new Illuminate HTTP request from server variables.
     */
    public static function capture()
    {
        static::enableHttpMethodParameterOverride();

        return static::createFromBase(SymfonyRequest::createFromGlobals());
    }

    /**
     * @param SwooleRequest $request
     *
     * @return LaravelRequest
     */
    public static function captureViaSwooleRequest(SwooleRequest $request)
    {
        static::enableHttpMethodParameterOverride();

        $get     = isset($request->get) ? $request->get : [];
        $post    = isset($request->post) ? $request->post : [];
        $cookies = isset($request->cookie) ? $request->cookie : [];
        $server  = isset($request->server) ? static::keyUpper($request->server) : [];
        $files   = isset($request->files) ? $request->files : [];

        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $server)) {
                $server['CONTENT_LENGTH'] = $server['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $server)) {
                $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
            }
        }

        $symfonyRequest = new SymfonyRequest($get, $post, [], $cookies, $files, $server);

        if (0 === strpos($symfonyRequest->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            &&
            in_array(strtoupper($symfonyRequest->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($symfonyRequest->getContent(), $data);
            $symfonyRequest->request = new ParameterBag($data);
        }

        return static::createFromBase($symfonyRequest);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected static function keyUpper(array $array)
    {
        $keys = [];
        foreach (array_keys($array) as $key) {
            $keys[] = strtoupper($key);
        }

        $values = [];
        foreach (array_values($array) as $value) {
            $values[] = $value;
        }

        return array_combine($keys, $values);
    }

    /**
     * Retrieve a file from the request.
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|UploadedFile|array|null
     */
    public function file($key = null, $default = null)
    {
        return data_get($this->allFiles(), $key, $default);
    }


    /**
     * Get an array of all of the files on the request.
     *
     * @return array
     */
    public function allFiles()
    {
        $files = $this->files->all();

        return $this->convertedFiles
            ? $this->convertedFiles
            : $this->convertedFiles = $this->convertUploadedFiles($files);
    }

    /**
     * Convert the given array of Symfony UploadedFiles to custom Laravel UploadedFiles.
     *
     * @param  array $files
     *
     * @return array
     */
    protected function convertUploadedFiles(array $files)
    {
        return array_map(function ($file) {
            if (is_null($file) || (is_array($file) && empty(array_filter($file)))) {
                return $file;
            }

            return is_array($file)
                ? $this->convertUploadedFiles($file)
                : UploadedFile::createFromBase($file);
        }, $files);
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->input(), $this->allFiles());
    }


}