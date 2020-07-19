<?php

class ProviderLinkedin extends SystemeProvider
{
    protected $data = [
        "name" => "Linkedin",
        "redirect_uri" => "http://localhost:8000",
        "scope" => "r_emailaddress r_liteprofile w_member_social"
    ];
    protected $clientSecret;
    protected $clientId;
    protected $uri = "https://api.linkedin.com/v2";
    protected $accessLink = "https://www.linkedin.com/oauth/v2/authorization";
    protected $uriAuth = "https://www.linkedin.com/oauth/v2/accessToken";

    public function __construct(string $client_id, string $client_secret)
    {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
    }

    public function getUserData()
    {
        return $this->callback("/user");
    }
}
