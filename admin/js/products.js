// Function to update products table
function updateProductsTable(products) {
    const tbody = $('#productsTableBody');
    tbody.empty();
    
    products.forEach(product => {
        const row = `
            <tr>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input product-select" value="${product.productId}">
                    </div>
                </td>
                <td>${product.productId}</td>
                <td>
                    <img src="${product.productImage}" alt="${product.productName}" class="product-thumb" style="max-width: 50px;">
                </td>
                <td>${product.productName}</td>
                <td>${product.categoryName}</td>
                <td>${product.vendorCount}</td>
                <td>${product.priceRange}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="handleEdit(${product.productId})">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    updateBulkActionControls();
}

// Wrapper functions for edit and delete
function handleEdit(productId) {
    // Initialize offcanvas
    const offcanvas = new bootstrap.Offcanvas('#editProductModal');
    
    // Show loading state
    $('#editProductForm').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
    
    // Show the offcanvas first
    offcanvas.show();
    
    // Load product details
    $.ajax({
        url: 'includes/get_product.php',
        type: 'POST',
        data: { productId: productId },
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response); // Debug log
            
            if (response.error) {
                alert('Error: ' + response.message);
                return;
            }

            const product = response.data;
            
            // Restore form HTML
            $('#editProductForm').html(`
                <input type="hidden" id="editProductId" name="productId" value="${product.productId}">
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="editProductName" name="productName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-control" id="editCategoryId" name="categoryId" required>
                        <!-- Categories will be populated dynamically -->
                    </select>
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
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Vendor Prices</label>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Price</th>
                                    <th>URL</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody id="vendorPricesBody"></tbody>
                        </table>
                    </div>
                </div>
            `);

            // Fill form fields
            $('#editProductName').val(product.productName);
            $('#editCategoryId').val(product.categoryId);
            $('#editDescription').val(product.description);
            $('#editCurrentImage').attr('src', product.productImage);
            
            // Update vendor prices table
            const vendorPricesBody = $('#vendorPricesBody');
            if (product.vendorPrices && product.vendorPrices.length > 0) {
                product.vendorPrices.forEach(price => {
                    const row = `
                        <tr>
                            <td>${price.vendorName}</td>
                            <td>৳${price.price}</td>
                            <td>
                                <a href="${price.productUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Visit
                                </a>
                            </td>
                            <td>${new Date(price.lastUpdated).toLocaleString()}</td>
                        </tr>
                    `;
                    vendorPricesBody.append(row);
                });
            } else {
                vendorPricesBody.html('<tr><td colspan="4" class="text-center">No vendor prices available</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error); // Debug log
            alert('Failed to load product details');
        }
    });
}

function handleDelete(productId) {
    console.log('Delete clicked for product:', productId);
    deleteProduct(productId);
    return false;
}

// Function to load products
function loadProducts(page = 1) {
    const pageSize = $('#pageSizeFilter').val();
    const filters = {
        search: $('#searchInput').val(),
        category: $('#categoryFilter').val(),
        vendor: $('#vendorFilter').val(),
        page: page,
        perPage: pageSize === 'all' ? 'all' : parseInt(pageSize)
    };

    // Show loading indicator
    $('#productsTableBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

    $.ajax({
        url: 'includes/fetch_filtered_products.php',
        type: 'POST',
        data: filters,
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            if (response.error) {
                console.error('Server error:', response.message);
                $('#productsTableBody').html(`
                    <tr><td colspan="7" class="text-center text-danger">Error: ${response.message}</td></tr>
                `);
                return;
            }
            updateProductsTable(response.products);
            if (pageSize !== 'all') {
                updatePagination(response.totalPages, response.currentPage);
            } else {
                $('#pagination').empty(); // Hide pagination for "All" option
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#productsTableBody').html(`
                <tr><td colspan="7" class="text-center text-danger">Failed to load products</td></tr>
            `);
        }
    });
}

// Function to update pagination
function updatePagination(totalPages, currentPage) {
    const pagination = $('#pagination');
    pagination.empty();

    if (totalPages > 1) {
        // Add Previous button
        const prevButton = $('<button>')
            .addClass('btn btn-sm mx-1 btn-outline-primary')
            .html('<i class="fas fa-chevron-left"></i>')
            .prop('disabled', currentPage === 1)
            .click(() => loadProducts(currentPage - 1));
        pagination.append(prevButton);

        // Add page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        // Adjust start page if we're near the end
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        // Add first page and ellipsis if necessary
        if (startPage > 1) {
            pagination.append(
                $('<button>')
                    .addClass('btn btn-sm mx-1')
                    .addClass(1 === currentPage ? 'btn-primary' : 'btn-outline-primary')
                    .text('1')
                    .click(() => loadProducts(1))
            );
            if (startPage > 2) {
                pagination.append('<span class="mx-2">...</span>');
            }
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            const button = $('<button>')
                .addClass('btn btn-sm mx-1')
                .addClass(i === currentPage ? 'btn-primary' : 'btn-outline-primary')
                .text(i)
                .click(() => loadProducts(i));
            pagination.append(button);
        }

        // Add last page and ellipsis if necessary
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.append('<span class="mx-2">...</span>');
            }
            pagination.append(
                $('<button>')
                    .addClass('btn btn-sm mx-1')
                    .addClass(totalPages === currentPage ? 'btn-primary' : 'btn-outline-primary')
                    .text(totalPages)
                    .click(() => loadProducts(totalPages))
            );
        }

        // Add Next button
        const nextButton = $('<button>')
            .addClass('btn btn-sm mx-1 btn-outline-primary')
            .html('<i class="fas fa-chevron-right"></i>')
            .prop('disabled', currentPage === totalPages)
            .click(() => loadProducts(currentPage + 1));
        pagination.append(nextButton);

        // Add page info
        const pageInfo = $('<span>')
            .addClass('ms-3 text-muted')
            .text(`Page ${currentPage} of ${totalPages}`);
        pagination.append(pageInfo);
    }
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to edit product
function editProduct(productId) {
    console.log('Editing product:', productId);
    // Show modal first
    const modal = $('#editProductModal');
    modal.modal('show');
    
    // Store form content
    const formContent = modal.find('.modal-body').html();
    
    // Show loading state
    modal.find('.modal-body').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Loading...</div></div>');

    // Fetch product details
    $.ajax({
        url: 'includes/get_product.php',
        type: 'POST',
        data: { productId: productId },
        dataType: 'json',
        success: function(response) {
            console.log('Got product data:', response);
            // Restore form content first
            modal.find('.modal-body').html(formContent);
            
            if (response.error) {
                alert('Error loading product: ' + response.message);
                modal.modal('hide');
                return;
            }

            // Populate form with product details
            $('#editProductId').val(response.productId);
            $('#editProductName').val(response.productName);
            $('#editCategoryId').val(response.categoryId);
            $('#editDescription').val(response.description);
            
            // Handle image
            if (response.productImage) {
                $('#editCurrentImage').attr('src', response.productImage).show();
            } else {
                $('#editCurrentImage').hide();
            }

            // Populate vendor prices
            const vendorPricesBody = $('#vendorPricesBody');
            vendorPricesBody.empty();

            if (response.vendorPrices && response.vendorPrices.length > 0) {
                response.vendorPrices.forEach(price => {
                    const row = `
                        <tr>
                            <td>${price.vendorName}</td>
                            <td>৳${parseFloat(price.price).toLocaleString()}</td>
                            <td>
                                <a href="${price.productUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Visit
                                </a>
                            </td>
                            <td>${new Date(price.lastUpdated).toLocaleString()}</td>
                        </tr>
                    `;
                    vendorPricesBody.append(row);
                });
            } else {
                vendorPricesBody.html('<tr><td colspan="4" class="text-center">No vendor prices available</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            // Restore form content
            modal.find('.modal-body').html(formContent);
            alert('Failed to load product details');
        }
    });
}

// Function to delete product
function deleteProduct(productId) {
    console.log('Deleting product:', productId);
    if (confirm('Are you sure you want to delete this product?')) {
        // Implement delete functionality
        console.log('Delete confirmed for product:', productId);
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Debug log
    console.log('Script loaded');
    
    // Use event delegation since modal content might be dynamic
    $(document).on('click', '#getImageBtn', function(e) {
        e.preventDefault();
        
        const productId = $('#editProductId').val();
        if (!productId) {
            alert('Product ID not found');
            return;
        }

        const btn = $(this);
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Getting Image...').prop('disabled', true);

        // Remove any existing alerts
        $('.alert').remove();

        // Get StarTech vendor row if exists
        const starTechRow = $('#vendorPricesBody tr').filter(function() {
            return $(this).find('td:first').text().trim().toLowerCase().includes('startech');
        });

        if (starTechRow.length === 0) {
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    No StarTech listing found for this product
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            $('#editCurrentImage').after(errorAlert);
            btn.html(originalText).prop('disabled', false);
            return;
        }

        $.ajax({
            url: 'includes/scrapers/StarTechScraper.php',
            type: 'POST',
            data: { productId: productId },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    $('#editCurrentImage').after(errorAlert);
                    return;
                }

                // Update the image preview
                $('#editCurrentImage').attr('src', response.imagePath);
                
                // Show success message
                const successAlert = `
                    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                        Image updated successfully from StarTech!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                $('#editCurrentImage').after(successAlert);
            },
            error: function(xhr, status, error) {
                const errorAlert = `
                    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                        Failed to get image: ${error}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                $('#editCurrentImage').after(errorAlert);
            },
            complete: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Event listeners for filters
    $('#searchInput').on('input', debounce(() => loadProducts(), 300));
    $('#categoryFilter, #vendorFilter').on('change', () => loadProducts());
    $('#pageSizeFilter').on('change', () => loadProducts(1));
    
    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#categoryFilter').val('');
        $('#vendorFilter').val('');
        $('#pageSizeFilter').val('50');
        loadProducts();
    });

    // Debug log
    console.log('Initializing checkbox handlers');
    
    // Handle select all checkbox
    $(document).on('change', '#selectAll', function() {
        console.log('Select all clicked:', $(this).prop('checked'));
        const isChecked = $(this).prop('checked');
        $('.product-select').prop('checked', isChecked);
        updateBulkActionControls();
    });

    // Handle individual checkboxes
    $(document).on('change', '.product-select', function(e) {
        console.log('Individual checkbox clicked:', $(this).val());
        e.stopPropagation();
        updateBulkActionControls();
    });

    // Initial load
    loadProducts();

    // Function to save edited product
    window.saveEditProduct = function() {
        const form = $('#editProductForm')[0];
        const formData = new FormData(form);

        $.ajax({
            url: 'includes/update_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    alert('Error updating product: ' + response.message);
                    return;
                }
                
                // Show success toast
                const toast = `
                    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1070">
                        <div class="toast show bg-success text-white" role="alert">
                            <div class="toast-body">
                                Product updated successfully!
                            </div>
                        </div>
                    </div>`;
                $('body').append(toast);
                
                // Close offcanvas and remove toast after delay
                const offcanvas = bootstrap.Offcanvas.getInstance('#editProductModal');
                offcanvas.hide();
                setTimeout(() => {
                    $('.toast').remove();
                }, 3000);
                
                loadProducts(); // Reload products table
            },
            error: function() {
                alert('Failed to update product');
            }
        });
    };

    // Handle bulk action button
    $('#applyBulkAction').click(function() {
        const action = $('#bulkActionSelect').val();
        const selectedProducts = $('.product-select:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedProducts.length === 0) {
            alert('Please select products first');
            return;
        }

        switch (action) {
            case 'scrapeImages':
                bulkUpdateImages(selectedProducts);
                break;
            case 'delete':
                bulkDeleteProducts(selectedProducts);
                break;
            default:
                alert('Please select an action');
        }
    });
});

function updateBulkActionControls() {
    const selectedCount = $('.product-select:checked').length;
    const totalCheckboxes = $('.product-select').length;
    
    console.log('Selected:', selectedCount, 'Total:', totalCheckboxes);
    
    $('#bulkActionSelect, #applyBulkAction').prop('disabled', selectedCount === 0);
    
    // Update select all checkbox state
    const selectAllCheckbox = $('#selectAll');
    
    if (selectedCount === 0) {
        selectAllCheckbox.prop('checked', false).prop('indeterminate', false);
    } else if (selectedCount === totalCheckboxes) {
        selectAllCheckbox.prop('checked', true).prop('indeterminate', false);
    } else {
        selectAllCheckbox.prop('checked', false).prop('indeterminate', true);
    }
}

// Make functions globally available
window.handleEdit = handleEdit;
window.handleDelete = handleDelete;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;

function bulkUpdateImages(productIds) {
    if (!confirm(`Update images for ${productIds.length} products?`)) {
        return;
    }

    let completed = 0;
    let successful = 0;
    const total = productIds.length;

    // Show progress alert
    const progressAlert = `
        <div class="alert alert-info" id="bulkProgressAlert">
            <i class="fas fa-spinner fa-spin"></i> Updating images... (0/${total})
        </div>`;
    $('.bulk-actions').after(progressAlert);

    productIds.forEach(productId => {
        $.ajax({
            url: 'includes/scrapers/StarTechScraper.php',
            type: 'POST',
            data: { productId: productId },
            dataType: 'json',
            success: function(response) {
                completed++;
                if (!response.error) {
                    successful++;
                }
                updateProgressAlert(completed, successful, total);
            },
            error: function() {
                completed++;
                updateProgressAlert(completed, successful, total);
            }
        });
    });
}

function bulkDeleteProducts(productIds) {
    if (!confirm(`Delete ${productIds.length} products?`)) {
        return;
    }

    $.ajax({
        url: 'includes/bulk_delete_products.php',
        type: 'POST',
        data: { productIds: productIds },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                alert('Error: ' + response.message);
                return;
            }
            alert('Products deleted successfully');
            loadProducts(); // Reload the table
        },
        error: function() {
            alert('Failed to delete products');
        }
    });
}

function updateProgressAlert(completed, successful, total) {
    $('#bulkProgressAlert').html(`
        <i class="fas fa-spinner fa-spin"></i> 
        Updating images... (${completed}/${total}) 
        <br>
        Successfully updated: ${successful}
    `);

    if (completed === total) {
        setTimeout(() => {
            $('#bulkProgressAlert').removeClass('alert-info').addClass('alert-success').html(`
                <i class="fas fa-check"></i> 
                Completed! Successfully updated ${successful} out of ${total} images.
            `);
            loadProducts(); // Reload the table to show updated images
            
            // Remove the alert after 5 seconds
            setTimeout(() => {
                $('#bulkProgressAlert').fadeOut(() => {
                    $('#bulkProgressAlert').remove();
                });
            }, 5000);
        }, 500);
    }
} 