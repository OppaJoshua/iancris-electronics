<?php
session_start();
$page_title = "Contact Us";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<style>
    body.contact-page {
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
    
    .animate-slide-up {
        animation: slideUp 1s ease-out forwards;
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    
    .animate-delay-1 { opacity: 0; animation-delay: 0.1s; }
    .animate-delay-2 { opacity: 0; animation-delay: 0.2s; }
    .animate-delay-3 { opacity: 0; animation-delay: 0.3s; }
    
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
    
    .contact-card {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
    }
    
    .contact-card:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        background-color: #dbeafe;
    }
</style>

<script>
    document.body.classList.add('contact-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <div class="gradient-overlay"></div>
    
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-8">
        <div class="max-w-3xl mx-auto w-full">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-6 animate-slide-up">
                <span class="text-blue-600 block italic" style="font-style: italic;">GET IN</span>
                <span class="text-black block">TOUCH</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-700 animate-slide-up animate-delay-1">
                We're here to help with your security needs
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-8">
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            
            <!-- Phone Card -->
            <div class="contact-card animate-fade-in animate-delay-1">
                <div class="icon-circle">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Phone</h3>
                <a href="tel:+639123456789" class="block text-center text-base text-blue-600 hover:text-blue-700 font-medium transition">
                    +63 912 345 6789
                </a>
            </div>

            <!-- Facebook Card -->
            <div class="contact-card animate-fade-in animate-delay-2">
                <div class="icon-circle">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Facebook</h3>
                <a href="https://facebook.com/iancriselectronics" target="_blank" class="block text-center text-base text-blue-600 hover:text-blue-700 font-medium transition">
                    @iancriselectronics
                </a>
            </div>

            <!-- Email Card -->
            <div class="contact-card animate-fade-in animate-delay-3">
                <div class="icon-circle">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Email</h3>
                <a href="mailto:info@iancriselectronics.com" class="block text-center text-base text-blue-600 hover:text-blue-700 font-medium transition break-all">
                    info@iancriselectronics.com
                </a>
            </div>

        </div>

        <!-- Business Hours -->
        <div class="text-center border-t-2 border-gray-200 pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Business Hours</h2>
            <div class="space-y-2 text-base text-gray-700 max-w-md mx-auto">
                <p><span class="font-semibold">Monday - Friday:</span> 8:00 AM - 6:00 PM</p>
                <p><span class="font-semibold">Saturday:</span> 9:00 AM - 5:00 PM</p>
                <p><span class="font-semibold">Sunday:</span> Closed</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
