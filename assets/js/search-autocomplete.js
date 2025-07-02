document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');
    const searchContainer = document.querySelector('.search-autocomplete-wrapper');
    
    if (!searchInput || !searchForm || !searchContainer) return;
    
    let autocompleteResults = null;
    let selectedIndex = -1;
    let debounceTimer;
    
    // Create autocomplete container
    const autocompleteDiv = document.createElement('div');
    autocompleteDiv.className = 'search-autocomplete d-none';
    searchContainer.appendChild(autocompleteDiv);
    
    // Add event listeners
    searchInput.addEventListener('input', handleInput);
    searchInput.addEventListener('keydown', handleKeyPress);
    document.addEventListener('click', handleOutsideClick);
    
    // Handle input event with debounce
    function handleInput() {
        const query = searchInput.value.trim();
        
        clearTimeout(debounceTimer);
        
        if (query.length < 1) {
            hideAutocomplete();
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetchSearchResults(query);
        }, 300);
    }
    
    // Fetch search results from the server
    function fetchSearchResults(query) {
        fetch(`includes/search_products.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data.products);
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
            });
    }
    
    // Display search results in the autocomplete dropdown
    function displaySearchResults(products) {
        autocompleteResults = products;
        selectedIndex = -1;
        
        if (products.length === 0) {
            autocompleteDiv.innerHTML = '<div class="search-autocomplete-empty">Không tìm thấy sản phẩm nào phù hợp</div>';
        } else {
            autocompleteDiv.innerHTML = '';
            
            products.forEach((product, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'search-autocomplete-item';
                itemDiv.dataset.index = index;
                itemDiv.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="search-autocomplete-item-image">
                    <div class="search-autocomplete-item-details">
                        <span class="search-autocomplete-item-name">${product.name}</span>
                        <span class="search-autocomplete-item-price">${product.price}</span>
                    </div>
                `;
                
                itemDiv.addEventListener('click', () => {
                    window.location.href = product.url;
                });
                
                autocompleteDiv.appendChild(itemDiv);
            });
        }
        
        autocompleteDiv.classList.remove('d-none');
    }
    
    // Hide autocomplete dropdown
    function hideAutocomplete() {
        autocompleteDiv.classList.add('d-none');
        autocompleteResults = null;
        selectedIndex = -1;
    }
    
    // Handle arrow key navigation and enter key selection
    function handleKeyPress(e) {
        if (!autocompleteResults || autocompleteResults.length === 0) return;
        
        // Down arrow
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % autocompleteResults.length;
            highlightSelected();
        } 
        // Up arrow
        else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = selectedIndex <= 0 ? autocompleteResults.length - 1 : selectedIndex - 1;
            highlightSelected();
        } 
        // Enter key
        else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            window.location.href = autocompleteResults[selectedIndex].url;
        }
    }
    
    // Highlight the selected item
    function highlightSelected() {
        const items = autocompleteDiv.querySelectorAll('.search-autocomplete-item');
        
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    // Close autocomplete when clicking outside
    function handleOutsideClick(e) {
        if (!searchContainer.contains(e.target)) {
            hideAutocomplete();
        }
    }
}); 