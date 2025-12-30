<?php
require_once 'config.php';

$sql = "ALTER TABLE pemesanan MODIFY COLUMN waktu_survey TIME";
if ($conn->query($sql) === TRUE) {
    echo "Successfully reverted waktu_survey column to TIME.";
} else {
    echo "Error updating column: " . $conn->error;
}
?>
