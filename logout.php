<?php
// user session එක clear කර logout කිරීම.
require_once 'includes/functions.php';
session_destroy();
session_start();
setFlash('success', 'You have logged out successfully.');
redirect('index.php');
?>
