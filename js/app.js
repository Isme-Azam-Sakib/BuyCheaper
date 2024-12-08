document.addEventListener('DOMContentLoaded', function () {
    initSearch();
    initShowMoreButton();
    initComparisonSearch();
    initGlobalProductSearch();
});

function redirectToProduct(productId) {
    window.location.href = `/buyCheaper/public/product_details.php?id=${productId}`;
}

function initSearch() {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('results');
    if (!searchInput || !searchResults) return;


    searchResults.classList.add('hidden');

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query !== '') {
            fetch('/buyCheaper/includes/search.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query })
            })
            .then(response => response.text())
            .then(data => {
                searchResults.innerHTML = data;

                // Toggle visibility based on content
                if (data.trim() !== '') {
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.classList.add('hidden');
                }

                // Add "View More" button if not already present
                if (document.querySelector('.view-more') === null && data.trim() !== '') {
                    const viewMoreButton = document.createElement('div');
                    viewMoreButton.className = 'view-more';
                    viewMoreButton.textContent = 'View More';
                    viewMoreButton.onclick = () => viewMore(query);
                    searchResults.appendChild(viewMoreButton);
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden'); // Hide when query is empty
        }
    });

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            const query = searchInput.value.trim();
            if (query) viewMore(query);
        }
    });
}


function initShowMoreButton() {
    const showMoreBtn = document.getElementById('show-more-btn');
    const hiddenBrands = document.querySelectorAll('.hidden-brand');

    if (!showMoreBtn || hiddenBrands.length === 0) return;

    showMoreBtn.addEventListener('click', () => {
        hiddenBrands.forEach(brand => brand.classList.toggle('hidden-brand'));
        showMoreBtn.innerHTML = showMoreBtn.innerHTML.includes('Show More') 
            ? 'Show Less <span>&#9650;</span>' 
            : 'Show More <span>&#9660;</span>';
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

function initComparisonSearch() {
    document.querySelectorAll('.product-search').forEach(input => {
        const column = input.getAttribute('data-column');
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'search-results-container';
        resultsContainer.id = `search-results-${column}`;
        input.parentNode.appendChild(resultsContainer);

        input.addEventListener('input', function() {
            const query = this.value.trim();
            
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
                    const resultsDiv = document.getElementById(`search-results-${column}`);
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
                                resultsDiv.innerHTML = '';
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
                document.getElementById(`search-results-${column}`).style.display = 'none';
            }
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !document.getElementById(`search-results-${column}`).contains(e.target)) {
                document.getElementById(`search-results-${column}`).style.display = 'none';
            }
        });
    });
}

function initGlobalProductSearch() {
    const searchInputs = document.querySelectorAll('.comparison-search-input');

    searchInputs.forEach(input => {
        input.addEventListener('input', function () {
            const searchInput = this.value.trim();
            if (searchInput.length > 0) {
                fetch('/buyCheaper/includes/search.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ query: searchInput, comparison: 'true' })
                })
                .then(response => response.json())
                .then(response => displayComparisonResults(response, this))
                .catch(error => console.error('Error:', error));
            }
        });
    });
}
