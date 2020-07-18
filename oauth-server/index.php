<?php


function register()
{
    $function = new Fonction();
    // Get client application data
    [
        "name" => $name, "uri" => $uri,
        'redirect_success' => $redirect_success, 'redirect_error' => $redirect_error
    ] = $_POST;

    // Check if an app is already registered with this URI
    if (getApp($uri)) {
        http_response_code(400);
        echo 'URI already registered';
    } else {
        // Generate a client_id/client_secret
        $clientId = uniqid('client_', true);

        // Insert app data with the newly created credentials in the database
        $data = $function->read_file('./data/app.data');
        $data[] = [
            "name" => $name, "uri" => $uri,
            'redirect_success' => $redirect_success, 'redirect_error' => $redirect_error,
            'client_id' => $clientId, 'client_secret' => sha1($clientId)
        ];
        $function->write_file($data, './data/app.data');

        // Return a well-formated response to the client with the newly created credentials
        http_response_code(201);
        header('Content-Type: application/json');
        echo json_encode([
            'client_id' => $clientId, 'client_secret' => sha1($clientId)
        ]);
    }
}

// Partie auth
/**
 * https://auth-server/auth?
 *    response_type=code&client_id=..
 *    &scope=...&state=...&redirect_uri=...
 * 
 * 1) --récupérer les données de la requête--
 * 2) --vérifier si le client_id existe en base--
 * 3.1) --Si non, revoyer un 404--
 * 3.2) --Si oui, afficher la page d'autorisation
 *    (nom de l'app, url de l'app, bouton cancel, bouton approve)
 *     approve: /auth-success
 *     cancel: /auth-cancel--
 * 4) Si approuvé, générer un code avec une expiration de 5sec, le sauvegarder en base 
 *    et rediriger vers l'URL de succès avec le code et le state
 */
function auth()
{
    $function = new Fonction();
    [
        "response_type" => $code, "client_id" => $client_id,
        "scope" => $scope, "state" => $state,
        "redirect_uri" => $redirect_uri
    ] = $_GET;
    if (false !== ($app = $function->getClientID($client_id))) {
        http_response_code(200);
        echo "" . $app["name"] . "</br>";
        echo "" . $app["uri"] . "</br>";
        echo "<a href='/auth-success?client_id=${client_id}&state=${state}'>Approve</a></br>";
        echo "<a href='/auth-cancel'>Cancel</a></br>";
    } else {
        http_response_code(404);
    }
}

function authSuccess()
{
    $function = new Fonction();
    [
        "client_id" => $client_id,
        "state" => $state,
    ] = $_GET;
    // Generate code and its expiration Date
    $code = uniqid();
    $expireDate = new DateTime('+ 15 seconds');
    // Save them into the database
    $data = $function->read_file('./data/code.data');
    $data[] =
        [
            "client_id" => $client_id,
            "code" => $code,
            "expireDate" => $expireDate
        ];
        $function->write_file($data, './data/code.data');
    // Redirect to client success route
    $app = $function->getClientID($client_id);
    header("Location: {$app['redirect_success']}?state=$state&code=$code");
}

function exchangeAuthorizationCodeToToken(string $client_id, $code)
{
    $function = new Fonction();
    // Check if code exist and not expired
    $code = $function->getCode($client_id, $code);
    if (false !== $code && $code['expireDate'] > new DateTime()) {
        // Generate token and its expiration Date
        $token = uniqid("", true);
        $expireDate = new DateTime('+ 3600 seconds');
        // Save them into the database
        $data = $function->read_file('./data/token.data');
        $data[] =
            [
                "token" => $token,
                'expireDate' => $expireDate,
                'user_id' => uniqid()
            ];
        $file = './data/token.data';
        $function->write_file($data, $file);
        // Send token and expirationDate as a json response
        http_response_code(201);
        echo json_encode([
            'token' => $token, 'expireDate' => $expireDate
        ]);
    } else {
        http_response_code(400);
        echo !$code ? "Code not found" : "Code expired";
    }
}

function exchangePasswordToToken(string $username, string $client_id, $password)
{ 
    $function = new Fonction();
    // Mais du coup on récupère le password par où? C'est via un fichier? 
    // Check if code exist and not expired
    if ($username === "user" && $password === "password") {
        // Generate token and its expiration Date
        $token = uniqid("", true);
        $expireDate = new DateTime('+ 3600 seconds');
        // Save them into the database
        $data = $function->read_file('./data/token.data');
        $data[] =
            [
                "token" => $token,
                'expireDate' => $expireDate,
                'user_id' => uniqid()
            ];
        $file = './data/token.data';
        $function->write_file($data, $file);
        // Send token and expirationDate as a json response
        http_response_code(201);
        echo json_encode([
            'token' => $token, 'expireDate' => $expireDate
        ]);
    } else {
        http_response_code(400);
        echo "Invalid credentials";
    }
}


function exchangeClientCredentialsToToken(string $client_secret, string $client_id)
{
    $function = new Fonction();
    // Generate token and its expiration Date
    $token = uniqid("", true);
    $expireDate = new DateTime('+ 3600 seconds');
    // Save them into the database
    $data = $function->read_file('./data/token_client.data');
    $data[] =
        [
            "token" => $token,
            'expireDate' => $expireDate,
            'client_id' => $client_id
        ];
    $file = './data/token_client.data';
    $function->write_file($data, $file);
    // Send token and expirationDate as a json response
    http_response_code(201);
    echo json_encode([
        'token' => $token, 'expireDate' => $expireDate
    ]);
}


// Partie auth
/**
 * https://auth-server/token?
 *    grant_type=authorization_code
 *    &client_id=..&client_secret=...
 *    &code=...&redirect_uri=...
 * 
 * 1) --récupérer les données de la requête--
 * 2) --vérifier si le client_id et client_secret existent en base--
 * 3.1) --Si non, revoyer un 404--
 * 3.2) --Si oui, vérifier que le code est correct (existe et non expiré)--
 * 4) Si ok, générer un access_token et son expiration, sauvegarder en base,
 *    renvoyer sous format JSON l'access_token et sa date d'expiration
 */
function token()
{
    $function = new Fonction();
    // Get request params
    [
        "grant_type" => $grant_type,
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "redirect_uri" => $redirect_uri
    ] = $_GET;
    // Check if app exist and secret is valid
    if (false !== ($app = getClientId($client_id)) && $app['client_secret'] == $client_secret) {
        switch ($grant_type) {
            case "authorization_code":
                ["code" => $code] = $_GET;
                exchangeAuthorizationCodeToToken($client_id, $code);
                break;
            case "password":
                [
                    "username" => $username,
                    "password" => $password
                ] = $_GET;
                exchangePasswordToToken($username, $client_id, $password);
                break;
            case "client_credentials":
                exchangeClientCredentialsToToken($client_secret, $client_id);
                break;
        }
    } else {
        http_response_code(404);
        echo "App not found";
    }
}

function me()
{
    $function = new Fonction();
    ['Authorization' => $auth] = getallheaders();
    $token = str_replace('Bearer ', '', $auth);
    if (false !== ($token = getToken($token))) {
        echo json_encode(['user_id' => $token['user_id'], "email" => 'test@test.com']);
    } else {
        http_response_code(401);
        echo "Token not found";
    }
}