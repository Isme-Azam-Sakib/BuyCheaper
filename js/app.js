document.addEventListener('DOMContentLoaded', function () {
    initSearch();
    initComparisonSearch();
});

function initSearch() {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('results');
    if (!searchInput || !searchResults) return;

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query.length > 2) {
            fetch('/buyCheaper/includes/search.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query })
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
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="error">Error loading results</div>';
            });
        } else {
            searchResults.style.display = 'none';
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
}

function initComparisonSearch() {
    document.querySelectorAll('.product-search').forEach(input => {
        const column = input.getAttribute('data-column');
        
        input.addEventListener('input', function() {
            const query = this.value.trim();
            const resultsDiv = document.getElementById(`search-results-${column}`);
            
            if (query.length > 2) {
                fetch('/buyCheaper/includes/search.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        query: query,
                        comparison: 'true'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = '';

                    if (data.message) {
                        resultsDiv.innerHTML = `<div class="no-results">${data.message}</div>`;
                    } else {
                        data.forEach(product => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'search-result-item';
                            resultItem.innerHTML = `
                                <img src="${product.image}" alt="${product.name}">
                                <div class="result-details">
                                    <div class="product-name">${product.name}</div>
                                    <div class="product-price">৳${product.lowestPrice}</div>
                                </div>
                            `;
                            
                            resultItem.addEventListener('click', () => {
                                selectProduct(column, product.productId);
                                resultsDiv.style.display = 'none';
                                input.value = product.name;
                            });
                            
                            resultsDiv.appendChild(resultItem);
                        });
                    }
                    resultsDiv.style.display = 'block';
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
            } else {
                resultsDiv.style.display = 'none';
            }
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            const resultsDiv = document.getElementById(`search-results-${column}`);
            if (!input.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.style.display = 'none';
            }
        });
    });
}

function selectProduct(column, productId) {
    fetch(`/buyCheaper/includes/getProductDetails.php?productId=${productId}`)
        .then(response => response.json())
        .then(product => {
            // Set Product Image
            document.querySelector(`#image-${column}`).innerHTML = `
                <img src="${product.image}" alt="${product.name}">
            `;

            // Set Product Name
            document.querySelector(`#name-${column}`).textContent = product.name;

            // Set Vendor
            document.querySelector(`#vendor-${column}`).innerHTML = `
                <img src="${product.vendor_logo}" alt="${product.vendor_name}">
            `;

            // Set Lowest Price
            document.querySelector(`#price-${column}`).textContent = `৳${product.lowest_price}`;

            // Set Model (remove first word)
            const model = product.name.split(' ').slice(1).join(' ');
            document.querySelector(`#model-${column}`).textContent = model;

            // Set Brand (first word)
            const brand = product.name.split(' ')[0];
            document.querySelector(`#brand-${column}`).textContent = brand;

            // Set Summary
            document.querySelector(`#summary-${column}`).textContent = product.description;
        })
        .catch(error => console.error('Error:', error));
}
