document.addEventListener('DOMContentLoaded', function () {
    initSearch();
    initComparisonSearch();
    initCarousel();
    initFilters();
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

            // Set Lowest Price and Store Button
            document.querySelector(`#price-${column}`).textContent = `৳${product.lowest_price}`;
            
            // Update store button with the correct URL
            const storeButton = document.querySelector(`#store-button-${column}`);
            if (product.vendor_url) {
                storeButton.innerHTML = `
                    <a href="${product.vendor_url}" target="_blank" class="visit-store-btn">Visit Store</a>
                `;
            } else {
                storeButton.innerHTML = ''; // Clear button if no URL available
            }

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

function initCarousel() {
    const track = document.querySelector('.carousel-track');
    if (!track) return; // Exit if no carousel exists

    const items = document.querySelectorAll('.carousel-item');
    const leftArrow = document.querySelector('.left-arrow');
    const rightArrow = document.querySelector('.right-arrow');
    
    let currentIndex = 0;
    const itemWidth = 330; // 300px width + 30px margin
    const maxIndex = Math.max(0, items.length - Math.floor(track.offsetWidth / itemWidth));

    function updateCarousel() {
        const offset = -currentIndex * itemWidth;
        track.style.transform = `translateX(${offset}px)`;
        
        // Update arrow visibility
        leftArrow.style.opacity = currentIndex === 0 ? '0.5' : '1';
        rightArrow.style.opacity = currentIndex === maxIndex ? '0.5' : '1';
    }

    rightArrow.addEventListener('click', () => {
        if (currentIndex < maxIndex) {
            currentIndex++;
            updateCarousel();
        }
    });

    leftArrow.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    });

    // Initial setup
    updateCarousel();

    // Update on window resize
    window.addEventListener('resize', () => {
        const newMaxIndex = Math.max(0, items.length - Math.floor(track.offsetWidth / itemWidth));
        if (currentIndex > newMaxIndex) {
            currentIndex = newMaxIndex;
        }
        updateCarousel();
    });
}

function initFilters() {
    // Handle items per page change
    const itemsPerPage = document.getElementById('items-per-page');
    if (itemsPerPage) {
        itemsPerPage.addEventListener('change', function() {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('per_page', this.value);
            urlParams.delete('page'); // Reset to first page when changing items per page
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        });
    }

    // Handle sort change
    const sortSelect = document.getElementById('sort-products');
    if (sortSelect) {
        // Set initial value from URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('sort')) {
            sortSelect.value = urlParams.get('sort');
        }

        sortSelect.addEventListener('change', function() {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', this.value);
            urlParams.delete('page'); // Reset to first page when changing sort
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        });
    }
}
