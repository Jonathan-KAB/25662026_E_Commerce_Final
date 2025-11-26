/**
 * Product Display and Search JavaScript
 * Handles dynamic product loading, search, filtering, and AJAX interactions
 */

// Debounce function to limit API calls during typing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Show search suggestions as user types
function showSearchSuggestions(query) {
    if (query.length < 2) {
        document.getElementById('searchSuggestions')?.remove();
        return;
    }
    
    // Make AJAX call to get suggestions
    fetch(`../actions/product_actions.php?action=search&query=${encodeURIComponent(query)}&limit=5`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.length > 0) {
                displaySuggestions(data.data);
            } else {
                document.getElementById('searchSuggestions')?.remove();
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
        });
}

// Display search suggestions dropdown
function displaySuggestions(products) {
    let suggestionsDiv = document.getElementById('searchSuggestions');
    
    // Create suggestions div if it doesn't exist
    if (!suggestionsDiv) {
        suggestionsDiv = document.createElement('div');
        suggestionsDiv.id = 'searchSuggestions';
        suggestionsDiv.className = 'search-suggestions';
        const searchBox = document.querySelector('input[name="search"]');
        searchBox.parentNode.appendChild(suggestionsDiv);
    }
    
    // Build suggestions HTML
    let html = '<ul class="list-group">';
    products.forEach(product => {
        html += `
            <li class="list-group-item list-group-item-action" onclick="selectSuggestion('${product.product_title}')">
                <div class="d-flex align-items-center">
                    ${product.product_image ? 
                        `<img src="../uploads/${product.product_image}" alt="${product.product_title}" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">` : 
                        '<div style="width: 40px; height: 40px; background: #ddd; margin-right: 10px;"></div>'
                    }
                    <div>
                        <strong>${product.product_title}</strong>
                        <small class="text-muted d-block">GH₵ ${parseFloat(product.product_price).toFixed(2)}</small>
                    </div>
                </div>
            </li>
        `;
    });
    html += '</ul>';
    
    suggestionsDiv.innerHTML = html;
    suggestionsDiv.style.display = 'block';
}

// Select a suggestion and fill search box
function selectSuggestion(title) {
    const searchBox = document.querySelector('input[name="search"]');
    searchBox.value = title;
    document.getElementById('searchSuggestions')?.remove();
    
    // Submit the search form
    searchBox.closest('form').submit();
}

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box')) {
        document.getElementById('searchSuggestions')?.remove();
    }
});

// Attach debounced search to search input
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function(e) {
        showSearchSuggestions(e.target.value);
    }, 800));
}

// Load categories dynamically
function loadCategories(selectElement) {
    fetch('../actions/product_actions.php?action=get_categories')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                selectElement.innerHTML = '<option value="">All Categories</option>';
                data.data.forEach(cat => {
                    selectElement.innerHTML += `<option value="${cat.cat_id}">${cat.cat_name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading categories:', error));
}

// Load brands dynamically
function loadBrands(selectElement) {
    fetch('../actions/product_actions.php?action=get_brands')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                selectElement.innerHTML = '<option value="">All Brands</option>';
                data.data.forEach(brand => {
                    selectElement.innerHTML += `<option value="${brand.brand_id}">${brand.brand_name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading brands:', error));
}

// Add to cart function (placeholder)
function addToCart(productId) {
    alert('Add to cart functionality coming soon! Product ID: ' + productId);
    // TODO: Implement actual cart functionality
    // This would typically make an AJAX call to add the item to the cart
}

// AJAX-based product loading (optional enhancement)
function loadProducts(action, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = `../actions/product_actions.php?action=${action}&${queryString}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderProducts(data.data);
                renderPagination(data.page, data.total_pages);
            } else {
                console.error('Error loading products:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Render products in grid
function renderProducts(products) {
    const container = document.getElementById('productsContainer');
    if (!container) return;
    
    if (products.length === 0) {
        container.innerHTML = '<div class="col-12 text-center"><p class="text-muted">No products found</p></div>';
        return;
    }
    
    let html = '';
    products.forEach(product => {
        const imageHtml = product.product_image 
            ? `<img src="../uploads/${product.product_image}" class="card-img-top" alt="${product.product_title}" style="height: 220px; object-fit: cover;">`
            : `<div class="product-image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
               </div>`;
        
        html += `
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    ${imageHtml}
                    <div class="card-body">
                        <h5 class="card-title">${product.product_title}</h5>
                        <p class="card-text text-muted">${product.product_desc.substring(0, 100)}...</p>
                        <p class="card-text"><strong>GH₵ ${parseFloat(product.product_price).toFixed(2)}</strong></p>
                        <a href="single_product.php?id=${product.product_id}" class="btn btn-sm btn-primary">View Details</a>
                        <button class="btn btn-sm btn-success" onclick="addToCart(${product.product_id})">Add to Cart</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Render pagination
function renderPagination(currentPage, totalPages) {
    const pagination = document.getElementById('pagination');
    if (!pagination || totalPages <= 1) return;
    
    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    if (currentPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="?page=${currentPage - 1}">Previous</a></li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="?page=${i}">${i}</a></li>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        html += `<li class="page-item"><a class="page-link" href="?page=${currentPage + 1}">Next</a></li>`;
    }
    
    html += '</ul></nav>';
    pagination.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load categories and brands for filter dropdowns if they exist
    const categorySelect = document.getElementById('categoryFilter');
    const brandSelect = document.getElementById('brandFilter');
    
    if (categorySelect && !categorySelect.hasAttribute('data-loaded')) {
        loadCategories(categorySelect);
        categorySelect.setAttribute('data-loaded', 'true');
    }
    
    if (brandSelect && !brandSelect.hasAttribute('data-loaded')) {
        loadBrands(brandSelect);
        brandSelect.setAttribute('data-loaded', 'true');
    }
});

// CSS for search suggestions (can be moved to app.css)
const style = document.createElement('style');
style.textContent = `
    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .search-suggestions .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
    }
    .search-suggestions .list-group-item:hover {
        background-color: #f8f9fa;
    }
    .search-box {
        position: relative;
    }
`;
document.head.appendChild(style);