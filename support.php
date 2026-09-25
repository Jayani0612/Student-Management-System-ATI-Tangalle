<?php
require_once "includes/session_check.php";
$pageTitle = "Support";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Support · EduSphere</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include "includes/sidebar.php"; ?>

    <div class="main">
        <?php include "includes/header.php"; ?>

        <div class="content">
            <div class="card placeholder-panel">
                <div class="placeholder-icon"><i class="fa-solid fa-circle-question"></i></div>
                <h2>Support Module</h2>
                <p>Get help, browse documentation or contact the EduSphere team.<br>This section is scaffolded and ready — connect it to the MySQL database the same way the Students module is built.</p>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
