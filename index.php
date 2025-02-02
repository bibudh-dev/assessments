<?php
// index.php
session_start();

// Load configuration (alternatively, you could load from .env here)
// For this example, we define them directly:
$appId       = "570519095985039";
$redirectUri = "https://fad7-2405-201-403a-40c6-7411-6e8-4dea-ff9.ngrok-free.app/fb-callback.php";  // Must match your Facebook App settings
$scope       = "email,pages_show_list,pages_read_engagement,read_insights";

// Generate a random state parameter and store it in session
$state = bin2hex(random_bytes(8));
$_SESSION['fb_state'] = $state;

// Build the Facebook OAuth URL
$loginUrl = "https://www.facebook.com/v20.0/dialog/oauth?"
  . http_build_query([
      'client_id'     => $appId,
      'redirect_uri'  => $redirectUri,
      'state'         => $state,
      'scope'         => $scope,
      'response_type' => 'code'
  ]);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login with Facebook</title>
</head>
<body>
  <h1>Facebook Insights</h1>
  <a href="<?php echo htmlspecialchars($loginUrl); ?>">
    <button>Login with Facebook</button>
  </a>
</body>
</html>
