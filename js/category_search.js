document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('category-search');
    const searchResults = document.getElementById('results');
    const searchButton = document.getElementById('search-button');
    const categoryId = document.getElementById('category-id').value; // Assuming a hidden input with the category ID

    if (searchButton) {
        searchButton.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length > 0) {
                fetch('/buyCheaper/includes/category_search.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ query, categoryId })
                })
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.message) {
                        searchResults.innerHTML = `<div class="no-results">${data.message}</div>`;
                    } else {
                        data.forEach(product => {
                            const resultDiv = document.createElement('div');
                            resultDiv.className = 'search-result';
                            resultDiv.innerHTML = `
                                <img src="${product.image}" alt="${product.name}">
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
                    searchResults.style.display = 'block';
                })
                .catch(error => console.error('Search error:', error));
            }
        });
    }

    searchInput.addEventListener('input', function() {
        const query = searchInput.value.trim();
        if (query.length > 2) {
            fetch('/buyCheaper/includes/category_search.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query, categoryId })
            })
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data.message) {
                    searchResults.innerHTML = `<div class="no-results">${data.message}</div>`;
                } else {
                    data.forEach(product => {
                        const resultDiv = document.createElement('div');
                        resultDiv.className = 'search-result';
                        resultDiv.innerHTML = `
                            <img src="${product.image}" alt="${product.name}">
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
                searchResults.style.display = 'block';
            })
            .catch(error => console.error('Search error:', error));
        } else {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
        }
    });
});
