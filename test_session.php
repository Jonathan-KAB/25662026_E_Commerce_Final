<?php
session_start();

echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . " (1=disabled, 2=active)<br>";

if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 1;
    $_SESSION['test_time'] = date('Y-m-d H:i:s');
} else {
    $_SESSION['test_counter']++;
}

echo "Counter: " . $_SESSION['test_counter'] . "<br>";
echo "First Visit: " . $_SESSION['test_time'] . "<br>";
echo "<br>All Session Data:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<br><a href='test_session.php'>Refresh Page</a>";
echo " | <a href='test_session.php?clear=1'>Clear Session</a>";

if (isset($_GET['clear'])) {
    session_destroy();
    echo "<br><br>Session destroyed! <a href='test_session.php'>Start Fresh</a>";
}
?>
