document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('results');

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query !== '') {
            fetch('/buyCheaper/includes/search.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ query })
            })
            .then(response => response.text())
            .then(data => {
                searchResults.innerHTML = data;

                // Add "View More" button if not already present
                if (document.querySelector('.view-more') === null && data.trim() !== '') {
                    const viewMoreButton = document.createElement('div');
                    viewMoreButton.className = 'view-more';
                    viewMoreButton.textContent = 'View More';
                    viewMoreButton.onclick = function () {
                        viewMore(query);
                    };
                    searchResults.appendChild(viewMoreButton);
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            searchResults.innerHTML = '';
        }
    });

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            const query = searchInput.value.trim();
            if (query) {
                viewMore(query);
            }
        }
    });
});

// Function to handle "View More" button click
function viewMore(query) {
    window.location.href = `/buyCheaper/public/search_results.php?query=${encodeURIComponent(query)}`; 
}

function redirectToProduct(productId) {
    window.location.href = `/buyCheaper/public/product_details.php?id=${productId}`; 
}

document.querySelectorAll('.product-search').forEach(input => {
    input.addEventListener('input', function () {
        const column = this.getAttribute('data-column');
        const query = this.value.trim();

        if (query.length > 2) { // Trigger search after 3 characters
            fetch('/buyCheaper/includes/search.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query, comparison: 'true' })
            })
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.querySelector(`#column-${column} .product-details`);
                resultsDiv.innerHTML = '';

                if (data.message) {
                    resultsDiv.innerHTML = `<p>${data.message}</p>`;
                } else {
                    data.forEach(product => {
                        const productDiv = document.createElement('div');
                        productDiv.classList.add('product-result');
                        productDiv.innerHTML = `
                            <img src="${product.image}" alt="${product.name}">
                            <p>${product.name} - ৳${product.lowestPrice}</p>
                        `;
                        productDiv.addEventListener('click', () => selectProduct(column, product.productId));
                        resultsDiv.appendChild(productDiv);
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            document.querySelector(`#column-${column} .product-details`).innerHTML = '';
        }
    });
});


function selectProduct(column, productId) {
    fetch(`/buyCheaper/includes/getProductDetails.php?productId=${productId}`)
        .then(response => response.json())
        .then(product => {
            const detailsDiv = document.querySelector(`#column-${column} .product-details`);
            detailsDiv.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <h3>${product.name}</h3>
                <p>${product.description}</p>
                <p>Lowest Price: ৳${product.lowest_price}</p>
            `;
        })
        .catch(error => console.error('Error:', error));
}


document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = document.querySelectorAll('.comparison-search-input'); // Ensure your input fields have this class

    searchInputs.forEach(input => {
        input.addEventListener('input', function () {
            const searchInput = this.value.trim();
            if (searchInput.length > 0) {
                $.ajax({
                    url: '/buyCheaper/includes/search.php',
                    method: 'POST',
                    data: { query: searchInput, comparison: 'true' },
                    success: function (response) {
                        // Call a function to display the search results
                        displayComparisonResults(response, input);
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                    },
                });
            }
        });
    });
});

// Define displayComparisonResults to render the results dynamically
function displayComparisonResults(response, inputField) {
    const dropdown = inputField.nextElementSibling; // Assume a dropdown for search suggestions
    dropdown.innerHTML = '';

    if (response.message) {
        dropdown.innerHTML = `<p>${response.message}</p>`;
    } else {
        response.forEach(product => {
            const productItem = document.createElement('div');
            productItem.classList.add('search-result-item');
            productItem.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <span>${product.name} - ৳${product.lowestPrice}</span>
            `;
            productItem.addEventListener('click', function () {
                selectProductForComparison(product, inputField);
            });
            dropdown.appendChild(productItem);
        });
    }
}

function selectProductForComparison(product, inputField) {
    const column = inputField.closest('.comparison-column');
    column.querySelector('.product-image').src = product.image;
    column.querySelector('.product-description').innerText = product.name;
    column.querySelector('.product-price').innerText = `৳${product.lowestPrice}`;
}
