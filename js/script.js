document.getElementById('searchBox').addEventListener('input', function () {
    let searchTerm = this.value;
    let category = document.getElementById('categorySelect').value;

    if (searchTerm.length > 2) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', '../includes/search.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (this.status === 200) {
                let results = JSON.parse(this.responseText);
                let output = '';

                if (results.length > 0) {
                    results.forEach(function (product) {
                        output += `<div class="result-item" data-productid="${product.productId}">
                                     <img src="${product.productImage}" alt="${product.productName}" style="width:50px; height:50px;">
                                     <h4>${product.productName}</h4>
                                     <p>${product.description}</p>
                                   </div>`;
                    });
                } else {
                    output = '<p>No matching products found</p>';
                }

                document.getElementById('searchResults').innerHTML = output;

                document.querySelectorAll('.result-item').forEach(function (item) {
                    item.addEventListener('click', function () {
                        let productId = this.getAttribute('data-productid');
                        window.location.href = `compare.php?productId=${productId}&category=${category}`;
                    });
                });
            }
        };

        xhr.send('category=' + category + '&searchTerm=' + searchTerm);
    } else {
        document.getElementById('searchResults').innerHTML = '';
    }
});
