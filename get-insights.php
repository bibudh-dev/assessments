<?php
// get-insights.php
session_start();
if (!isset($_SESSION['fb_access_token'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['page_data']) || !isset($_GET['since']) || !isset($_GET['until'])) {
    echo "Missing required parameters.";
    exit;
}

$pageData = json_decode($_GET['page_data'], true);
if (!$pageData || !isset($pageData['id']) || !isset($pageData['access_token'])) {
    echo "Invalid page data.";
    exit;
}

$pageId = $pageData['id'];
$pageAccessToken = $pageData['access_token'];
$since = $_GET['since'];
$until = $_GET['until'];

// Define the metrics to fetch
$metrics = [
    'page_follows',                       // Total Followers / Fans
    'page_post_engagements',                 // Total Engagement (use this as an alternative to page_engaged_users)
    'page_impressions',                // Total Impressions
    'page_actions_post_reactions_like_total'// Total Reactions
];

$insights = [];

foreach ($metrics as $metric) {
    // For demonstration, we assume "page_engagement" and others support total_over_range.
    // Adjust the period as needed (e.g., 'lifetime' if required by the metric).

        $period = 'total_over_range';

    $insightsUrl = "https://graph.facebook.com/v20.0/{$pageId}/insights?" . http_build_query([
        'metric'       => $metric,
        'since'        => $since,
        'until'        => $until,
        'period'       => $period,
        'access_token' => $pageAccessToken
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $insightsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    $insights[$metric] = $decoded;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Page Insights</title>
  <style>
    .card {
      border: 1px solid #ccc;
      padding: 15px;
      margin: 10px;
      display: inline-block;
      width: 220px;
      vertical-align: top;
    }
  </style>
</head>
<body>
  <h1>Page Insights</h1>
  <?php foreach ($insights as $metric => $data): ?>
    <div class="card">
      <h3><?php echo ucfirst(str_replace('_', ' ', $metric)); ?></h3>
      <?php if (isset($data['data'][0]['values'][0]['value'])): ?>
        <p>
          <?php
            $value = $data['data'][0]['values'][0]['value'];
            // Check if the value is a string; if not, convert it to a JSON string.
            echo is_string($value) ? htmlspecialchars($value) : htmlspecialchars(json_encode($value));
          ?>
        </p>
      <?php else: ?>
        <p>
          <?php
            if (isset($data['error'])) {
              $errorVal = $data['error'];
              // If error is an array, encode it to string; otherwise, display as is.
              echo is_string($errorVal) ? htmlspecialchars($errorVal) : htmlspecialchars(json_encode($errorVal));
            } else {
              echo "No data available";
            }
          ?>
        </p>
      <?php endif; ?>
    </div>
<?php endforeach; ?>

  <br><br>
  <a href="pages.php">Back to Pages</a>
</body>
</html>
