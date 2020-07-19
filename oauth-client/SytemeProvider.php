<?php

class SystemeProvider
{


    private function getToken(): ?string
    {
        [
            "code" => $code,
            "state" => $state,
        ] = $_GET;

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => "{$this->data['redirect_uri']}",
            'client_secret' => $this->clientSecret,
            'code' => $code,
            "grant_type" => "authorization_code"
        ];

        if (in_array($this->data["name"], ["Github"])) {
            $result = $this->callback($this->uriAuth, $params);
            $string = explode("&", $result, 2)[0];
            $access_token = explode("=", $string)[1];
        } else {
            $surl = "{$this->uriAuth}?" . http_build_query($params);
            $result = json_decode(file_get_contents($surl), true);
            ['access_token' => $access_token] = $result;
        }

        if (in_array($this->data["name"], ["Linkedin"])) {
            $result = $this->callback($this->uriAuth, $params);
            $string = explode("&", $result, 2)[0];
            $access_token = explode("=", $string)[1];
        } else {
            $surl = "{$this->uriAuth}?" . http_build_query($params);
            $result = json_decode(file_get_contents($surl), true);
            ['access_token' => $access_token] = $result;
        }

        return $access_token;
    }

    public function callback(string $path, array $body = [])
    {
        if (empty($body)) {
            $token = $this->getToken();
            if (is_null($token)) return null;
            $sapi = $this->uri . $path;
        } else {
            $sapi = $this->uriAuth;
        }

        // Get userdata with access_token

        $rs = curl_init($sapi);
        curl_setopt_array($rs, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}"
            ]
        ]);

        $result = curl_exec($rs);
        curl_close($rs);
        return $result;
    }
}
