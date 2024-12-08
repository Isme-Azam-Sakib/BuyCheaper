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
            const detailsDiv = document.querySelector(`#column-${column} .product-details`);
            detailsDiv.innerHTML = `
                <div class="selected-product">
                    <img src="${product.image}" alt="${product.name}">
                    <h3>${product.name}</h3>
                    <p class="price">৳${product.lowest_price}</p>
                    <p>${product.description}</p>
                </div>
            `;
        })
        .catch(error => console.error('Error:', error));
}

function initComparisonSearch() {
    document.querySelectorAll('.product-search').forEach(input => {
        input.addEventListener('input', function () {
            const column = this.getAttribute('data-column');
            const query = this.value.trim();
            const resultsDiv = document.querySelector(`#column-${column}-results`);

            if (query.length > 2) {
                fetch('/buyCheaper/includes/search.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ query, comparison: 'true' })
                })
                .then(response => response.json())  // Parse JSON response
                .then(data => {
                    resultsDiv.innerHTML = '';

                    if (data.message) {  // If no results found
                        resultsDiv.innerHTML = `<p>${data.message}</p>`;
                    } else {  // If results found
                        data.forEach(product => {
                            const productDiv = document.createElement('div');
                            productDiv.classList.add('product-result');
                            productDiv.innerHTML = `
                                <img src="${product.image}" alt="${product.name}">
                                <p>${product.name} - ৳${product.lowestPrice}</p>
                            `;
                            productDiv.addEventListener('click', () => {
                                selectProduct(column, product.productId);
                                resultsDiv.innerHTML = ''; // Clear results
                                input.value = product.name; // Update input
                            });
                            resultsDiv.appendChild(productDiv);
                        });
                        resultsDiv.style.display = 'block'; // Show results
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsDiv.innerHTML = '<p>Error loading results</p>';
                });
            } else {
                resultsDiv.innerHTML = '';
                resultsDiv.style.display = 'none';
            }
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target)) {
                const resultsDiv = document.querySelector(`#column-${input.getAttribute('data-column')}-results`);
                if (resultsDiv) {
                    resultsDiv.style.display = 'none';
                }
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
