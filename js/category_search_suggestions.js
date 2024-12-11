document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('results');
    const categoryId = document.getElementById('category-id').value;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = searchInput.value.trim();
            if (query.length >= 2) { // Trigger search after at least 2 characters
                fetch('/buyCheaper/includes/category_search.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ query, categoryId }) // Send both query and category ID
                })
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = ''; // Clear previous results
                    if (data.message) {
                        searchResults.innerHTML = `<div class="no-results">${data.message}</div>`;
                    } else {
                        data.forEach(product => {
                            const resultDiv = document.createElement('div');
                            resultDiv.className = 'search-result';
                            resultDiv.innerHTML = `
                                <img src="${product.image}" alt="${product.name}" style="width: 50px; height: auto;"> <!-- Adjust image size -->
                                <div class="result-info">
                                    <div class="product-name">${product.name}</div>
                                    <div class="product-price">৳${product.lowestPrice}</div>
                                </div>
                            `;
                            resultDiv.addEventListener('click', () => {
                                window.location.href = `/buyCheaper/public/product_details.php?id=${product.productId}`;
                            });
                            searchResults.appendChild(resultDiv);
                        });
                    }
                    searchResults.style.display = 'block'; // Show results
                })
                .catch(error => console.error('Search error:', error));
            } else {
                searchResults.innerHTML = ''; // Clear results if input is less than 2 characters
                searchResults.style.display = 'none'; // Hide results
            }
        });
    }
});
