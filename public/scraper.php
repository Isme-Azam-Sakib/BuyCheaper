<!-- scrapers.php -->
<?php
include_once '../config/database.php';
include '../includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrapers</title>
    <link rel="stylesheet" href="../css/style.css">    
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
                <form action='{$vendor['scraper']}' method='post'>
                    <button type='submit'>Scrape Now</button>
                </form>
              </div>";
    }
    ?>
</div>

</body>
</html>
