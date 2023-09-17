<?php
session_start();
require_once './bootstrap.php';
use App\Database\Database;
use Salla\OAuth2\Client\Provider\Salla;

if (checkIfTokenExists()) {

    if (checkIfTokenIsValid()) {
        $userObj = getUserDataFromDb();
        $_SESSION['token'] = $userObj->token;
        $_SESSION['refresh_token'] = $userObj->refresh_token;
        $_SESSION['name'] = $userObj->name;

        header('Location: dashboard.php');
        exit;
    } else {
        $token = getRefreshToken();
        setSession($token->getToken());
    }

} else {
    getNewToken();
}

function checkIfTokenExists()
{
    $userObj = getUserDataFromDb();

    if (gettype($userObj) == 'Object' && $userObj->token) {
        return true;
    }
}

function checkIfTokenIsValid()
{
    $today = date('Y-m-d');
    $userObj = getUserDataFromDb();
    if ($userObj && $userObj->expire_date >= $today) {
        return true;
    }
}

function getRefreshToken()
{
    $userObj = getUserDataFromDb();
    $refresh_token = $userObj->refresh_token;
    $token = refreshToken($refresh_token);
    updateToken($token, $refresh_token);

    return $token;
}

function setSession($token)
{
    $_SESSION['token'] = $token;
    header('Location: dashboard.php');
    exit;
}

function updateToken($token, $refresh_token)
{
    $db = new Database();
    $db->connect();
    $sql = "UPDATE users SET token = ':token' WHERE 'refresh_token' = ':refresh_token'";
    $params = array(':token' => $token, ':refresh_token' => $refresh_token);
    $db->query($sql, $params);
}

function refreshToken(string $refresh_token)
{
    $provider = new Salla([
        'clientId' => $_ENV['SALLA_CLIENT_ID'],
        'clientSecret' => $_ENV['SALLA_SECRET'],
    ]);

    $token = $provider->getAccessToken('refresh_token', ['refresh_token' => $refresh_token]);
    return $token;
    // echo $token;

    // $client = new Client();
    // $post = [
    //     'client_id' => $_ENV['SALLA_CLIENT_ID'],
    //     'client_secret' => $_ENV['SALLA_SECRET'],
    //     'grant_type' => 'refresh_token',
    //     'refresh_token' => $refresh_token,
    //     'redirect_uri' => $_ENV['SALLA_REDIRECT_URI'],
    // ];

    // $response = $client->request('POST', 'https://accounts.salla.sa/oauth2/token', [
    //     'headers' => ['Content-Type' => 'application/json'],
    //     'body' => json_encode($post),
    // ]);

    // echo $response->getBody();
    // $url = "https://accounts.salla.sa/oauth2/token";

    // $header = [
    //     'Content-Type: application/json',
    // ];

    // $post = [
    //     'client_id' => $_ENV['SALLA_CLIENT_ID'],
    //     'client_secret' => $_ENV['SALLA_SECRET'],
    //     'grant_type' => 'refresh_token',
    //     'refresh_token' => $refresh_token,
    //     'redirect_uri' => $_ENV['SALLA_REDIRECT_URI'],
    // ];

    // // Initialize a CURL session.
    // $newCurl = curl_init();

    // curl_setopt($newCurl, CURLOPT_HTTPHEADER, $header);
    // curl_setopt($newCurl, CURLOPT_URL, $url);
    // curl_setopt($newCurl, CURLOPT_POST, 1);
    // curl_setopt($newCurl, CURLOPT_POSTFIELDS, http_build_query($post));
    // curl_setopt($newCurl, CURLOPT_RETURNTRANSFER, true);

    // $output = curl_exec($newCurl);
    // $json = json_decode($output);
    // var_dump($json);
    // curl_close ($newCurl);

    // var_dump($_ENV['SALLA_CLIENT_ID'], $_ENV['SALLA_SECRET'], $_ENV['SALLA_REDIRECT_URI']);exit;
    // $curl = curl_init();

    // curl_setopt_array($curl, [
    //     CURLOPT_URL => "https://accounts.salla.sa/oauth2/token",
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => "",
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 30,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => "POST",
    //     CURLOPT_POSTFIELDS => json_encode([
    //        $post
    //     ]),
    //     CURLOPT_HTTPHEADER => [
    //         "Content-Type: application/json",
    //     ],
    // ]);

    // $response = curl_exec($curl);
    // $err = curl_error($curl);

    // curl_close($curl);

    // if ($err) {
    //     echo "cURL Error #:" . $err;
    // } else {
    //     echo $response;
    // }
}

function getNewToken()
{
    $provider = new Salla([
        'clientId' => $_ENV['SALLA_CLIENT_ID'], // The client ID assigned to you by Salla
        'clientSecret' => $_ENV['SALLA_SECRET'], // The client password assigned to you by Salla
        'redirectUri' => $_ENV['SALLA_REDIRECT_URI'], // the url for current page in your service
    ]);

    if (empty($_GET['code'])) {
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => 'offline_access',
            //Important: If you want to generate the refresh token, set this value as offline_access
        ]);

        header('Location: ' . $authUrl);
        exit;
    }

    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    $refresh_token = $token->getRefreshToken();

    $expire_date = date('Y-m-d H:i:s', $token->getExpires());
    $user = $provider->getResourceOwner($token);

    $data = [
        'name' => $user->getName(),
        'email' => $user->getEmail(),
        'mobile' => $user->getMobile(),
        'token' => $token,
        'refresh_token' => $refresh_token,
        'expire_date' => $expire_date,
        'store_name' => $user->getStoreName()
    ];

    saveUserResource($data);

    setSession($token->getToken());
}

function saveUserResource($data)
{
    $db = new Database();
    $db->connect();
    $sql = "INSERT INTO users(name,email,mobile,token,refresh_token,expire_date,store_name) VALUES (:name,:email,:mobile,:token,:refresh_token,:expire_date,:store_name)";
    $params = [
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':mobile' => $data['mobile'],
        ':token' => $data['token'],
        ':refresh_token' => $data['refresh_token'],
        ':expire_date' => $data['expire_date'],
        ':store_name' => $data['store_name']
    ];
    $db->query($sql, $params);
}

function getUserDataFromDb()
{
    $db = new Database();
    $db->connect();
    $sql = "SELECT * FROM users";
    return $db->fetch($sql);
}
