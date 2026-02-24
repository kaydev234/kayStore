function searchProducts() {
    let name = document.getElementById("searchInput").value;
    let category = document.getElementById("categorySelect").value;

    fetch(`api/search_products.php?name=${encodeURIComponent(name)}&category=${encodeURIComponent(category)}`)
        .then(res => res.json())
        .then(data => {
            let grid = document.getElementById("productGrid");
            grid.innerHTML = "";

            if (data.count === 0) {
                grid.innerHTML = `<div class="col-12 text-center"><p class="text-muted">No products found.</p></div>`;
                return;
            }

            data.data.forEach(product => {
                let card = `
                    <div class="col-md-3 mb-4">
                        <div class="product-card text-center d-flex flex-column justify-content-between">
                            <div>
                                <img src="product_images/${product.image_path}" class="img-fluid" alt="">
                                <h5>${product.name}</h5>
                                <p class="text-success fw-bold">₦${parseFloat(product.price).toFixed(2)}</p>
                                <p class="text-muted small">${product.category}</p>
                            </div>
                            <div class="mt-2">
                                <a href="add_to_cart.php?id=${product.id}" class="btn btn-primary btn-sm me-2">Add to Cart</a>
                                <a href="product_view.php?id=${product.id}" class="btn btn-outline-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                `;
                grid.innerHTML += card;
            });
        })
        .catch(err => {
            console.error(err);
        });
}

// Load all products on first page load
window.onload = searchProducts;
