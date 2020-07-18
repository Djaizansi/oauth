<?php

class OauthClient extends SystemeProvider {

    protected $client_id = "client_5edfd43b0db573.88203718";
    protected $client_secret = "e0a6a1f5c55fafd48cbcce2b7279d4029fad76f4";
    protected $state = "DEAZFAEF321432DAEAFD3E13223R";
    protected $local_url = "http://localhost:7071";

    public function home($client_id,$state,$local_url)
    {
        $link = "http://localhost:7070/auth?response_type=code&client_id={$client_id}&state={$state}&scope=email&redirect_uri={$local_url}/success";

        echo "<a href=\"{$link}\">Se connecter via OauthServer</a>";
    }

    public function callback($client_id,$client_secret,$state)
    {

        ['code' => $code, 'state' => $rstate] = $_GET;

        if ($state === $rstate) {
            $link = "http://oauth-server/token?grant_type=authorization_code&code={$code}&client_id={$client_id}&client_secret={$client_secret}";
            ['token' => $token] = json_decode(file_get_contents($link), true);

            $link = "http://oauth-server/me";
            $rs = curl_init($link);
            curl_setopt_array($rs, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}"
                ]
            ]);
            echo curl_exec($rs);
            curl_close($rs);
        } else {
            http_response_code(400);
            echo "Invalid state";
        }
    }
}