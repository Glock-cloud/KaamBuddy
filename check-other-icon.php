<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Icon Check - KaamBuddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .icon-display {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 5px;
        }
        .icon {
            font-size: 40px;
            color: #ff6b6b;
            width: 60px;
            text-align: center;
        }
        .icon-details {
            flex: 1;
        }
        h1 {
            margin-bottom: 30px;
        }
        h2 {
            margin-top: 40px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Category Icon Check</h1>
    
    <h2>Other Category Icon Display</h2>
    <div class="icon-display">
        <div class="icon">
            <i class="fas fa-screwdriver-wrench"></i>
        </div>
        <div class="icon-details">
            <h3>Other</h3>
            <p>Icon class: <code>fa-screwdriver-wrench</code></p>
        </div>
    </div>
    
    <h2>All Service Categories</h2>
    <?php
    include_once 'includes/functions.php';
    
    $query = "SELECT * FROM service_categories ORDER BY name";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="icon-display">';
            echo '<div class="icon"><i class="fas ' . $row['icon'] . '"></i></div>';
            echo '<div class="icon-details">';
            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
            echo '<p>Icon class: <code>' . $row['icon'] . '</code></p>';
            echo '<p>' . htmlspecialchars($row['description']) . '</p>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No categories found</p>';
    }
    ?>
    
    <h2>FontAwesome Version Info</h2>
    <p>This page is using FontAwesome 6.4.0 which includes the <code>fa-screwdriver-wrench</code> icon.</p>
    <p>The following changes were made:</p>
    <pre>
// Updated all pages from:
&lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"&gt;

// To:
&lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"&gt;
    </pre>
</body>
</html> 