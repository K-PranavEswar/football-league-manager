<?php include('db.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Team</title>
  <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2 class="text-center mb-4">➕ Add New Team</h2>

  <form method="POST" class="w-50 mx-auto">
    <div class="mb-3">
      <input type="text" name="name" class="form-control" placeholder="Enter team name" required>
    </div>
    <div class="text-center">
      <button type="submit" name="add" class="btn btn-primary">Add Team</button>
      <a href="index.php" class="btn btn-secondary">Back</a>
    </div>
  </form>

  <?php
  if (isset($_POST['add'])) {
      $name = trim($_POST['name']);
      $check = $conn->query("SELECT * FROM teams WHERE name='$name'");
      if ($check->num_rows > 0) {
          echo "<div class='alert alert-warning mt-3 text-center'>Team already exists!</div>";
      } else {
          $conn->query("INSERT INTO teams (name) VALUES ('$name')");
          echo "<div class='alert alert-success mt-3 text-center'>Team added successfully!</div>";
      }
  }
  ?>
  <script src="assets/bootstrap.bundle.min.js"></script>
</body>
</html>
