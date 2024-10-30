<?php
// Initialize session if not already started
if (!session_id()) session_start();

// Database connection settings for XAMPP
$hostname = 'localhost'; // Use 'localhost' for XAMPP
$username = 'root';      // Default XAMPP username is 'root'
$password = '';          // Default XAMPP password for root is empty
$databasename = 'data_store'; // Ensure this matches your database name in XAMPP

// Create connection using MySQLi
$conn = new mysqli($hostname, $username, $password, $databasename);

// Check connection
if ($conn->connect_error) {
    die("Không thể kết nối cơ sở dữ liệu! " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Language setting logic
if (isset($_POST['set_language']) && $_POST['set_language'] == 'true') {
    $_SESSION['LANGUAGE'] = $_POST['LANGUAGE'];
} else {
    if (!isset($_SESSION['LANGUAGE']) || $_SESSION['LANGUAGE'] == NULL) {
        $_SESSION['LANGUAGE'] = 1; // Default language is Vietnamese
    }
}

$_lang = $_SESSION['LANGUAGE'] == 1 ? "vn" : "en";
include("lib/lang" . $_SESSION['LANGUAGE'] . ".php");

$langTitle = ($_lang == "vn") 
    ? '<a class="aLink3" href="#" onClick="doChangeLanguage(2)">
         <img src="images/flagEN.gif" border="0" height="13" width="23"> English
       </a>'
    : '<a class="aLink3" href="#" onClick="doChangeLanguage(1)">
         <img src="images/flagVN.gif" border="0" height="13" width="23"> Việt Nam
       </a>';
?>
