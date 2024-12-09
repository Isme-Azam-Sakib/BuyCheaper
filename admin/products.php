<?php
session_start();
include '../config/database.php';
include 'includes/auth.php';
checkAdminAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin Panel</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Product Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>

                <!-- Filters Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
                            </div>
                            <div class="col-md-3">
                                <select id="categoryFilter" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT categoryId, categoryName FROM categories ORDER BY categoryName");
                                    while ($category = $stmt->fetch()) {
                                        echo "<option value='{$category['categoryId']}'>" . 
                                             htmlspecialchars($category['categoryName']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="vendorFilter" class="form-select">
                                    <option value="">All Vendors</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT vendorId, vendorName FROM vendors ORDER BY vendorName");
                                    while ($vendor = $stmt->fetch()) {
                                        echo "<option value='{$vendor['vendorId']}'>" . 
                                             htmlspecialchars($vendor['vendorName']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="pageSizeFilter" class="form-select">
                                    <option value="20">20 per page</option>
                                    <option value="50" selected>50 per page</option>
                                    <option value="100">100 per page</option>
                                    <option value="all">All products</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button id="resetFilters" class="btn btn-secondary w-100">
                                    <i class="fas fa-undo"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add this above your filters section -->
                <div class="bulk-actions mb-3">
                    <div class="d-flex gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                        <select id="bulkActionSelect" class="form-select w-auto" disabled>
                            <option value="">Bulk Actions</option>
                            <option value="scrapeImages">Update Images from Vendor</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button id="applyBulkAction" class="btn btn-primary" disabled>Apply</button>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="select-all-rows"></th>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Vendors</th>
                                        <th>Price Range</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    <!-- Products will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div id="pagination" class="d-flex justify-content-center mt-3">
                            <!-- Pagination will be loaded here -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="productName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="categoryId" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php
                                $stmt = $pdo->query("SELECT categoryId, categoryName FROM categories ORDER BY categoryName");
                                while ($category = $stmt->fetch()) {
                                    echo "<option value='{$category['categoryId']}'>" . 
                                         htmlspecialchars($category['categoryName']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" name="productImage" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="editProductModal" aria-labelledby="editProductModalLabel" style="width: 700px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="editProductModalLabel">Edit Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="editProductForm">
                <input type="hidden" id="editProductId" name="productId">
                
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" id="editProductName" name="productName" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select id="editCategoryId" name="categoryId" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php
                        $stmt = $pdo->query("SELECT categoryId, categoryName FROM categories ORDER BY categoryName");
                        while ($category = $stmt->fetch()) {
                            echo "<option value='{$category['categoryId']}'>" . 
                                 htmlspecialchars($category['categoryName']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea id="editDescription" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <div class="d-flex align-items-center mb-2">
                        <img id="editCurrentImage" src="" alt="Current Product Image" 
                             class="img-thumbnail me-3" style="max-width: 200px;">
                        <button type="button" id="getImageBtn" class="btn btn-info">
                            <i class="fas fa-download me-1"></i> Get Image from Vendor
                        </button>
                    </div>
                    <input type="file" id="editProductImage" name="productImage" class="form-control mt-2">
                    <small class="text-muted">Upload manually or get image from vendor</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Vendor Prices</label>
                    <div class="table-responsive vendor-prices-table">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Price</th>
                                    <th>URL</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody id="vendorPricesBody">
                                <!-- Vendor prices will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
        <div class="offcanvas-footer p-3 border-top">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveEditProduct()">Save Changes</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/products.js"></script>
</body>
</html> 