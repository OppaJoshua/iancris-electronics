<?php
session_start();
$page_title = "3D Product Videos";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<style>
    body.videos-page {
        overflow-x: hidden;
    }
    
    .hero-background {
        background-image: url('../uploads/images/Home - Herosection.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        width: 100%;
        min-height: 40vh;
    }
    
    .gradient-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.75);
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
    
    .animate-slide-up {
        animation: slideUp 1s ease-out forwards;
    }
    
    .video-card {
        transition: all 0.3s ease;
    }
    
    .video-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }
    
    /* 360 Video Controls */
    .video-360-container {
        position: relative;
        cursor: grab;
        user-select: none;
    }
    
    .video-360-container:active {
        cursor: grabbing;
    }
    
    .rotation-controls {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        border-radius: 50px;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10;
    }
    
    .rotation-slider {
        width: 200px;
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
        cursor: pointer;
        position: relative;
    }
    
    .rotation-slider-fill {
        height: 100%;
        background: #3B82F6;
        border-radius: 2px;
        width: 0%;
        transition: width 0.1s;
    }
    
    .rotation-icon {
        color: white;
        animation: rotateIcon 2s linear infinite;
    }
    
    @keyframes rotateIcon {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .video-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(37, 99, 235, 0.9);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        z-index: 5;
    }
</style>

<script>
    document.body.classList.add('videos-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <div class="gradient-overlay"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-8 flex items-center justify-center" style="min-height: 40vh;">
        <div class="max-w-3xl mx-auto w-full text-center">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-4 animate-slide-up">
                <span class="text-blue-600 block italic" style="font-style: italic;">WATCH</span>
                <span class="text-black block">3D PRODUCT DEMOS</span>
            </h1>
            <p class="text-lg text-gray-600 animate-slide-up">Drag to rotate and explore our CCTV products in 360°</p>
        </div>
    </div>
</section>

<!-- Video Grid Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <!-- CCTV Camera -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video1">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video1" class="w-full h-full object-cover" preload="auto" muted playsinline>
                        <source src="../uploads/3d/CTTV.mp4" type="video/mp4">
                        <source src="../uploads/3d/CCTV.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">CCTV Camera</h3>
                    <p class="text-gray-600 text-sm mb-4">Professional CCTV surveillance camera with HD recording</p>
                    <a href="products.php?category=CCTV" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Router -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video2">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video2" class="w-full h-full object-cover" preload="metadata" muted>
                        <source src="../uploads/3d/ROUTER.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Router</h3>
                    <p class="text-gray-600 text-sm mb-4">High-speed network router for reliable connectivity</p>
                    <a href="products.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- NVR Recorder -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video3">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video3" class="w-full h-full object-cover" preload="metadata" muted>
                        <source src="../uploads/3d/net recorder.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">NVR Recorder</h3>
                    <p class="text-gray-600 text-sm mb-4">Network video recorder with cloud storage</p>
                    <a href="products.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CAT6 UTP Cable -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video4">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video4" class="w-full h-full object-cover" preload="metadata" muted>
                        <source src="../uploads/3d/cat6 UTP Dlink.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">CAT6 UTP D-Link Cable</h3>
                    <p class="text-gray-600 text-sm mb-4">High-quality D-Link CAT6 network cabling</p>
                    <a href="products.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- DAHUA Cable -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video5">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video5" class="w-full h-full object-cover" preload="metadata" muted>
                        <source src="../uploads/3d/dahua cable.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">DAHUA Cable</h3>
                    <p class="text-gray-600 text-sm mb-4">Professional DAHUA cabling solution</p>
                    <a href="products.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- DAHUA CAT6 Cable -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video6">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video6" class="w-full h-full object-cover" preload="metadata" muted>
                        <source src="../uploads/3d/Dahua CAT6 UTP Cable.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">DAHUA CAT6 UTP Cable</h3>
                    <p class="text-gray-600 text-sm mb-4">Premium DAHUA CAT6 UTP cabling solution</p>
                    <a href="products.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CABLE -->
            <div class="video-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden">
                <div class="aspect-video bg-gray-900 relative video-360-container" data-video-id="video7">
                    <div class="video-overlay">360° Rotate</div>
                    <video id="video7" class="w-full h-full object-cover" preload="metadata" muted>
                        <source src="../uploads/3d/CABLE.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <div class="rotation-controls">
                        <svg class="w-5 h-5 rotation-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="rotation-slider">
                            <div class="rotation-slider-fill"></div>
                        </div>
                        <span class="text-white text-xs font-medium">0°</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Network Cable</h3>
                    <p class="text-gray-600 text-sm mb-4">Professional network cabling for installations</p>
                    <a href="products.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-2">
                        View Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-white border-2 border-blue-600 rounded-2xl p-8 md:p-12 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Ready to Secure Your Property?</h2>
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                Browse our full catalog or contact us for a personalized consultation
            </p>
            <div class="flex gap-4 justify-center flex-wrap">
                <a href="products.php" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-full font-bold uppercase text-sm hover:bg-blue-700 transition">
                    View All Products
                </a>
                <a href="contact.php" class="inline-block bg-transparent border-2 border-black text-black px-8 py-4 rounded-full font-bold uppercase text-sm hover:bg-black hover:text-white transition">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Initialize 360 video controls for all video containers
document.querySelectorAll('.video-360-container').forEach(container => {
    const videoId = container.dataset.videoId;
    const video = document.getElementById(videoId);
    const slider = container.querySelector('.rotation-slider');
    const sliderFill = container.querySelector('.rotation-slider-fill');
    const angleText = container.querySelector('.rotation-controls span');
    
    let isDragging = false;
    let startX = 0;
    let currentRotation = 0;
    let animationFrameId = null;
    
    // Make video loop and ensure it's ready
    video.loop = true;
    video.preload = 'auto';
    
    // Pause video by default (user controls with drag)
    video.addEventListener('loadedmetadata', () => {
        console.log(`Video ${videoId} loaded. Duration: ${video.duration}s`);
        video.pause();
        video.currentTime = 0;
    });
    
    // Prevent video from auto-playing
    video.addEventListener('play', () => {
        if (!isDragging) {
            video.pause();
        }
    });
    
    // Mouse drag on video - SMOOTH & FAST
    container.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.clientX;
        container.style.cursor = 'grabbing';
        e.preventDefault();
    });
    
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        
        const deltaX = e.clientX - startX;
        const rotationChange = deltaX * 1.2; // Fast rotation: 1.2 degrees per pixel
        
        currentRotation = (currentRotation + rotationChange) % 360;
        if (currentRotation < 0) currentRotation += 360;
        
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
        }
        
        animationFrameId = requestAnimationFrame(() => {
            updateVideoPosition(video, currentRotation, sliderFill, angleText);
        });
        
        startX = e.clientX;
    });
    
    document.addEventListener('mouseup', () => {
        isDragging = false;
        container.style.cursor = 'grab';
    });
    
    // Touch support for mobile
    let touchStartX = 0;
    
    container.addEventListener('touchstart', (e) => {
        isDragging = true;
        touchStartX = e.touches[0].clientX;
        e.preventDefault();
    }, { passive: false });
    
    document.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        const deltaX = e.touches[0].clientX - touchStartX;
        const rotationChange = deltaX * 1.2;
        
        currentRotation = (currentRotation + rotationChange) % 360;
        if (currentRotation < 0) currentRotation += 360;
        
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
        }
        
        animationFrameId = requestAnimationFrame(() => {
            updateVideoPosition(video, currentRotation, sliderFill, angleText);
        });
        
        touchStartX = e.touches[0].clientX;
    }, { passive: false });
    
    document.addEventListener('touchend', () => {
        isDragging = false;
    });
    
    // Slider click
    slider.addEventListener('click', (e) => {
        const rect = slider.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const percentage = Math.max(0, Math.min(1, clickX / rect.width));
        currentRotation = percentage * 360;
        animateToRotation(video, currentRotation, sliderFill, angleText, 300);
    });
    
    // Slider drag
    let isDraggingSlider = false;
    
    slider.addEventListener('mousedown', (e) => {
        isDraggingSlider = true;
        updateSliderPosition(e);
        e.stopPropagation();
    });
    
    document.addEventListener('mousemove', (e) => {
        if (!isDraggingSlider) return;
        updateSliderPosition(e);
    });
    
    document.addEventListener('mouseup', () => {
        isDraggingSlider = false;
    });
    
    function updateSliderPosition(e) {
        const rect = slider.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const percentage = Math.max(0, Math.min(1, clickX / rect.width));
        currentRotation = percentage * 360;
        updateVideoPosition(video, currentRotation, sliderFill, angleText);
    }
});

// SMOOTH video position update
function updateVideoPosition(video, rotation, sliderFill, angleText) {
    if (!video.duration || isNaN(video.duration)) return;
    
    const percentage = rotation / 360;
    const targetTime = video.duration * percentage;
    
    // Smooth seek
    if (Math.abs(video.currentTime - targetTime) > 0.01) {
        video.currentTime = targetTime;
    }
    
    // Update UI
    sliderFill.style.width = `${percentage * 100}%`;
    angleText.textContent = `${Math.round(rotation)}°`;
}

// SMOOTH animation to target rotation
function animateToRotation(video, targetRotation, sliderFill, angleText, duration) {
    const startRotation = (video.currentTime / video.duration) * 360;
    const startTime = performance.now();
    
    function animate(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeProgress = progress < 0.5
            ? 2 * progress * progress
            : -1 + (4 - 2 * progress) * progress;
        
        const currentRotation = startRotation + (targetRotation - startRotation) * easeProgress;
        
        updateVideoPosition(video, currentRotation, sliderFill, angleText);
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }
    
    requestAnimationFrame(animate);
}
</script>

<?php require_once '../includes/footer.php'; ?>
