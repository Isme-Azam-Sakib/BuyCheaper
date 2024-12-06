<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Builder</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="pc-builder">
        <h1>Build Your PC</h1>

        <div class="component">
            <label for="cpu">CPU:</label>
            <select id="cpu" class="component-select" data-category="1">
                <option value="">Select a CPU</option>
            </select>
            <span class="price" id="cpu-price">0</span>
        </div>

        <div class="component">
            <label for="gpu">GPU:</label>
            <select id="gpu" class="component-select" data-category="2">
                <option value="">Select a GPU</option>
            </select>
            <span class="price" id="gpu-price">0</span>
        </div>

        <!-- Add similar blocks for RAM, PSU, etc. -->

        <div class="total-cost">
            <h2>Total Cost: <span id="total-cost">0</span></h2>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.component-select').each(function() {
                const selectElement = $(this);
                const categoryId = selectElement.data('category');

                // Fetch components for the category
                $.get('../includes/fetch_components.php', { categoryId: categoryId }, function(data) {
                    const products = JSON.parse(data);

                    products.forEach(product => {
                        selectElement.append(`<option value="${product.productId}" data-price="${product.price}">${product.productName}</option>`);
                    });
                });
            });

            // Update price and total cost when an option is selected
            $('.component-select').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const price = parseFloat(selectedOption.data('price')) || 0;

                // Update individual component price
                const priceElementId = `#${$(this).attr('id')}-price`;
                $(priceElementId).text(price);

                // Update total cost
                let totalCost = 0;
                $('.price').each(function() {
                    totalCost += parseFloat($(this).text()) || 0;
                });
                $('#total-cost').text(totalCost);
            });
        });
    </script>
</body>
</html>
