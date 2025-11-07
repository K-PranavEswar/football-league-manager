<?php
include('db.php');

// 1. Check if the regular season is actually finished
$pending_matches_check = $conn->query("SELECT id FROM matches WHERE match_played = 0 AND round_number < 100");
if ($pending_matches_check->num_rows > 0) {
    // Not finished, redirect back with an error
    $message = urlencode("You cannot generate playoffs until all league matches are played!");
    header("Location: index.php?message={$message}&type=danger");
    exit;
}

// 2. Check if playoffs have already been generated
$playoff_check = $conn->query("SELECT id FROM matches WHERE round_number = 100");
if ($playoff_check->num_rows > 0) {
    $message = urlencode("Playoffs have already been generated.");
    header("Location: index.php?message={$message}&type=warning");
    exit;
}

// 3. Get the Top 4 teams from the 'teams' table
$top_4_query = $conn->query("SELECT id FROM teams ORDER BY points DESC, gd DESC, goals_for DESC, name ASC LIMIT 4");

if ($top_4_query->num_rows < 4) {
    $message = urlencode("You need at least 4 teams in the league to generate playoffs.");
    header("Location: index.php?message={$message}&type=danger");
    exit;
}

$top_4_ids = [];
while ($row = $top_4_query->fetch_assoc()) {
    $top_4_ids[] = $row['id'];
}

// We have our top 4. 
// $top_4_ids[0] = 1st Place
// $top_4_ids[1] = 2nd Place
// $top_4_ids[2] = 3rd Place
// $top_4_ids[3] = 4th Place

// 4. Create the Semi-Final matches (1st vs 4th, 2nd vs 3rd)
// We will use Round 100 for Semi-Finals
$sf1_stmt = $conn->prepare("INSERT INTO matches (home_team_id, away_team_id, round_number) VALUES (?, ?, 100)");
$sf1_stmt->bind_param("ii", $top_4_ids[0], $top_4_ids[3]); // 1st vs 4th
$sf1_stmt->execute();

$sf2_stmt = $conn->prepare("INSERT INTO matches (home_team_id, away_team_id, round_number) VALUES (?, ?, 100)");
$sf2_stmt->bind_param("ii", $top_4_ids[1], $top_4_ids[2]); // 2nd vs 3rd
$sf2_stmt->execute();

// 5. Redirect back with success
$message = urlencode("Top 4 playoffs (Semi-Finals) have been generated!");
header("Location: index.php?message={$message}&type=success");
exit;
?>