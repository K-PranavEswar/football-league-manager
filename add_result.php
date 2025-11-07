<?php include('db.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Match Result</title>
  <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2 class="text-center mb-4">⚽ Add Match Result</h2>

  <form method="POST" class="w-75 mx-auto">
    <div class="mb-3">
      <label>Home Team:</label>
      <select name="home_team" class="form-select" required>
        <option value="">Select Home Team</option>
        <?php
        $teams = $conn->query("SELECT * FROM teams");
        while ($row = $teams->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Away Team:</label>
      <select name="away_team" class="form-select" required>
        <option value="">Select Away Team</option>
        <?php
        $teams = $conn->query("SELECT * FROM teams");
        while ($row = $teams->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
      </select>
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
    <div class="text-center mt-3">
      <button class="btn btn-success" name="submit">Add Result</button>
      <a href="index.php" class="btn btn-secondary">Back</a>
    </div>
  </form>

  <?php
  if (isset($_POST['submit'])) {
      $home = $_POST['home_team'];
      $away = $_POST['away_team'];
      $hg = $_POST['home_goals'];
      $ag = $_POST['away_goals'];

      if ($home == $away) {
          echo "<div class='alert alert-danger mt-3 text-center'>A team cannot play against itself!</div>";
      } else {
          // Update stats
          $conn->query("UPDATE teams SET played = played + 1, goals_for = goals_for + $hg, goals_against = goals_against + $ag WHERE id = $home");
          $conn->query("UPDATE teams SET played = played + 1, goals_for = goals_for + $ag, goals_against = goals_against + $hg WHERE id = $away");

          if ($hg > $ag) {
              $conn->query("UPDATE teams SET won = won + 1, points = points + 3 WHERE id = $home");
              $conn->query("UPDATE teams SET lost = lost + 1 WHERE id = $away");
          } elseif ($hg < $ag) {
              $conn->query("UPDATE teams SET won = won + 1, points = points + 3 WHERE id = $away");
              $conn->query("UPDATE teams SET lost = lost + 1 WHERE id = $home");
          } else {
              $conn->query("UPDATE teams SET drawn = drawn + 1, points = points + 1 WHERE id IN ($home, $away)");
          }

          echo "<div class='alert alert-success mt-3 text-center'>Result added successfully!</div>";
      }
  }
  ?>
  <script src="assets/bootstrap.bundle.min.js"></script>
</body>
</html>
