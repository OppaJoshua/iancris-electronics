<?php
session_start();
$page_title = "Gallery";
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/database.php';

// Get all active gallery items
$sql = "SELECT * FROM gallery WHERE status = 'active' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<style>
    body.gallery-page {
        overflow-x: hidden;
    }
    
    .hero-background {
        background-image: url('../uploads/images/Home - Herosection.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        width: 100%;
        min-height: 50vh;
    }
    
    .gradient-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.7);
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-slide-up {
        animation: slideUp 1s ease-out forwards;
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    
    .animate-delay-1 { opacity: 0; animation-delay: 0.1s; }
    .animate-delay-2 { opacity: 0; animation-delay: 0.2s; }
    .animate-delay-3 { opacity: 0; animation-delay: 0.3s; }
    .animate-delay-4 { opacity: 0; animation-delay: 0.4s; }
    .animate-delay-5 { opacity: 0; animation-delay: 0.5s; }
    .animate-delay-6 { opacity: 0; animation-delay: 0.6s; }
    
    nav#main-nav {
        position: fixed;
        z-index: 9999 !important;
    }
    
    .hero-content {
        position: relative;
        z-index: 10;
        min-height: 50vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    /* Ensure map stays below nav */
    #map {
        z-index: 1;
    }
    
    .leaflet-pane {
        z-index: 400;
    }
    
    .leaflet-top,
    .leaflet-bottom {
        z-index: 1000;
    }
    
    .leaflet-popup {
        z-index: 1001;
    }
    
    /* Map Styles */
    .leaflet-popup-content-wrapper {
        border-radius: 0.75rem;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .leaflet-popup-content {
        margin: 0;
        width: 320px !important;
    }
    
    .leaflet-popup-tip {
        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.2);
    }
    
    .map-popup-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }
    
    .map-popup-content {
        padding: 1.25rem;
        background: white;
    }
    
    .map-popup-title {
        font-size: 1.25rem;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    
    .map-popup-location {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        color: #2563eb;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    
    .map-popup-location svg {
        width: 1rem;
        height: 1rem;
        margin-right: 0.5rem;
        flex-shrink: 0;
    }
    
    .map-popup-description {
        font-size: 0.9rem;
        color: #4b5563;
        line-height: 1.6;
    }
    
    /* Custom marker pulse animation */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(37, 99, 235, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
        }
    }
    
    .custom-pin {
        animation: pulse 2s infinite;
    }
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.body.classList.add('gallery-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <div class="gradient-overlay"></div>
    
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-8">
        <div class="max-w-3xl mx-auto w-full">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-8 animate-slide-up">
                <span class="text-blue-600 block italic" style="font-style: italic;">FIND</span>
                <span class="text-black block">YOUR PROJECT</span>
            </h1>
            
            <div class="animate-slide-up animate-delay-1">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search by location or project name..." 
                        class="w-full px-6 py-4 pr-12 text-lg border-2 border-black rounded-full focus:outline-none focus:border-blue-600 transition"
                    >
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Projects Across Cebu</h2>
            <p class="text-xl text-gray-600">Click on the pins to view our installations</p>
        </div>
        
        <!-- Map Container with Border and Shadow -->
        <div class="relative rounded-2xl overflow-hidden border-4 border-gray-200" style="height: 600px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);">
            <div id="map" style="width: 100%; height: 100%;"></div>
            
            <!-- Map Legend -->
            <div class="absolute top-6 right-6 bg-white p-5 rounded-xl shadow-xl z-10 border border-gray-100">
                <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2 text-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Projects
                </h4>
                <div class="flex items-center gap-3 text-sm text-gray-700 bg-blue-50 px-3 py-2 rounded-lg">
                    <div class="w-5 h-5 bg-blue-600 rounded-full"></div>
                    <span class="font-medium">Installation Site</span>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                    <div class="text-3xl font-bold text-blue-600"><?php echo $result->num_rows; ?></div>
                    <div class="text-sm text-gray-500 font-medium">Completed</div>
                </div>
            </div>
        </div>
        
        <?php if ($result->num_rows == 0): ?>
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Projects Yet</h3>
                <p class="text-gray-600">Check back soon for our latest installations!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Ready to Secure Your Property?</h2>
        <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto">
            Join hundreds of satisfied clients across Cebu. Let us help you with professional CCTV installation and IT solutions.
        </p>
        <div class="flex gap-4 justify-center flex-wrap">
            <a href="../pages/products.php" class="inline-block bg-transparent text-black px-8 py-4 rounded-full font-bold uppercase text-sm border-2 border-black hover:bg-black hover:text-white transition">
                View Products
            </a>
            <a href="../pages/contact.php" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-full font-bold uppercase text-sm hover:bg-blue-700 transition">
                Get A Quote
            </a>
        </div>
    </div>
</section>

<script>
// Initialize the map centered on Cebu with colored theme - showing whole Cebu
const map = L.map('map', {
    center: [10.3157, 123.8854],
    zoom: 9.5,
    zoomControl: true,
    scrollWheelZoom: true,
    minZoom: 9,
    maxZoom: 16
});

// Add colored map tiles (Alidade Smooth)
L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
    attribution: '© Stadia Maps, © OpenMapTiles, © OpenStreetMap contributors',
    maxZoom: 20,
}).addTo(map);

// Custom blue pin icon with pulse effect
const blueIcon = L.divIcon({
    className: 'custom-pin',
    html: `<div style="position: relative;">
            <svg width="35" height="45" viewBox="0 0 35 45" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.3"/>
                    </filter>
                </defs>
                <path d="M17.5 0C10.044 0 4 6.044 4 13.5c0 10.5 13.5 31.5 13.5 31.5s13.5-21 13.5-31.5C31 6.044 24.956 0 17.5 0z" 
                      fill="#2563eb" filter="url(#shadow)"/>
                <circle cx="17.5" cy="13.5" r="6" fill="white"/>
                <circle cx="17.5" cy="13.5" r="3" fill="#2563eb"/>
            </svg>
           </div>`,
    iconSize: [35, 45],
    iconAnchor: [17.5, 45],
    popupAnchor: [0, -45]
});

// Project locations with coordinates
const projects = <?php 
$result->data_seek(0);
$locations = [];

while ($item = $result->fetch_assoc()) {
    // Use coordinates from database if available, otherwise skip this item
    if (!empty($item['latitude']) && !empty($item['longitude'])) {
        $locations[] = [
            'title' => $item['title'],
            'location' => $item['location'],
            'description' => $item['description'],
            'image' => $item['image'],
            'lat' => floatval($item['latitude']),  // Use exact coordinates from database
            'lng' => floatval($item['longitude'])  // Use exact coordinates from database
        ];
    }
}
echo json_encode($locations);
?>;

// Add markers for each project
projects.forEach(project => {
    // Log to verify coordinates are correct
    console.log(`${project.title}: ${project.lat}, ${project.lng}`);
    
    const marker = L.marker([project.lat, project.lng], { icon: blueIcon }).addTo(map);
    
    const popupContent = `
        <div>
            <img src="../${project.image}" alt="${project.title}" class="map-popup-image">
            <div class="map-popup-content">
                <div class="map-popup-title">${project.title}</div>
                <div class="map-popup-location">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    ${project.location}
                </div>
                <div class="map-popup-description">${project.description}</div>
            </div>
        </div>
    `;
    
    marker.bindPopup(popupContent, {
        maxWidth: 320,
        className: 'custom-popup',
        closeButton: true
    });
    
    // Optional: Open popup on hover
    marker.on('mouseover', function() {
        this.openPopup();
    });
});

// Search functionality
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        // Close all popups first
        map.closePopup();
        
        if (searchTerm.length > 2) {
            let found = false;
            projects.forEach((project, index) => {
                const title = project.title.toLowerCase();
                const location = project.location.toLowerCase();
                
                if (title.includes(searchTerm) || location.includes(searchTerm)) {
                    if (!found) {
                        // Pan to first match and open popup
                        map.setView([project.lat, project.lng], 14);
                        setTimeout(() => {
                            map.eachLayer(layer => {
                                if (layer instanceof L.Marker) {
                                    const pos = layer.getLatLng();
                                    if (pos.lat === project.lat && pos.lng === project.lng) {
                                        layer.openPopup();
                                    }
                                }
                            });
                        }, 500);
                        found = true;
                    }
                }
            });
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>