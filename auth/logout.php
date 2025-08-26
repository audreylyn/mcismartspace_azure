
<?php
session_start();
session_destroy();
header('Location: login.php'); // No output before this line!
exit();