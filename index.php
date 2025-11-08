<?php
include('db.php');
if (isset($_POST['league_name']) && !empty($_POST['league_name'])) {
    $_SESSION['league_name'] = trim($_POST['league_name']);
    session_write_close();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['league_name']) || empty($_SESSION['league_name'])) {
    header("Location: home.php");
    exit;
}

$league_name = $_SESSION['league_name'];
$message = null; 
if (isset($_POST['add_team'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt_check = $conn->prepare("SELECT * FROM teams WHERE name = ? AND league_name = ?");
        $stmt_check->bind_param("ss", $name, $league_name);
        $stmt_check->execute();
        $check = $stmt_check->get_result();

        if ($check->num_rows > 0) {
            $message = ['type' => 'warning', 'text' => 'Team already exists in this league!'];
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO teams (name, league_name) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $name, $league_name);
            $stmt_insert->execute();
            $message = ['type' => 'success', 'text' => 'Team added successfully!'];
        }
    }
}
if (isset($_POST['update_result'])) {
    $match_id = (int)$_POST['match_id'];
    $hg = (int)$_POST['home_goals'];
    $ag = (int)$_POST['away_goals'];
    $stmt_match = $conn->prepare("SELECT * FROM matches WHERE id = ? AND match_played = 0 AND league_name = ?");
    $stmt_match->bind_param("is", $match_id, $league_name);
    $stmt_match->execute();
    $match_result = $stmt_match->get_result();

    if ($match_result->num_rows > 0) {
        $match = $match_result->fetch_assoc();
        $home_team_id = $match['home_team_id'];
        $away_team_id = $match['away_team_id'];
        $round_number = $match['round_number'];
        $is_playoff = $match['is_playoff'];
        if ($is_playoff == 1 && $hg == $ag) {
            $message = ['type' => 'danger', 'text' => 'Draws are not allowed in knockout rounds. Please enter a winner.'];
        } else {
            $winner_team_id = NULL;
            if ($is_playoff == 1) {
                $winner_team_id = ($hg > $ag) ? $home_team_id : $away_team_id;
            }
            $stmt_update = $conn->prepare("UPDATE matches SET home_goals = ?, away_goals = ?, match_played = 1, winner_team_id = ? WHERE id = ?");
            $stmt_update->bind_param("iiii", $hg, $ag, $winner_team_id, $match_id);
            $stmt_update->execute();
            if ($is_playoff == 0) {
                $stmt_update_home = $conn->prepare("UPDATE teams SET played = played + 1, goals_for = goals_for + ?, goals_against = goals_against + ? WHERE id = ?");
                $stmt_update_home->bind_param("iii", $hg, $ag, $home_team_id);
                $stmt_update_home->execute();
                
                $stmt_update_away = $conn->prepare("UPDATE teams SET played = played + 1, goals_for = goals_for + ?, goals_against = goals_against + ? WHERE id = ?");
                $stmt_update_away->bind_param("iii", $ag, $hg, $away_team_id);
                $stmt_update_away->execute();
                if ($hg > $ag) { 
                    $conn->query("UPDATE teams SET won = won + 1, points = points + 3 WHERE id = $home_team_id");
                    $conn->query("UPDATE teams SET lost = lost + 1 WHERE id = $away_team_id");
                } elseif ($hg < $ag) { 
                    $conn->query("UPDATE teams SET won = won + 1, points = points + 3 WHERE id = $away_team_id");
                    $conn->query("UPDATE teams SET lost = lost + 1 WHERE id = $home_team_id");
                } else { 
                    $conn->query("UPDATE teams SET drawn = drawn + 1, points = points + 1 WHERE id IN ($home_team_id, $away_team_id)");
                }
            }
            $message = ['type' => 'success', 'text' => 'Result updated successfully!'];
            if ($round_number == 100) {
                $stmt_semis_check = $conn->prepare("SELECT id FROM matches WHERE round_number = 100 AND match_played = 0 AND league_name = ?");
                $stmt_semis_check->bind_param("s", $league_name);
                $stmt_semis_check->execute();
                $semis_pending_check = $stmt_semis_check->get_result();
                
                if ($semis_pending_check->num_rows == 0) {
                    $stmt_winners = $conn->prepare("SELECT winner_team_id FROM matches WHERE round_number = 100 AND league_name = ?");
                    $stmt_winners->bind_param("s", $league_name);
                    $stmt_winners->execute();
                    $semi_final_results = $stmt_winners->get_result();
                    
                    $winners = [];
                    while ($sf_match = $semi_final_results->fetch_assoc()) {
                        $winners[] = $sf_match['winner_team_id'];
                    }
                    if (count($winners) == 2) {
                        $stmt_final = $conn->prepare("INSERT INTO matches (league_name, round_number, home_team_id, away_team_id, is_playoff) VALUES (?, 101, ?, ?, 1)");
                        $stmt_final->bind_param("sii", $league_name, $winners[0], $winners[1]);
                        $stmt_final->execute();
                        $message = ['type' => 'success', 'text' => 'Semi-Final result saved. The Final has been generated!'];
                    }
                }
            }
        }
    } else {
        $message = ['type' => 'danger', 'text' => 'This result has already been submitted or the match does not exist.'];
    }
}
$stmt_league = $conn->prepare("SELECT *, (goals_for - goals_against) AS gd FROM teams WHERE league_name = ? ORDER BY points DESC, gd DESC, goals_for DESC, name ASC");
$stmt_league->bind_param("s", $league_name);
$stmt_league->execute();
$league_result = $stmt_league->get_result();
$team_count = $league_result->num_rows;
$stmt_schedule = $conn->prepare("SELECT m.id, m.round_number, t_home.name as home_name, t_away.name as away_name, m.home_goals, m.away_goals, m.match_played, m.is_playoff
    FROM matches m 
    JOIN teams t_home ON m.home_team_id = t_home.id 
    JOIN teams t_away ON m.away_team_id = t_away.id 
    WHERE m.league_name = ?
    ORDER BY m.round_number, m.id");
$stmt_schedule->bind_param("s", $league_name);
$stmt_schedule->execute();
$schedule_result = $stmt_schedule->get_result();

$schedule_count = 0;
$fixtures_by_round = [];
if ($schedule_result) {
    $schedule_count = $schedule_result->num_rows;
    while ($row = $schedule_result->fetch_assoc()) {
        $fixtures_by_round[$row['round_number']][] = $row;
    }
}
$stmt_active_round = $conn->prepare("SELECT MIN(round_number) as current_round FROM matches WHERE match_played = 0 AND league_name = ?");
$stmt_active_round->bind_param("s", $league_name);
$stmt_active_round->execute();
$active_round = $stmt_active_round->get_result()->fetch_assoc()['current_round'] ?? null;
$stmt_league_complete = $conn->prepare("SELECT id FROM matches WHERE match_played = 0 AND is_playoff = 0 AND league_name = ?");
$stmt_league_complete->bind_param("s", $league_name);
$stmt_league_complete->execute();
$league_complete_check = $stmt_league_complete->get_result();
$league_complete = ($league_complete_check->num_rows == 0 && $schedule_count > 0 && !empty($fixtures_by_round));
$stmt_playoffs_check = $conn->prepare("SELECT id FROM matches WHERE is_playoff = 1 AND league_name = ?");
$stmt_playoffs_check->bind_param("s", $league_name);
$stmt_playoffs_check->execute();
$playoffs_generated = ($stmt_playoffs_check->get_result()->num_rows > 0);
$champion = null;
$stmt_champion = $conn->prepare("SELECT m.winner_team_id, t.name as winner_name FROM matches m JOIN teams t ON m.winner_team_id = t.id WHERE m.round_number = 101 AND m.match_played = 1 AND m.league_name = ?");
$stmt_champion->bind_param("s", $league_name);
$stmt_champion->execute();
$champion_check = $stmt_champion->get_result();

if ($champion_check->num_rows > 0) {
    $champion_data = $champion_check->fetch_assoc();
    $champion = $champion_data['winner_name'];
}
$season_complete = ($active_round === null && $schedule_count > 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($league_name); ?> - League Manager</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;5G00;600;700&display=swap');
    
    :root {
        --fuchsia-accent: #c81482; 
        --fuchsia-accent-hover: #d81b8f;
        --purple-accent: #8e44ad; 
        --purple-accent-hover: #9b59b6;
        
        --text-primary: #f5f5f5;
        --text-secondary: #b0b0b0;
        
        --bg-container: rgba(20, 10, 30, 0.75);
        --bg-header: rgba(20, 10, 30, 0.85);
        --bg-controls: rgba(10, 5, 20, 0.8);
        --bg-card: rgba(10, 5, 20, 0.6);
        
        --border-light: rgba(255, 255, 255, 0.1);
        --border-medium: rgba(255, 255, 255, 0.2);
    }

    body {
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        background-image: linear-gradient(45deg, rgba(50, 0, 80, 0.9), rgba(120, 20, 100, 0.85)), url('https://source.unsplash.com/1600x900/?stadium,lights,night');
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        min-height: 100vh;
    }

    .league-container {
        max-width: 1200px;
        margin: 2rem auto;
        border: 1px solid var(--border-light);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        overflow: hidden;
    }

    .league-header { 
        padding: 1.5rem 2rem; 
        border-bottom: 1px solid var(--border-light); 
        background-color: var(--bg-header);
    }
    
    .league-controls { 
        padding: 1rem 1.5rem; 
        background-color: var(--bg-controls); 
        border-bottom: 1px solid var(--border-light); 
        display: flex; 
        gap: 0.75rem; 
        justify-content: center; 
        flex-wrap: wrap; 
    }
    .league-controls .btn, .league-controls form {
        flex-grow: 1; 
        flex-basis: 150px; 
        display: flex; 
    }
    .league-controls .btn {
        width: 100%; 
    }
    
    .league-content { 
        padding: 1.5rem;
        background-color: var(--bg-container);
    }

    .stat-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
    }
    .stat-card-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    .stat-card-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .stat-card-value.text-success { color: #28a745 !important; }
    .stat-card-value.text-warning { color: #ffc107 !important; }
    .stat-card-value.text-info { color: #17a2b8 !important; }

    .content-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 8px;
        padding: 1rem;
        height: 100%;
    }
    .content-card h4 {
        margin-top: 0.25rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .table { 
        border-collapse: separate; 
        border-spacing: 0; 
        margin-bottom: 0; 
        color: var(--text-primary); 
    }
    .table thead th { 
        background-color: transparent; 
        border: 0;
        border-bottom: 2px solid var(--border-medium); 
        color: var(--text-primary); 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .table tbody tr {
        border-bottom: 1px solid var(--border-light); 
    }
    .table tbody tr:last-child {
        border-bottom: none; 
    }
    .table tbody tr:hover { 
        background-color: rgba(255, 255, 255, 0.05);
    }
    .table td { 
        vertical-align: middle; 
        border-top: none;
        padding: 0.75rem 0.5rem;
    }
    .table thead th:first-child { border-top-left-radius: 0; }
    .table thead th:last-child { border-top-right-radius: 0; }
    .table tbody tr:last-child td:first-child { border-bottom-left-radius: 6px; }
    .table tbody tr:last-child td:last-child { border-bottom-right-radius: 6px; }
    
    .table .pos-cell { 
        min-width: 50px; 
        padding-left: 1rem;
    }
    
    .table tbody tr:nth-child(1) .pos-cell { 
        background-color: rgba(40, 167, 69, 0.25); 
        font-weight: 700; 
        color: #28a745; 
        border-radius: 4px; 
    }
    .table tbody tr:nth-child(2) .pos-cell { 
        background-color: rgba(59, 130, 246, 0.25); 
        font-weight: 700; 
        color: #3b82f6; 
        border-radius: 4px;
    }
    .table tbody tr:nth-child(3) .pos-cell { 
        background-color: rgba(34, 211, 238, 0.25); 
        font-weight: 700; 
        color: #22d3ee; 
        border-radius: 4px;
    }
    .btn-primary {
        background-color: var(--fuchsia-accent);
        border-color: var(--fuchsia-accent);
        color: #ffffff;
        font-weight: 500;
    }
    .btn-primary:hover {
        background-color: var(--fuchsia-accent-hover);
        border-color: var(--fuchsia-accent-hover);
        color: #ffffff;
    }
    .btn-success { 
        background-color: #28a745;
        border-color: #28a745;
        color: #ffffff;
        font-weight: 500;
    }
     .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    .btn-info {
        background-color: #17a2b8; 
        border-color: #17a2b8;
        color: #ffffff;
        font-weight: 500;
    }
    .btn-info:hover {
        background-color: #138496;
        border-color: #117a8b;
    }
    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529; 
        font-weight: 700; 
    }
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #d39e00;
    }
    .btn-danger { 
        background-color: #dc3545; 
        border-color: #dc3545;
        color: #ffffff;
        font-weight: 500;
    }
    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    .btn-outline-secondary {
        color: var(--text-secondary);
        border-color: var(--border-medium);
    }
    .btn-outline-secondary:hover {
        background-color: var(--border-medium);
        color: var(--text-primary);
    }

    .modal-content {
        background-color: rgba(30, 20, 40, 0.9);
        border: 1px solid var(--border-light);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: var(--text-primary);
    }
    .modal-header {
        border-bottom: 1px solid var(--border-light);
    }
    .modal-footer {
        border-top: 1px solid var(--border-light);
    }
    .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .form-control {
        background-color: rgba(0, 0, 0, 0.2);
        color: var(--text-primary);
        border: 1px solid var(--border-medium);
    }
    .form-control:focus {
        background-color: rgba(0, 0, 0, 0.3);
        color: var(--text-primary);
        border-color: var(--fuchsia-accent);
        box-shadow: 0 0 0 0.25rem rgba(200, 20, 130, 0.3);
    }
    .form-control::placeholder {
        color: #888;
    }
    .alert { 
        border-width: 0; 
        border-left: 5px solid;
        background-color: rgba(30, 30, 30, 0.7);
        backdrop-filter: blur(5px);
    }
    .alert-success {
        color: #6ee7b7;
        border-color: #1e4d3a;
    }
    .alert-warning {
        color: #fde047;
        border-color: #4d4d1e;
    }
    .alert-danger {
        color: #fca5a5;
        border-color: #4d1e1e;
    }
    .alert-info {
        color: #7dd3fc;
        border-color: #0c4a6e;
    }
    .alert .btn-close {
        filter: none;
    }
    .table-striped > tbody > tr:nth-of-type(odd) > * {
        background-color: rgba(255, 255, 255, 0.05);
        color: var(--text-primary);
    }
     .table-striped > tbody > tr:nth-of-type(even) > * {
        background-color: transparent;
        color: var(--text-primary);
    }
    .fixture-list { list-style: none; padding-left: 0; }
    .fixture-list li {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border-light);
    }
    .fixture-list li:last-child { border-bottom: none; }
    .fixture-team { display: inline-block; width: calc(50% - 40px); }
    .fixture-score { display: inline-block; width: 80px; text-align: center; }
</style>
</head>
<body>

<div class="league-container">
    
    <div class="league-header text-center">
        <a href="logout.php" class="btn btn-sm btn-outline-secondary" style="float: right;">Change League</a>
        <h2>🏆 <?php echo htmlspecialchars($league_name); ?></h2>
    </div>

    <div class="league-controls">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
            ➕ Add Team
        </button>
        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#scheduleModal" <?php echo $schedule_count == 0 ? 'disabled' : ''; ?>>
            📅 View Full Schedule
        </button>
        <form method="POST" action="generate_schedule.php" onsubmit="return confirm('Are you sure? This will reset all current stats and fixtures for this league.');">
            <button type="submit" class="btn btn-success" <?php echo $team_count < 2 ? 'disabled' : ''; ?>>
                🔄 Generate Schedule
            </button>
        </form>
        <?php if ($league_complete && !$playoffs_generated && $team_count >= 4): ?>
            <form method="POST" action="generate_playoffs.php" onsubmit="return confirm('Generate Top 4 Playoffs?');">
                <button type="submit" class="btn btn-warning fw-bold">
                    🏆 Generate Playoffs
                </button>
            </form>
        <?php endif; ?>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#resetLeagueModal">
            ⛔ Reset League
        </button>
    </div>
    
    <div class="league-content">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_GET['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-title">Total Teams</div>
                    <div class="stat-card-value"><?php echo $team_count; ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-title">Total Matches</div>
                    <div class="stat-card-value"><?php echo $schedule_count; ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-title">Current Round</div>
                    <div class="stat-card-value text-info">
                        <?php 
                            if ($champion) echo "Finished";
                            elseif ($active_round == 101) echo "The Final";
                            elseif ($active_round == 100) echo "Semi-Finals";
                            elseif ($active_round) echo $active_round;
                            elseif ($season_complete) echo "Finished";
                            else echo "-";
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-title">League Status</div>
                    <div class="stat-card-value <?php echo $champion ? 'text-warning' : ($league_complete ? 'text-success' : ''); ?>">
                        <?php
                            if ($champion) echo "Complete";
                            elseif ($playoffs_generated) echo "Playoffs";
                            elseif ($league_complete) echo "League Done";
                            elseif ($schedule_count > 0) echo "In Progress";
                            else echo "Setup";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            
            <div class="col-lg-7">
                <div class="content-card">
                    <h4>League Standings</h4>
                    <div class="table-responsive">
                        <table class="table table-hover text-center">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Team</th>
                                    <th>P</th>
                                    <th>W</th>
                                    <th>D</th>
                                    <th>L</th>
                                    <th>GF</th>
                                    <th>GA</th>
                                    <th>GD</th>
                                    <th>Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pos = 1;
                                if ($league_result->num_rows > 0) {
                                    while ($row = $league_result->fetch_assoc()) {
                                        echo "<tr>
                                            <td class='pos-cell'>{$pos}</td>
                                            <td class='text-start'>" . htmlspecialchars($row['name']) . "</td>
                                            <td>{$row['played']}</td>
                                            <td>{$row['won']}</td>
                                            <td>{$row['drawn']}</td>
                                            <td>{$row['lost']}</td>
                                            <td>{$row['goals_for']}</td>
                                            <td>{$row['goals_against']}</td>
                                            <td>{$row['gd']}</td>
                                            <td><strong>{$row['points']}</strong></td>
                                        </tr>";
                                        $pos++;
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='p-3'>No teams added yet.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="content-card">

                    <?php if ($champion): ?>
                        <h4 class="text-center">🏆 League Champion 🏆</h4>
                        <div class="text-center p-4">
                            <h1 class="text-warning mb-3"><?php echo htmlspecialchars($champion); ?></h1>
                            <p>The season is complete!</p>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                📅 View Season Review
                            </button>
                        </div>

                    <?php elseif ($league_complete && !$playoffs_generated && $team_count >= 4): ?>
                        <h4>Playoffs Ready</h4>
                        <div class="text-center p-4">
                            <p>The league season is complete! The Top 4 teams will advance to the knockout stage.</p>
                            <form method="POST" action="generate_playoffs.php" onsubmit="return confirm('Generate Top 4 Playoffs?');">
                                <button type="submit" class="btn btn-lg btn-warning fw-bold">
                                    🏆 Generate Playoffs Now
                                </button>
                            </form>
                        </div>
                    
                    <?php elseif ($season_complete && !$playoffs_generated): ?>
                         <h4>League Complete</h4>
                        <div class="text-center p-4">
                            <p>The league season is complete!</p>
                            <?php if ($team_count < 4): ?>
                                <p class="text-muted">(Not enough teams for playoffs).</p>
                            <?php endif; ?>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                📅 View Season Review
                            </button>
                        </div>

                    <?php elseif ($active_round && isset($fixtures_by_round[$active_round])): ?>
                        <?php
                            $round_name = "Round " . $active_round;
                            if ($active_round == 100) $round_name = "Semi-Finals";
                            if ($active_round == 101) $round_name = "The Final";
                        ?>
                        <h4>Active: <?php echo $round_name; ?></h4>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-end">Home Team</th>
                                        <th class="text-center">Result</th>
                                        <th class="text-start">Away Team</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fixtures_by_round[$active_round] as $match): ?>
                                        <tr>
                                            <td class="text-end"><?php echo htmlspecialchars($match['home_name']); ?></td>
                                            
                                            <?php if ($match['match_played']): ?>
                                                <td class="text-center" style="width: 80px;"><strong><?php echo $match['home_goals']; ?> - <?php echo $match['away_goals']; ?></strong></td>
                                            <?php else: ?>
                                                <td class="text-center" style="width: 80px;">vs</td>
                                            <?php endif; ?>

                                            <td class="text-start"><?php echo htmlspecialchars($match['away_name']); ?></td>
                                            <td class="text-center" style="width: 120px;">
                                                <?php if ($match['match_played']): ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Played</button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#enterResultModal"
                                                            data-bs-match-id="<?php echo $match['id']; ?>"
                                                            data-bs-home-name="<?php echo htmlspecialchars($match['home_name']); ?>"
                                                            data-bs-away-name="<?php echo htmlspecialchars($match['away_name']); ?>">
                                                        Enter Result
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    
                    <?php elseif ($schedule_count == 0): ?>
                        <h4>Welcome!</h4>
                        <div class="text-center p-4">
                            <p>Your league is set up. Add at least 2 teams and then click "Generate Schedule" to begin.</p>
                        </div>
                    
                    <?php else: ?>
                         <h4>Error</h4>
                        <div class="text-center p-4">
                            <p>Could not determine league state. Please refresh.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div> </div> </div> <div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Add New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="name" class="form-control" placeholder="Enter team name" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_team" class="btn btn-primary">Add Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📅 Full Schedule & Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">

                    <?php if (empty($fixtures_by_round)): ?>
                        <p class="text-center p-4">No schedule generated yet. Click "Generate Schedule" to create one.</p>
                    
                    <?php else: ?>
                        <?php if ($champion): ?>
                            <h3 class="text-center text-warning p-4">🏆 League Champion: <?php echo htmlspecialchars($champion); ?> 🏆</h3>
                            <hr>
                        <?php endif; ?>

                        <h4 class="text-center mb-3 mt-4">Full Season Review</h4>
                        
                        <?php foreach ($fixtures_by_round as $round_num => $matches): ?>
                            <?php
                                $round_name = "Round " . $round_num;
                                if ($round_num == 100) $round_name = "Semi-Finals";
                                if ($round_num == 101) $round_name = "The Final";
                            ?>
                            <h5 class="mt-4"><?php echo $round_name; ?></h5>
                            <table class="table table-striped align-middle mb-4">
                                <thead>
                                    <tr>
                                        <th class="text-end">Home Team</th>
                                        <th class="text-center">Result</th>
                                        <th class="text-start">Away Team</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matches as $match): ?>
                                        <tr>
                                            <td class="text-end"><?php echo htmlspecialchars($match['home_name']); ?></td>
                                            <td class="text-center" style="width: 100px;">
                                                <?php if ($match['match_played']): ?>
                                                    <strong><?php echo $match['home_goals']; ?> - <?php echo $match['away_goals']; ?></strong>
                                                <?php else: ?>
                                                    <span class="text-muted">vs</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start"><?php echo htmlspecialchars($match['away_name']); ?></td>
                                            <td class="text-center" style="width: 120px;">
                                                <?php if ($match['match_played']): ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Played</button>
                                                <?php else: ?>
                                                     <button class="btn btn-sm btn-outline-secondary" disabled>Pending</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="enterResultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="enterResultModalTitle">Enter Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="match_id" id="modal_match_id">
                    
                    <div class="d-flex justify-content-center align-items-center mb-3">
                        <span id="modal_home_name" class="fw-bold fs-5 text-end" style="flex: 1;">Home</span>
                        <span class="mx-3">vs</span>
                        <span id="modal_away_name" class="fw-bold fs-5 text-start" style="flex: 1;">Away</span>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label>Home Goals:</label>
                            <input type="number" name="home_goals" class="form-control" min="0" required>
                        </div>
                        <div class="col">
                            <label>Away Goals:</label>
                            <input type="number" name="away_goals" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_result" class="btn btn-success">Save Result</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="resetLeagueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="reset.php">
                <div class="modal-header">
                    <h5 class="modal-title">⛔ Reset League</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset <strong><?php echo htmlspecialchars($league_name); ?></strong>? 
                    <strong>All teams, scores, and schedules for this league will be permanently deleted.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Reset This League</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/bootstrap.bundle.min.js"></script>
<script>
    var enterResultModal = document.getElementById('enterResultModal');
    if (enterResultModal) {
        enterResultModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var matchId = button.getAttribute('data-bs-match-id');
            var homeName = button.getAttribute('data-bs-home-name');
            var awayName = button.getAttribute('data-bs-away-name');
            var modalTitle = enterResultModal.querySelector('.modal-title');
            var modalMatchIdInput = enterResultModal.querySelector('#modal_match_id');
            var modalHomeNameSpan = enterResultModal.querySelector('#modal_home_name');
            var modalAwayNameSpan = enterResultModal.querySelector('#modal_away_name');
            
            modalTitle.textContent = 'Enter Result: ' + homeName + ' vs ' + awayName;
            modalMatchIdInput.value = matchId;
            modalHomeNameSpan.textContent = homeName;
            modalAwayNameSpan.textContent = awayName;
        });
    }
</script>
</body>
</html>