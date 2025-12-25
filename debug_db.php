<?php
require_once 'config.php';

echo "<h2>Check Table hasil_survey</h2>";
$result = $conn->query("SHOW COLUMNS FROM hasil_survey");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}
?>
