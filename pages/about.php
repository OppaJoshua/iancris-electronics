<?php
session_start();
$page_title = "About Us";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<style>
    /* Apply overflow hidden on about page */
    body.about-page {
        overflow-x: hidden;
    }
    
    .hero-background {
        background-image: url('../uploads/images/abouthero.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        width: 100%;
        min-height: 70vh;
    }
    
    .gradient-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, 
            rgba(255, 255, 255, 0.9) 60%,
            rgba(255, 255, 255, 0.7) 80%,
            rgba(255, 255, 255, 0.5) 90%);
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
    
    @keyframes countUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes scroll {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }
    
    .animate-slide-up {
        animation: slideUp 1s ease-out forwards;
    }
    
    .animate-fade-in {
        animation: fadeIn 1s ease-out forwards;
    }
    
    .animate-delay-1 {
        opacity: 0;
        animation-delay: 0.2s;
    }
    
    .animate-delay-2 {
        opacity: 0;
        animation-delay: 0.4s;
    }
    
    .animate-delay-3 {
        opacity: 0;
        animation-delay: 0.6s;
    }
    
    .animate-delay-4 {
        opacity: 0;
        animation-delay: 0.8s;
    }
    
    /* Ensure navbar is above hero */
    nav#main-nav {
        position: fixed;
        z-index: 50;
    }
    
    /* Hero content positioning */
    .hero-content {
        position: relative;
        z-index: 10;
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    /* Stats animation */
    .stat-number {
        animation: countUp 0.8s ease-out forwards;
    }
    
    /* Logo carousel */
    .logo-carousel {
        overflow: hidden;
        position: relative;
        width: 100%;
    }
    
    .logo-track {
        display: flex;
        width: fit-content;
        animation: scroll 30s linear infinite;
    }
    
    .logo-track:hover {
        animation-play-state: paused;
    }
    
    .logo-item {
        flex-shrink: 0;
        width: 200px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 40px;
        opacity: 0.7;
        transition: opacity 0.3s;
        filter: grayscale(100%);
    }
    
    .logo-item:hover {
        opacity: 1;
        filter: grayscale(0%);
    }
    
    .logo-item img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
</style>

<script>
    document.body.classList.add('about-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <div class="gradient-overlay"></div>
    
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold leading-tight mb-6 animate-slide-up">
                <span class="text-blue-600 block italic" style="font-style: italic;">ABOUT</span>
                <span class="text-black block">IANKRIS CCTV</span>
            </h1>
            
            <p class="text-xl sm:text-2xl text-black font-medium animate-slide-up animate-delay-1">
                Your Trusted Partner in Security Solutions
            </p>
        </div>
    </div>
</section>

<!-- Company Overview Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-16 text-center">Who We Are</h2>
        
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Text Content -->
            <div class="space-y-6">
                <p class="text-gray-700 text-lg leading-relaxed">
                    We specialize in CCTV cameras, computers, structured cabling, network monitoring, and security equipment. The company's existing CCTV camera technical department, intelligent monitoring engineering department, network security service department, and technical after-sales service department.
                </p>
                
                <p class="text-gray-700 text-lg leading-relaxed">
                    Gradually grow from a repair and services to a strongest company that leads or provide customer satisfaction support services. The company will adhere to the business belief of "professionalism, focus, and great potential", and will continue to provide the best quality service for every partner and user.
                </p>
            </div>
            
            <!-- Image Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-200 rounded-lg overflow-hidden h-48">
                    <img src="../uploads/images/who1.jpg" alt="CCTV Installation" class="w-full h-full object-cover">
                </div>
                <div class="bg-gray-200 rounded-lg overflow-hidden h-48 mt-8">
                    <img src="../uploads/images/who2.jpg" alt="Network Setup" class="w-full h-full object-cover">
                </div>
                <div class="bg-gray-200 rounded-lg overflow-hidden h-48 -mt-8">
                    <img src="../uploads/images/who3.jpg" alt="Security System" class="w-full h-full object-cover">
                </div>
                <div class="bg-gray-200 rounded-lg overflow-hidden h-48">
                    <img src="../uploads/images/who4.jpg" alt="Technical Service" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Goals Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 text-center">Our Mission & Goals</h2>
        <p class="text-xl text-gray-600 text-center mb-16 italic">"Professionalism, focus, and great potential"</p>
        
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <!-- Mission Card -->
            <div class="bg-white p-8 rounded-lg">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h3>
                <p class="text-gray-700 leading-relaxed">
                   To be the leading cctv camera, computers, network, Fdas and,structured cabling devices.As good technical , services and provider for the different sytems in the Philippines.
                </p>
            </div>
            
            <!-- Vision Card -->
            <div class="bg-white p-8 rounded-lg">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h3>
                <p class="text-gray-700 leading-relaxed">
                    To supply best quality of cctv camera
computer, cables and network devices to
achieve customer satisfaction.
*To develop professional partnership with
our clients and help them improved and
expand their business.
                </p>
            </div>
            
            <!-- Goal 2 Card -->
            <div class="bg-white p-8 rounded-lg">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Partnership</h3>
                <p class="text-gray-700 leading-relaxed">
                    Develop professional partnerships with our clients and help them improve and expand their business operations.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Stats Section -->
<section class="py-20 relative">
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
        <img src="../uploads/images/about2.png" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-white bg-opacity-70"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-8 relative z-10">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 text-center">Why Choose Us</h2>
        <p class="text-xl text-gray-700 text-center mb-16">Excellence in every aspect of our service</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <!-- Stat 1 -->
            <div class="bg-white p-8 rounded-lg text-center stat-item shadow-lg">
                <div class="text-5xl font-bold text-blue-600 mb-2 stat-number">
                    <span class="counter" data-target="5">0</span>+
                </div>
                <p class="text-gray-900 font-bold text-lg mb-2">Years Experience</p>
                <p class="text-gray-600 text-sm">Trusted expertise in the industry</p>
            </div>
            
            <!-- Stat 2 -->
            <div class="bg-white p-8 rounded-lg text-center stat-item shadow-lg">
                <div class="text-5xl font-bold text-blue-600 mb-2 stat-number animate-delay-1">
                    <span class="counter" data-target="500">0</span>+
                </div>
                <p class="text-gray-900 font-bold text-lg mb-2">Happy Clients</p>
                <p class="text-gray-600 text-sm">Satisfied customers nationwide</p>
            </div>
            
            <!-- Stat 3 -->
            <div class="bg-white p-8 rounded-lg text-center stat-item shadow-lg">
                <div class="text-5xl font-bold text-blue-600 mb-2 stat-number animate-delay-2">
                    24/7
                </div>
                <p class="text-gray-900 font-bold text-lg mb-2">Support Available</p>
                <p class="text-gray-600 text-sm">Round-the-clock assistance</p>
            </div>
            
            <!-- Stat 4 -->
            <div class="bg-white p-8 rounded-lg text-center stat-item shadow-lg">
                <div class="text-5xl font-bold text-blue-600 mb-2 stat-number animate-delay-3">
                    <span class="counter" data-target="100">0</span>%
                </div>
                <p class="text-gray-900 font-bold text-lg mb-2">Satisfaction Rate</p>
                <p class="text-gray-600 text-sm">Quality guaranteed service</p>
            </div>
        </div>
    </div>
</section>

<!-- Brands/Logo Carousel Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 text-center">Trusted Brands We Install</h2>
        <p class="text-gray-600 text-center mb-12">Industry-leading equipment for your security and technology needs</p>
        
         <div class="logo-carousel">
            <div class="logo-track">
                <!-- First set of logos -->
                <div class="logo-item">
                    <img src="../uploads/logo/aboitiz.png" alt="Aboitiz">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/aozora.jpg" alt="Aozora">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/arterra.jpg" alt="Arterra">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/bangkal HS.png" alt="Bangkal High School">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/be mactan.jpg" alt="BE Mactan">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/bebohol.jpg" alt="Bohol">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/CIT.png" alt="CIT">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/CSC.png" alt="CSC">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/decahomes.png" alt="Decahomes">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/divine mercy chapel.png" alt="Divine Mercy Chapel">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/DOF BOC.png" alt="DOF BOC">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/lima estate.png" alt="Lima Estate">
                </div>
                
                <!-- Duplicate set for seamless loop -->
                <div class="logo-item">
                    <img src="../uploads/logo/lima water.png" alt="Lima Water">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/mez2 estate.png" alt="Mez2 Estate">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/naz_s fitness gym.png" alt="Naz's Fitness Gym">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/nuestra.png" alt="Nuestra">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/our lady of sacred heart.png" alt="Our Lady of Sacred Heart">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/our mother of perpetual.png" alt="Our Mother of Perpetual">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/portville.png" alt="Portville">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/punta engano.png" alt="Punta Engano">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/rockdiving.png" alt="Rock Diving">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/seaoil.png" alt="Seaoil">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/Talisay NHS.png" alt="Talisay NHS">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/valvoline.png" alt="Valvoline">
                </div>
                <div class="logo-item">
                    <img src="../uploads/logo/west cebu.png" alt="West Cebu">
                </div>
            </div>
        </div>
       
</section>

<script>
// Counter animation
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
        current += increment;
        if (current < target) {
            element.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target;
        }
    };
    
    updateCounter();
}

// Intersection Observer for counter animation
const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counters = entry.target.querySelectorAll('.counter');
            counters.forEach(counter => {
                animateCounter(counter);
            });
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe stats section
document.addEventListener('DOMContentLoaded', () => {
    const statsSection = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-4');
    if (statsSection) {
        observer.observe(statsSection);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>