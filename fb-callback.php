<?php
// fb-callback.php
// This file processes the OAuth callback from Facebook.
// It verifies the state parameter, exchanges the authorization code for an access token,
// and then fetches the user’s profile information (id, name, picture).

session_start();

// Set the content type (we will output HTML below)
header("Content-Type: text/html; charset=UTF-8");

// Load configuration (you can also use dotenv if preferred)
// For this example, we'll define them directly.
$appId       = "570519095985039";
$appSecret   = "64231bf9464754a8f73063f798c4358b";
$redirectUri = "https://fad7-2405-201-403a-40c6-7411-6e8-4dea-ff9.ngrok-free.app/fb-callback.php"; // Must exactly match your Facebook app settings

// Ensure required GET parameters are present
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    echo "Invalid response from Facebook.";
    exit;
}

// Verify the state parameter to protect against CSRF
if (!isset($_SESSION['fb_state']) || $_GET['state'] !== $_SESSION['fb_state']) {
    echo "State parameter mismatch.";
    exit;
}

$code = $_GET['code'];

// Step 1: Exchange the authorization code for an access token

$tokenUrl = "https://graph.facebook.com/v20.0/oauth/access_token?" . http_build_query([
    'client_id'     => $appId,
    'redirect_uri'  => $redirectUri,
    'client_secret' => $appSecret,
    'code'          => $code,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    echo "Error retrieving access token: " . htmlspecialchars($tokenResponse);
    exit;
}

$accessToken = $tokenData['access_token'];
// Save the user access token in the session (later used to retrieve managed pages)
$_SESSION['fb_access_token'] = $accessToken;

// Step 2: Use the access token to fetch the user’s profile information
$userUrl = "https://graph.facebook.com/v20.0/me?" . http_build_query([
    'fields'       => 'id,name,picture',
    'access_token' => $accessToken,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userResponse = curl_exec($ch);
curl_close($ch);

$userData = json_decode($userResponse, true);
if (!isset($userData['id'])) {
    echo "Error retrieving user data: " . htmlspecialchars($userResponse);
    exit;
}

// (Optional) Save the user data in the session for further use
$_SESSION['fb_user'] = $userData;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Facebook Callback</title>
</head>
<body>
  <h1>Logged in as <?php echo htmlspecialchars($userData['name']); ?></h1>
  <img src="<?php echo htmlspecialchars($userData['picture']['data']['url']); ?>" alt="Profile Picture">
  <br><br>
  <!-- Provide a link to the next step (pages.php) -->
  <a href="pages.php">View Managed Pages</a>
</body>
</html>
