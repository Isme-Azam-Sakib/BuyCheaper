<!DOCTYPE html>
<html lang="en">
<head>
    <title>Price Comparison - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="container my-5">
    <h1 class="text-center mb-4">Compare Prices of PC Parts</h1>
    
    <!-- Category Selection -->
    <div class="row mb-3">
        <div class="col-md-4 offset-md-4">
            <select id="categorySelect" class="form-select form-select-lg mb-3">
                <option value="cpuTable">CPU</option>
                <option value="gpuTable">GPU</option>
                <option value="ramTable">RAM</option>
                <option value="casingTable">Casing</option>
                <!-- Add more categories as needed -->
            </select>
        </div>
    </div>

    <!-- Search Box -->
    <div class="row mb-4">
        <div class="col-md-4 offset-md-4">
            <input type="text" id="searchBox" class="form-control form-control-lg" placeholder="Search for products..." autocomplete="off">
            <div id="searchResults" class="mt-2"></div>
        </div>
    </div>
</div>

<script src="../js/script.js"></script>
</body>
</html>
