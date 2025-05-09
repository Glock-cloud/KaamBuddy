<?php
include_once 'includes/functions.php';

// Update the "Other" category icon
$updateQuery = "UPDATE service_categories SET icon = 'fa-screwdriver-wrench' WHERE name = 'Other'";
$result = $conn->query($updateQuery);

if ($result) {
    echo "Successfully updated the 'Other' category icon to 'fa-screwdriver-wrench'";
} else {
    echo "Error updating category icon: " . $conn->error;
}
?> 