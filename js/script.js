// JavaScript untuk autocomplete dan interaktivitas
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dynamic background
    initDynamicBackground();
    
    const searchInput = document.getElementById('keyword');
    const autocompleteResults = document.getElementById('autocomplete-results');
    const hiddenKodeWilayah = document.getElementById('kode_wilayah');
    const searchForm = document.querySelector('.search-form');
    let selectedIndex = -1;
    let currentResults = [];
    let searchTimeout;

    // Dynamic Background Functions
    function initDynamicBackground() {
        setTimeBasedBackground();
        setWeatherBasedBackground();
        
        // Update background every 30 minutes
        setInterval(() => {
            setTimeBasedBackground();
            setWeatherBasedBackground();
        }, 30 * 60 * 1000);
    }
    
    function setTimeBasedBackground() {
        const now = new Date();
        const hour = now.getHours();
        const body = document.body;
        
        // Remove existing time classes
        body.classList.remove('morning', 'day', 'evening', 'night');
        
        if (hour >= 5 && hour < 10) {
            body.classList.add('morning');
        } else if (hour >= 10 && hour < 17) {
            body.classList.add('day');
        } else if (hour >= 17 && hour < 20) {
            body.classList.add('evening');
        } else {
            body.classList.add('night');
        }
    }
    
    function setWeatherBasedBackground() {
        // Try to get weather from current page data or make API call
        const currentWeatherDesc = document.querySelector('.current-desc');
        if (currentWeatherDesc) {
            const weatherText = currentWeatherDesc.textContent.toLowerCase();
            applyWeatherClass(weatherText);
        } else {
            // Fallback: try to detect location and get weather
            const locationElement = document.querySelector('.current-weather h2');
            if (locationElement) {
                // For demo purposes, apply a default weather class
                applyWeatherClass('clear');
            }
        }
    }
    
    function applyWeatherClass(weatherDesc) {
        const body = document.body;
        
        // Remove existing weather classes
        body.classList.remove('rainy', 'cloudy', 'sunny', 'clear');
        
        if (weatherDesc.includes('hujan') || weatherDesc.includes('rain')) {
            body.classList.add('rainy');
        } else if (weatherDesc.includes('berawan') || weatherDesc.includes('cloud') || weatherDesc.includes('mendung')) {
            body.classList.add('cloudy');
        } else if (weatherDesc.includes('cerah') || weatherDesc.includes('sunny') || weatherDesc.includes('panas')) {
            body.classList.add('sunny');
        } else {
            body.classList.add('clear');
        }
    }

    // Autocomplete functionality
    if (searchInput && autocompleteResults) {
        searchInput.addEventListener('input', function() {
            const keyword = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            if (keyword.length < 2) {
                hideAutocomplete();
                return;
            }
            
            // Debounce search requests
            searchTimeout = setTimeout(() => {
                fetchAutocompleteResults(keyword);
            }, 300);
        });

        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (!autocompleteResults.style.display || autocompleteResults.style.display === 'none') {
                return;
            }

            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, currentResults.length - 1);
                    updateSelection();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0 && currentResults[selectedIndex]) {
                        selectRegion(currentResults[selectedIndex]);
                    }
                    break;
                case 'Escape':
                    hideAutocomplete();
                    break;
            }
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
                hideAutocomplete();
            }
        });
    }

    // Form submission handler
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // If a region code is selected, redirect directly to weather page
            if (hiddenKodeWilayah && hiddenKodeWilayah.value) {
                e.preventDefault();
                window.location.href = '?kode_wilayah=' + encodeURIComponent(hiddenKodeWilayah.value);
                return;
            }
            
            // Validate input
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                searchInput.classList.add('shake');
                setTimeout(() => {
                    searchInput.classList.remove('shake');
                }, 500);
            }
        });

        // Focus pada input pencarian saat halaman dimuat
        if (window.location.search === '' || window.location.search === '?') {
            searchInput.focus();
        }
    }

    // Fetch autocomplete results via AJAX
    function fetchAutocompleteResults(keyword) {
        fetch(`?ajax=autocomplete&keyword=${encodeURIComponent(keyword)}`)
            .then(response => response.json())
            .then(data => {
                currentResults = data;
                displayAutocompleteResults(data);
            })
            .catch(error => {
                console.error('Error fetching autocomplete results:', error);
                hideAutocomplete();
            });
    }

    // Display autocomplete results
    function displayAutocompleteResults(results) {
        if (results.length === 0) {
            hideAutocomplete();
            return;
        }

        autocompleteResults.innerHTML = '';
        selectedIndex = -1;

        results.forEach((result, index) => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <span class="region-name">${escapeHtml(result.nama)}</span>
            `;
            
            item.addEventListener('click', function() {
                selectRegion(result);
            });
            
            autocompleteResults.appendChild(item);
        });

        autocompleteResults.style.display = 'block';
    }

    // Update visual selection in autocomplete
    function updateSelection() {
        const items = autocompleteResults.querySelectorAll('.autocomplete-item');
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }

    // Select a region and redirect to weather page
    function selectRegion(region) {
        searchInput.value = region.nama;
        hiddenKodeWilayah.value = region.kode;
        hideAutocomplete();
        
        // Redirect directly to weather page
        window.location.href = '?kode_wilayah=' + encodeURIComponent(region.kode);
    }

    // Hide autocomplete dropdown
    function hideAutocomplete() {
        autocompleteResults.style.display = 'none';
        selectedIndex = -1;
        currentResults = [];
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Animasi untuk hasil pencarian (fallback)
    const resultItems = document.querySelectorAll('.result-item');
    resultItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });

    // Tambahkan efek hover pada item cuaca
    const weatherItems = document.querySelectorAll('.weather-day li');
    weatherItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.1)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.05)';
        });
    });
});