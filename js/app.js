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
            })
            .catch(error => console.error('Error:', error));
        } else {
            searchResults.innerHTML = '';
        }
    });
});

function redirectToProduct(productId) {
    window.location.href = `../public/product_details.php?id=${productId}`;
}
