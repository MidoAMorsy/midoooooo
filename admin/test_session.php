<?php
// admin/test_session.php
// This is a standalone file to test if sessions are working on the server.

require_once '../includes/config.php';
// session_start(); // config.php starts the session

// Check if a counter exists
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
    $msg = "New session started.";
} else {
    $_SESSION['test_counter']++;
    $msg = "Session exists! Counter: " . $_SESSION['test_counter'];
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Session Test</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Session Test</h1>

    <p>Session ID: <code><?php echo session_id(); ?></code></p>

    <p>Message: <span
            class="<?php echo $_SESSION['test_counter'] > 0 ? 'success' : 'error'; ?>"><?php echo $msg; ?></span></p>

    <p>Counter value: <strong><?php echo $_SESSION['test_counter']; ?></strong></p>

    <p>
        <a href="test_session.php">Click here to reload the page</a>
    </p>

    <hr>

    <h3>Instructions:</h3>
    <ol>
        <li>Click the "Reload" link above 2-3 times.</li>
        <li>If the "Counter value" increases (1, 2, 3...), sessions are <strong>WORKING</strong>.</li>
        <li>If the "Counter value" stays at 0 or "New session started" appears every time, sessions are
            <strong>BROKEN</strong>.
        </li>
    </ol>
</body>

</html>