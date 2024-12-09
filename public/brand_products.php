<?php
include '../config/database.php';
include '../includes/navbar.php';

$brand = isset($_GET['brand']) ? $_GET['brand'] : null;

// Pagination settings
$products_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 24;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// pagination
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT p.productId) as total
    FROM products p
    JOIN vendor_prices vp ON p.productId = vp.productId
    WHERE p.productName LIKE :brand
");
$stmt->execute(['brand' => $brand . '%']);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $products_per_page);

// Update the SQL query to handle sorting
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'vendors';
$order_clause = match ($sort_order) {
    'price-low' => 'MIN(vp.price) ASC',
    'price-high' => 'MIN(vp.price) DESC',
    'name' => 'p.productName ASC',
    'latest' => 'vp.lastUpdated DESC',
    'vendors' => 'vendorCount DESC, vp.lastUpdated DESC',
    default => 'vendorCount DESC, vp.lastUpdated DESC'
};

// Fetch products with pagination and sorting
if ($brand) {
    $stmt = $pdo->prepare("
        SELECT 
            p.productId, 
            p.productName, 
            p.productImage, 
            p.description,
            MIN(vp.price) as lowestPrice,
            COUNT(DISTINCT vp.vendorId) as vendorCount,
            MAX(vp.lastUpdated) as lastUpdated
        FROM products p
        JOIN vendor_prices vp ON p.productId = vp.productId
        WHERE p.productName LIKE :brand
            AND vp.price > 0
        GROUP BY p.productId
        HAVING vendorCount > 0
        ORDER BY {$order_clause}
        LIMIT :offset, :limit
    ");
    $stmt->bindValue(':brand', $brand . '%', PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = [];
}
?>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@100..900&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>
    <div class="container mt-5">
        <div class="category-header">
            <h1><?= htmlspecialchars($brand); ?></h1>
            <p><?= $total_products ?> products available</p>
        </div>

        <div class="category-filters">
            <div class="products-per-page">
                <label>Show:</label>
                <select id="items-per-page">
                    <option value="12" <?= $products_per_page == 12 ? 'selected' : '' ?>>12</option>
                    <option value="36" <?= $products_per_page == 36 ? 'selected' : '' ?>>36</option>
                    <option value="72" <?= $products_per_page == 72 ? 'selected' : '' ?>>72</option>
                    <option value="108" <?= $products_per_page == 108 ? 'selected' : '' ?>>108</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Sort by:</label>
                <select id="sort-products">
                    <option value="vendors" <?= $sort_order === 'vendors' ? 'selected' : '' ?>>Most Vendors</option>
                    <option value="latest" <?= $sort_order === 'latest' ? 'selected' : '' ?>>Latest</option>
                    <option value="price-low" <?= $sort_order === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price-high" <?= $sort_order === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name" <?= $sort_order === 'name' ? 'selected' : '' ?>>Name</option>
                </select>
            </div>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= !empty($product['productImage']) ? htmlspecialchars($product['productImage']) : '/buyCheaper/images/no-image.png' ?>"
                                alt="<?= htmlspecialchars($product['productName']); ?>">
                            <?php if ($product['vendorCount'] > 1): ?>
                                <span class="vendor-count"><?= $product['vendorCount'] ?> vendors</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="product-footer">
                                <div class="price">
                                    <span class="label">Starting from</span>
                                    <span class="amount">৳<?= number_format($product['lowestPrice']); ?></span>
                                </div>
                                <a href="product_details.php?id=<?= $product['productId']; ?>"
                                    class="compare-prices-btn">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?brand=<?= urlencode($brand) ?>&page=<?= $current_page - 1 ?>&per_page=<?= $products_per_page ?>&sort=<?= $sort_order ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    if ($end_page - $start_page < 4) {
                        $start_page = max(1, $end_page - 4);
                    }
                    ?>

                    <?php if ($start_page > 1): ?>
                        <a href="?brand=<?= urlencode($brand) ?>&page=1&per_page=<?= $products_per_page ?>&sort=<?= $sort_order ?>" class="page-link">1</a>
                        <?php if ($start_page > 2): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?brand=<?= urlencode($brand) ?>&page=<?= $i ?>&per_page=<?= $products_per_page ?>&sort=<?= $sort_order ?>"
                            class="page-link <?= $i === $current_page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                        <a href="?brand=<?= urlencode($brand) ?>&page=<?= $total_pages ?>&per_page=<?= $products_per_page ?>&sort=<?= $sort_order ?>" class="page-link"><?= $total_pages ?></a>
                    <?php endif; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?brand=<?= urlencode($brand) ?>&page=<?= $current_page + 1 ?>&per_page=<?= $products_per_page ?>&sort=<?= $sort_order ?>" class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <p>No products found for this brand.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle items per page change
            const itemsPerPage = document.getElementById('items-per-page');
            if (itemsPerPage) {
                itemsPerPage.addEventListener('change', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('per_page', this.value);
                    // Preserve the sort order and brand
                    const sort = urlParams.get('sort');
                    if (sort) {
                        urlParams.set('sort', sort);
                    }
                    const brand = urlParams.get('brand');
                    if (brand) {
                        urlParams.set('brand', brand);
                    }
                    urlParams.delete('page'); // Reset to first page
                    window.location.href = window.location.pathname + '?' + urlParams.toString();
                });
            }

            // Handle sort change
            const sortSelect = document.getElementById('sort-products');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('sort', this.value);
                    // Preserve the brand and items per page
                    const brand = urlParams.get('brand');
                    if (brand) {
                        urlParams.set('brand', brand);
                    }
                    const perPage = urlParams.get('per_page');
                    if (perPage) {
                        urlParams.set('per_page', perPage);
                    }
                    urlParams.delete('page'); // Reset to first page
                    window.location.href = window.location.pathname + '?' + urlParams.toString();
                });
            }
        });
    </script>
</body>