<?php
include_once 'includes/functions.php';

// Fetch all service categories
$query = "SELECT * FROM service_categories ORDER BY name";
$result = $conn->query($query);

echo "<h2>Service Categories and Icons</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Icon Class</th><th>Preview</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['icon'] . "</td>";
        echo "<td><i class='fas " . $row['icon'] . "'></i> (preview requires FontAwesome)</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Check if "Other" category exists, if not, create it
$checkOtherQuery = "SELECT * FROM service_categories WHERE name = 'Other'";
$otherResult = $conn->query($checkOtherQuery);

echo "<h3>Other Category Check</h3>";
if ($otherResult && $otherResult->num_rows > 0) {
    echo "Other category exists.";
} else {
    echo "Other category doesn't exist. Creating it now.<br>";
    $insertOtherQuery = "INSERT INTO service_categories (name, icon, description) VALUES ('Other', 'fa-screwdriver-wrench', 'Other miscellaneous services')";
    if ($conn->query($insertOtherQuery)) {
        echo "Successfully created 'Other' category with icon 'fa-screwdriver-wrench'";
    } else {
        echo "Error creating 'Other' category: " . $conn->error;
    }
}

// Check if the CSS link for FontAwesome includes the correct version for fa-screwdriver-wrench
echo "<h3>FontAwesome Version Check</h3>";
echo "Note: The fa-screwdriver-wrench icon requires FontAwesome 6.x, while your site is using FontAwesome 5.x<br>";
echo "Current link in index.php: <code>https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css</code><br>";
echo "For fa-screwdriver-wrench to work, you need to update to FontAwesome 6.x";
?> 