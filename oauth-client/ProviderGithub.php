<?php

class ProviderGithub extends SystemeProvider
{
    protected $data = [
        "name" => "Github",
        "redirect_uri" => "http://localhost:7071"
    ];

    protected $clientId;
    protected $clientSecret;
    protected $uri = "https://api.github.com";
    protected $accessLink = "https://github.com/login/oauth/authorize";
    protected $uriAuth = "https://github.com/login/oauth/access_token";

    public function __construct(string $client_id, string $client_secret)
    {

        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
    }

    public function home($accesLink)
    {
        $link = $accesLink;

        echo "<a href=\"{$link}\">Se connecter via Github</a>";
    }

    public function getData()
    {
        return $this->callback('/user');
    }

    
}