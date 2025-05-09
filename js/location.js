// Location handling
let userLocation = {
    latitude: null,
    longitude: null,
    city: null
};

// Get user's current location
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                userLocation.latitude = position.coords.latitude;
                userLocation.longitude = position.coords.longitude;
                reverseGeocode(position.coords.latitude, position.coords.longitude);
            },
            error => {
                console.error('Error getting location:', error);
                showLocationError();
            }
        );
    } else {
        showLocationError();
    }
}

// Reverse geocoding using Nominatim
async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        const data = await response.json();
        
        if (data.address) {
            userLocation.city = data.address.city || data.address.town || data.address.village;
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

// Show location error
function showLocationError() {
    const locationElement = document.getElementById('user-location');
    if (locationElement) {
        locationElement.textContent = 'Location not available';
    }
}

// Haversine formula for calculating distance
function calculateDistance(lat1, lon1, lat2, lon2) {
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
        }
    });
}

// Search services
async function searchServices(query) {
    try {
        const response = await fetch(`search.php?service=${encodeURIComponent(query)}&lat=${userLocation.latitude}&lng=${userLocation.longitude}`);
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
        const distance = calculateDistance(
            userLocation.latitude,
            userLocation.longitude,
            result.latitude,
            result.longitude
        );

        const resultElement = document.createElement('div');
        resultElement.className = 'search-result-item';
        resultElement.innerHTML = `
            <h3>${result.name}</h3>
            <p>${result.description}</p>
            <p>Distance: ${distance.toFixed(1)} km</p>
        `;
        resultsContainer.appendChild(resultElement);
    });
}

// Initialize location on page load
document.addEventListener('DOMContentLoaded', () => {
    getUserLocation();
}); 