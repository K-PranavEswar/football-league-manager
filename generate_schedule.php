<?php
include('db.php');

// --- Ensure a league is active ---
if (!isset($_SESSION['league_name']) || empty($_SESSION['league_name'])) {
    header("Location: home.php");
    exit;
}

$league_name = $_SESSION['league_name'];

// 1️⃣ Fetch all team IDs for this league
$team_ids = [];
$stmt_teams = $conn->prepare("SELECT id FROM teams WHERE league_name = ?");
$stmt_teams->bind_param("s", $league_name);
$stmt_teams->execute();
$result = $stmt_teams->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $team_ids[] = (int)$row['id'];
    }
} else {
    header("Location: index.php?message=" . urlencode("Add at least 2 teams to generate a schedule.") . "&type=warning");
    exit;
}

if (count($team_ids) < 2) {
    header("Location: index.php?message=" . urlencode("Add at least 2 teams to generate a schedule.") . "&type=warning");
    exit;
}

// 2️⃣ Clear old matches and reset team stats for this league only
$stmt_delete = $conn->prepare("DELETE FROM matches WHERE league_name = ?");
$stmt_delete->bind_param("s", $league_name);
$stmt_delete->execute();

$stmt_reset = $conn->prepare("UPDATE teams 
    SET played = 0, won = 0, drawn = 0, lost = 0, 
        goals_for = 0, goals_against = 0, points = 0 
    WHERE league_name = ?");
$stmt_reset->bind_param("s", $league_name);
$stmt_reset->execute();

// 3️⃣ Generate Round Robin Schedule
$teams = $team_ids;
$bye = null;
if (count($teams) % 2 != 0) {
    $teams[] = $bye; // Add "bye" if odd number of teams
}

$num_teams = count($teams);
$num_rounds = $num_teams - 1;
$all_matches = [];

// Generate first half
for ($r = 0; $r < $num_rounds; $r++) {
    $round_matches = [];
    for ($i = 0; $i < $num_teams / 2; $i++) {
        $home = $teams[$i];
        $away = $teams[($num_teams - 1) - $i];
        if ($home !== $bye && $away !== $bye) {
            // Randomize who plays home
            if (rand(0, 1) == 1) {
                $round_matches[] = [$r + 1, $home, $away];
            } else {
                $round_matches[] = [$r + 1, $away, $home];
            }
        }
    }
    // Rotate array (keeping first fixed)
    $last_team = array_pop($teams);
    array_splice($teams, 1, 0, [$last_team]);
    $all_matches = array_merge($all_matches, $round_matches);
}

// Generate return legs (swap home/away)
$return_matches = [];
foreach ($all_matches as $match) {
    $return_matches[] = [
        $match[0] + $num_rounds,
        $match[2],
        $match[1]
    ];
}
$full_schedule = array_merge($all_matches, $return_matches);

// 4️⃣ Insert matches into DB
$stmt_insert = $conn->prepare("INSERT INTO matches (league_name, round_number, home_team_id, away_team_id) VALUES (?, ?, ?, ?)");
if (!$stmt_insert) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

foreach ($full_schedule as $match) {
    $round_num = $match[0];
    $home_id = $match[1];
    $away_id = $match[2];
    $stmt_insert->bind_param("siii", $league_name, $round_num, $home_id, $away_id);
    if (!$stmt_insert->execute()) {
        die("Execute failed: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
    }
}

$stmt_insert->close();

// 5️⃣ Redirect back with success
$message = urlencode("✅ New double round-robin schedule generated and league stats reset!");
header("Location: index.php?message={$message}&type=success");
exit;
?>
