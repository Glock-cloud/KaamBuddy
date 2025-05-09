// Location handling
let userLocation = {
    latitude: null,
    longitude: null,
    city: null
};

// Get user's current location
function getUserLocation() {
    const locationElement = document.getElementById('user-location');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                userLocation.latitude = position.coords.latitude;
                userLocation.longitude = position.coords.longitude;
                
                // Add location params to search form
                updateSearchForm(position.coords.latitude, position.coords.longitude);
                
                reverseGeocode(position.coords.latitude, position.coords.longitude);
            },
            error => {
                console.error('Error getting location:', error);
                showLocationError(error);
                fallbackToIPLocation();
            },
            { 
                timeout: 10000,
                enableHighAccuracy: false,
                maximumAge: 60000
            }
        );
    } else {
        showLocationError({ code: 0, message: 'Geolocation not supported by this browser' });
        fallbackToIPLocation();
    }
}

// Fallback to IP-based location
async function fallbackToIPLocation() {
    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        
        if (data && data.latitude && data.longitude) {
            userLocation.latitude = data.latitude;
            userLocation.longitude = data.longitude;
            userLocation.city = data.city || 'Unknown location';
            
            updateLocationUI();
            updateSearchForm(data.latitude, data.longitude);
        }
    } catch (error) {
        console.error('Error getting IP location:', error);
    }
}

// Add location parameters to search form
function updateSearchForm(lat, lng) {
    // Find all search forms on the page
    const searchForms = document.querySelectorAll('form[action="search.php"]');
    
    searchForms.forEach(form => {
        // Check if lat/lng inputs already exist
        let latInput = form.querySelector('input[name="lat"]');
        let lngInput = form.querySelector('input[name="lng"]');
        
        // If they don't exist, create them
        if (!latInput) {
            latInput = document.createElement('input');
            latInput.type = 'hidden';
            latInput.name = 'lat';
            form.appendChild(latInput);
        }
        
        if (!lngInput) {
            lngInput = document.createElement('input');
            lngInput.type = 'hidden';
            lngInput.name = 'lng';
            form.appendChild(lngInput);
        }
        
        // Set the values
        latInput.value = lat;
        lngInput.value = lng;
    });
}

// Reverse geocoding using Nominatim
async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10`);
        const data = await response.json();
        
        if (data.address) {
            userLocation.city = data.address.city || data.address.town || data.address.village || data.address.state || 'Your location';
            updateLocationUI();
        }
    } catch (error) {
        console.error('Error in reverse geocoding:', error);
    }
}

// Update UI with location
function updateLocationUI() {
    const locationElement = document.getElementById('user-location');
    if (locationElement && userLocation.city) {
        locationElement.textContent = userLocation.city;
    }
}

// Show location error with specific messages
function showLocationError(error) {
    const locationElement = document.getElementById('user-location');
    if (!locationElement) return;
    
    let message = 'Location not available';
    
    if (error) {
        switch(error.code) {
            case 1:
                message = 'Location access denied';
                break;
            case 2:
                message = 'Location unavailable';
                break;
            case 3:
                message = 'Location request timed out';
                break;
        }
    }
    
    locationElement.textContent = message;
}

// Haversine formula for calculating distance
function calculateDistance(lat1, lon1, lat2, lon2) {
    if (!lat1 || !lon1 || !lat2 || !lon2) return 9999; // Return large value if coordinates are missing
    
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Search functionality
let searchTimeout;
const searchInput = document.getElementById('service-search');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchServices(query);
            }, 300);
        } else {
            // Clear results if query is too short
            const resultsContainer = document.getElementById('search-results');
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
            }
        }
    });
}

// Search services
async function searchServices(query) {
    try {
        const params = new URLSearchParams({
            service: query
        });
        
        // Add coordinates if available
        if (userLocation.latitude && userLocation.longitude) {
            params.append('lat', userLocation.latitude);
            params.append('lng', userLocation.longitude);
        }
        
        const response = await fetch(`search.php?${params.toString()}`);
        const data = await response.json();
        updateSearchResults(data);
    } catch (error) {
        console.error('Error searching services:', error);
    }
}

// Update search results in UI
function updateSearchResults(results) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;

    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p>No services found</p>';
        return;
    }

    results.forEach(result => {
        let distance = 'Unknown distance';
        
        if (userLocation.latitude && userLocation.longitude && result.latitude && result.longitude) {
            const distanceValue = calculateDistance(
                userLocation.latitude,
                userLocation.longitude,
                result.latitude,
                result.longitude
            );
            distance = `${distanceValue.toFixed(1)} km away`;
        }

        const resultElement = document.createElement('div');
        resultElement.className = 'search-result-item';
        resultElement.innerHTML = `
            <h3>${result.name}</h3>
            <p><i class="fas fa-tag"></i> ${result.custom_category || result.category_name || 'Service Provider'}</p>
            <p><i class="fas fa-map-marker-alt"></i> ${result.address || 'Location not specified'}</p>
            <p><i class="fas fa-route"></i> ${distance}</p>
            <a href="provider.php?id=${result.id}" class="btn-secondary">View Profile</a>
        `;
        resultsContainer.appendChild(resultElement);
    });
}

// Initialize location on page load
document.addEventListener('DOMContentLoaded', () => {
    getUserLocation();
}); 