<?php
session_start();
include '../config/database.php';
include 'includes/auth.php';
checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['merge'])) {
    $primaryProductId = $_POST['primary_product'];
    $productsToMerge = $_POST['products_to_merge'];
    
    try {
        $pdo->beginTransaction();
        
        // Update vendor_prices to point to primary product
        $stmt = $pdo->prepare("
            UPDATE vendor_prices 
            SET productId = :primaryId 
            WHERE productId IN (" . implode(',', $productsToMerge) . ")
        ");
        $stmt->execute([':primaryId' => $primaryProductId]);
        
        // Delete merged products from products table
        $stmt = $pdo->prepare("
            DELETE FROM products 
            WHERE productId IN (" . implode(',', $productsToMerge) . ")
        ");
        $stmt->execute();
        
        // Delete from all_products
        $stmt = $pdo->prepare("
            DELETE FROM all_products 
            WHERE id IN (" . implode(',', $productsToMerge) . ")
        ");
        $stmt->execute();
        
        $pdo->commit();
        $_SESSION['success'] = "Products merged successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error merging products: " . $e->getMessage();
    }
    
    header("Location: products.php");
    exit;
}

// Get all products for selection
$stmt = $pdo->query("
    SELECT p.productId, p.productName, ap.standard_name, c.categoryName
    FROM products p
    JOIN all_products ap ON p.productId = ap.id
    JOIN categories c ON p.categoryId = c.id
    ORDER BY p.productName
");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same header as other pages -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-10 main-content">
                <h2>Merge Products</h2>
                <div class="card mt-4">
                    <div class="card-body">
                        <form method="POST" id="mergeForm">
                            <div class="mb-3">
                                <label>Primary Product (Keep this one)</label>
                                <select name="primary_product" class="form-control" required>
                                    <option value="">Select Primary Product</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['productId']; ?>">
                                            <?php echo htmlspecialchars($product['productName']); ?> 
                                            (<?php echo htmlspecialchars($product['standard_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label>Products to Merge (Will be removed)</label>
                                <select name="products_to_merge[]" class="form-control" multiple required>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['productId']; ?>">
                                            <?php echo htmlspecialchars($product['productName']); ?> 
                                            (<?php echo htmlspecialchars($product['standard_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" name="merge" class="btn btn-primary" onclick="return confirm('Are you sure you want to merge these products? This action cannot be undone.')">
                                Merge Products
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('select').select2({
                width: '100%'
            });
            
            // Prevent selecting same product in both dropdowns
            $('select[name="primary_product"]').on('change', function() {
                let selectedValue = $(this).val();
                $('select[name="products_to_merge[]"] option').prop('disabled', false);
                $('select[name="products_to_merge[]"] option[value="' + selectedValue + '"]').prop('disabled', true);
                $('select[name="products_to_merge[]"]').select2();
            });
        });
    </script>
</body>
</html> 