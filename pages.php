<?php
// pages.php
session_start();
if (!isset($_SESSION['fb_access_token'])) {
    header("Location: index.php");
    exit;
}

$accessToken = $_SESSION['fb_access_token'];
$pagesUrl = "https://graph.facebook.com/v20.0/me/accounts?" . http_build_query([
    'access_token' => $accessToken,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pagesUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$pagesData = json_decode($response, true);

if (!isset($pagesData['data'])) {
    echo "Error retrieving pages: " . htmlspecialchars($response);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Select a Facebook Page</title>
  <script>
    // Function to validate that the date range does not exceed 93 days
    function validateDateRange() {
      const sinceInput = document.getElementById("since");
      const untilInput = document.getElementById("until");
      
      // Get date values from the inputs
      const sinceDate = new Date(sinceInput.value);
      const untilDate = new Date(untilInput.value);
      
      // Check if both dates are valid
      if (!sinceInput.value || !untilInput.value) {
        alert("Please select both dates.");
        return false;
      }
      
      // Calculate the difference in time (milliseconds)
      const diffTime = untilDate - sinceDate;
      // Convert to days
      const diffDays = diffTime / (1000 * 60 * 60 * 24);
      
      if (diffDays > 93) {
        alert("The date range cannot exceed 93 days. Please choose a shorter range.");
        return false;
      }
      
      return true;
    }
    
    // Attach form onsubmit event to validate date range before submission
    window.addEventListener("load", function() {
      const form = document.getElementById("pagesForm");
      form.addEventListener("submit", function(event) {
        if (!validateDateRange()) {
          event.preventDefault();
        }
      });
    });
  </script>
</head>
<body>
  <h1>Select a Facebook Page</h1>
  <!-- The form sends a JSON-encoded page object (ID and Page Access Token) along with date range -->
  <form id="pagesForm" action="get-insights.php" method="GET">
    <select name="page_data" required>
      <option value="">--Select a Page--</option>
      <?php foreach ($pagesData['data'] as $page): ?>
        <?php
          // Encode the page ID and its access token into JSON
          $pageInfo = json_encode([
            'id' => $page['id'],
            'access_token' => $page['access_token']
          ]);
        ?>
        <option value='<?php echo htmlspecialchars($pageInfo); ?>'>
          <?php echo htmlspecialchars($page['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <br><br>
    <label for="since">Since (YYYY-MM-DD): </label>
    <input type="date" name="since" id="since" required>
    <br><br>
    <label for="until">Until (YYYY-MM-DD): </label>
    <input type="date" name="until" id="until" required>
    <br><br>
    <button type="submit">Get Insights</button>
  </form>
</body>
</html>
