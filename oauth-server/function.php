<?php

class Fonction {

/**
 * @param string $filename
 * @return array
 */
    public function read_file(string $filename): array
    {
        if (!file_exists($filename)) throw new \Exception($filename . " not found");
        $data = file($filename);
        return array_map(fn ($item) => unserialize($item), $data);
    }

    /**
     * @param array $data
     * @param string $filename
     * @return int
     */
    public function write_file(array $data, string $filename): int
    {
        $data = array_map(fn ($item) => serialize($item), $data);
        return file_put_contents($filename, implode(PHP_EOL, $data));
    }

    /**
     * @param string $uri
     * @return false|array
     */
    public function getApp(string $uri)
    {
        $data = read_file('./data/app.data');
        foreach ($data as $app) {
            if ($app['uri'] === $uri) return $app;
        }
        return false;
    }

    /**
     * @param string $client_id
     * @return false|array
     */
    public function getClientId(string $client_id)
    {
        $data = read_file('./data/app.data');
        foreach ($data as $app) {
            if ($app['client_id'] === $client_id) return $app;
        }
        return false;
    }

    /**
     * @param string $client_id
     * @param string $code
     * @return false|array
     */
    public function getCode(string $client_id, string $code)
    {
        $data = read_file('./data/code.data');
        foreach ($data as $app) {
            if ($app['code'] === $code && $app['client_id'] === $client_id) return $app;
        }
        return false;
    }

    /**
     * @param string $token
     * @return false|array
     */
    public function getToken(string $token)
    {
        $data = read_file('./data/token.data');
        foreach ($data as $app) {
            if ($app['token'] === $token) return $app;
        }
        return false;
    }

}