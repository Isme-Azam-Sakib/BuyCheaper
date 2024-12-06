<!-- scrapers.php -->
<?php
$conn = require_once '../config/database.php';
include '../includes/navbar.php';

// Handle vendor data clearing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vendor'])) {
    $vendor = $_POST['vendor'];

    try {
        $sql = "DELETE FROM products WHERE vendor = :vendor";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':vendor', $vendor);
        $stmt->execute();

        header("Location: scraper.php?message=cleared");
        exit();
    } catch (PDOException $e) {
        header("Location: scraper.php?error=1");
        exit();
    }
}

function clearData($conn)
{
    try {
        // Start transaction to ensure both deletions succeed or none do
        $conn->beginTransaction();

        // Delete from vendor_prices first (because it likely has foreign key references)
        $stmt = $conn->prepare("DELETE FROM vendor_prices");
        $stmt->execute();

        // Then delete from products
        $stmt = $conn->prepare("DELETE FROM products");
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        return true;
    } catch (PDOException $e) {
        // If there's an error, rollback the changes
        $conn->rollBack();
        throw $e;
    }
}

// Usage (where your clear button handler is):
if (isset($_POST['clear_data']) || isset($_GET['clear_data'])) {
    try {
        clearData($conn);
        // Redirect or show success message
        header("Location: index.php?message=Data cleared successfully");
        exit();
    } catch (Exception $e) {
        // Handle error
        header("Location: index.php?error=Failed to clear data: " . urlencode($e->getMessage()));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrapers</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .scraper-card .button-container {
            display: flex !important;
            flex-direction: row !important;
            gap: 10px !important;
            justify-content: center !important;
            width: 100% !important;
        }

        .scraper-card .button-container form {
            flex: 1 !important;
            margin: 0 !important;
        }

        .scraper-card .button-container button {
            width: 100% !important;
            position: static !important;
            margin: 0 !important;
        }

        .scraper-card .button-container .clear-btn {
            background-color: #dc3545 !important;
        }

        .scraper-card .button-container .clear-btn:hover {
            background-color: #c82333 !important;
        }
    </style>
</head>

<body>

    <h2 style="text-align: center; margin-top: 20px;">Available Scrapers</h2>

    <div class="scraper-card-container">
        <?php
        $vendors = [
            ['name' => 'Startech', 'image' => '../assets/startech.png', 'scraper' => '../scrapers/startech_scraper.php'],
            // ['name' => 'Ryans', 'image' => '../assets/ryans.png', 'scraper' => '../scrapers/ryans_scraper.php'],
            ['name' => 'Techland', 'image' => '../assets/techland.png', 'scraper' => '../scrapers/techland_scraper.php'],
            ['name' => 'Skyland', 'image' => '../assets/skyland.png', 'scraper' => '../scrapers/skyland_scraper.php'],
            ['name' => 'Ultratech', 'image' => '../assets/ultratech.png', 'scraper' => '../scrapers/ultratech_scraper.php'],
            ['name' => 'PCHouse', 'image' => '../assets/pchouse.png', 'scraper' => '../scrapers/pchouse_scraper.php']
        ];

        foreach ($vendors as $vendor) {
            echo "<div class='scraper-card'>
                <img src='{$vendor['image']}' alt='{$vendor['name']} Logo'>
                <h3>{$vendor['name']}</h3>
                <div class='button-container' style='display: flex; gap: 10px; justify-content: center;'>
                    <form action='{$vendor['scraper']}' method='post'>
                        <button type='submit' class='scrape-btn'>Scrape Now</button>
                    </form>
                    
                </div>
              </div>";
        }
        ?>
    </div>
    <!-- <form action='scraper.php' method='post'>
        <input type='hidden' name='vendor' value='{$vendor[' name']}'>
        <button type='submit' class='clear-btn'>Clear Data</button>
    </form> -->

</body>

</html>