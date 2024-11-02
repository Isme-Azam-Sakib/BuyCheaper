document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('results');

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query !== '') {
            fetch('../includes/search.php', { 
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
    window.location.href = `search_results.php?query=${encodeURIComponent(query)}`;
}

// Function to redirect to the product details page when a search result is clicked
function redirectToProduct(productId) {
    window.location.href = `../public/product_details.php?id=${productId}`;
}
