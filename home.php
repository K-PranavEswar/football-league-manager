<?php
include('db.php');

// Logout handler (change league)
if (isset($_GET['action']) && $_GET['action'] === 'change') {
    unset($_SESSION['league_name']);
    $message = "You have logged out. Please create or load a league.";
}

// If already loaded, go to manager
if (isset($_SESSION['league_name'])) {
    header("Location: index.php");
    exit;
}

$error_create = null;
$error_load = null;

// === CREATE LEAGUE ===
if (isset($_POST['create_league'])) {
    $league_name = trim($_POST['league_name']);
    if (!empty($league_name)) {
        $stmt = $conn->prepare("SELECT name FROM leagues WHERE name = ?");
        $stmt->bind_param("s", $league_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_create = "A league with this name already exists. Try loading it.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO leagues (name) VALUES (?)");
            $stmt_insert->bind_param("s", $league_name);
            $stmt_insert->execute();

            $_SESSION['league_name'] = $league_name;
            header("Location: index.php");
            exit;
        }
    } else {
        $error_create = "Please enter a name for your new league.";
    }
}

// === LOAD LEAGUE ===
if (isset($_POST['load_league'])) {
    $league_name = trim($_POST['league_name']);
    if (!empty($league_name)) {
        $stmt_check = $conn->prepare("SELECT name FROM leagues WHERE name = ?");
        $stmt_check->bind_param("s", $league_name);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $_SESSION['league_name'] = $league_name;
            header("Location: index.php");
            exit;
        } else {
            $error_load = "The selected league does not exist.";
        }
    } else {
        $error_load = "Please select a league to load.";
    }
}

// Fetch all existing leagues
$leagues_result = $conn->query("SELECT name FROM leagues ORDER BY name ASC");
$existing_leagues = [];
if ($leagues_result) {
    $existing_leagues = $leagues_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football League Manager</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .manager-container {
            max-width: 500px;
            width: 100%;
            padding: 2.5rem;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
            text-align: center;
        }
        .form-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #aaa;
            margin: 2rem 0;
        }
        .form-divider::before,
        .form-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }
        .form-divider:not(:empty)::before {
            margin-right: .5em;
        }
        .form-divider:not(:empty)::after {
            margin-left: .5em;
        }

        footer {
            text-align: center;
            margin-top: 2rem;
            color: #888;
            font-size: 0.9rem;
        }
        footer a {
            color: #0d6efd;
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="manager-container">
        <h2 class="mb-3">🏆</h2>
        <h2 class="h3 mb-4 fw-bold">Football League Manager</h2>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Load Existing League -->
        <form method="POST" action="home.php">
            <h5 class="mb-3 text-muted">Load Existing League</h5>
            <div class="mb-3">
                <select name="league_name" class="form-select form-select-lg" <?php echo empty($existing_leagues) ? 'disabled' : ''; ?>>
                    <option value="">-- Select a league --</option>
                    <?php foreach ($existing_leagues as $league): ?>
                        <option value="<?php echo htmlspecialchars($league['name']); ?>">
                            <?php echo htmlspecialchars($league['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($error_load): ?>
                    <div class="text-danger small mt-2"><?php echo $error_load; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" name="load_league" class="btn btn-success btn-lg w-100" <?php echo empty($existing_leagues) ? 'disabled' : ''; ?>>
                Load League
            </button>
        </form>

        <div class="form-divider">OR</div>

        <!-- Create New League -->
        <form method="POST" action="home.php">
            <h5 class="mb-3 text-muted">Create New League</h5>
            <div class="mb-3">
                <input type="text" name="league_name" class="form-control form-control-lg" placeholder="e.g., 'Season 2'" required>
                <?php if ($error_create): ?>
                    <div class="text-danger small mt-2"><?php echo $error_create; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" name="create_league" class="btn btn-primary btn-lg w-100">
                Create League
            </button>
        </form>
    </div>

    <!-- FOOTER -->
    <footer class="mt-4">
        <p>© <?php echo date('Y'); ?> Football League Manager. All rights reserved.</p>
        <p class="mb-0">
            Designed by <a href="https://www.linkedin.com/in/k-pranav-eswar1/" target="_blank">Pranav Eswar</a> | 
            <span class="text-muted">Version 1.0</span>
        </p>
    </footer>

    <script src="assets/bootstrap.bundle.min.js"></script>
</body>
</html>
