<?php
include('db.php');

// Ensure league is selected
if (!isset($_SESSION['league_name']) || empty($_SESSION['league_name'])) {
    header("Location: home.php?message=" . urlencode("No league selected to reset.") . "&type=warning");
    exit;
}

$league_name = $_SESSION['league_name'];

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Delete all matches linked to this league
$stmt1 = $conn->prepare("DELETE FROM matches WHERE league_name = ?");
$stmt1->bind_param("s", $league_name);
$stmt1->execute();

// Delete all teams linked to this league
$stmt2 = $conn->prepare("DELETE FROM teams WHERE league_name = ?");
$stmt2->bind_param("s", $league_name);
$stmt2->execute();

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Redirect with confirmation message
header("Location: index.php?message=" . urlencode("League '{$league_name}' has been successfully reset!") . "&type=success");
exit;
?>
