<?php
session_start();
$page_title = "Home";
require_once 'includes/header.php';
require_once 'includes/nav.php';

// Get pending orders count if user is logged in
$pending_orders = 0;
if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status IN ('pending', 'confirmed', 'scheduled')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pending_orders = $row['count'];
}
?>

<style>
    /* Only apply overflow hidden on home page */
    body.home-page {
        overflow: hidden;
        height: 100vh;
    }
    
    .hero-background {
        background-image: url('uploads/images/Home - Herosection.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: 1;
    }
    
    .gradient-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, 
            rgba(255, 255, 255, 0.98) 0%, 
            rgba(255, 255, 255, 0.95) 15%, 
            rgba(255, 255, 255, 0.7) 40%,
            rgba(255, 255, 255, 0.3) 60%,
            rgba(255, 255, 255, 0) 100%);
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
    
    /* Ensure navbar is above hero */
    nav#main-nav {
        position: fixed;
        z-index: 50;
    }
    
    /* Hero content positioning */
    .hero-content {
        position: relative;
        z-index: 10;
        height: 100vh;
        display: flex;
        align-items: center;
    }
</style>

<script>
    // Add home-page class to body
    document.body.classList.add('home-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <!-- White Gradient Overlay -->
    <div class="gradient-overlay"></div>
    
    <!-- Hero Content -->
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-8">
        <div class="max-w-2xl">
            <h1 class="text-2xl sm:text-xl md:text-6xl font-extrabold leading-tight mb-6">
                <span class="text-blue-600 block italic animate-slide-up" style="font-style: italic;">YOUR SAFETY</span>
                <span class="text-black block animate-slide-up animate-delay-1">OUR TECHNOLOGY</span>
            </h1>
            
            <p class="text-lg sm:text-xl text-gray-700 italic font-medium mb-10 animate-slide-up animate-delay-2">
                Reliable CCTV and IT solutions for homes, offices, and businesses.
            </p>
            
            <a href="pages/products.php" class="inline-block bg-transparent text-black px-8 py-4 rounded-full font-bold uppercase text-sm border-2 border-black hover:bg-black hover:text-white transition shadow-lg animate-slide-up animate-delay-3">
                SHOP NOW
            </a>
        </div>
    </div>
</section>

<!-- AI Chatbot Widget -->
<div id="chatbot-container" class="fixed bottom-6 right-6 z-50">
    <!-- Chatbot Button -->
    <button id="chatbot-toggle" class="bg-blue-600 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-lg hover:bg-blue-700 transition-all duration-300 hover:scale-110">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
    </button>
    
    <!-- Chatbot Window -->
    <div id="chatbot-window" class="hidden absolute bottom-20 right-0 w-96 h-[500px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold">IanCris Assistant</h3>
                    <p class="text-xs text-blue-100">Online now</p>
                </div>
            </div>
            <button id="chatbot-close" class="hover:bg-blue-800 p-2 rounded-full transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Messages -->
        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-none p-3 shadow-sm max-w-[75%]">
                    <p class="text-sm text-gray-800">Hi! 👋 I'm your IanCris Electronics assistant. How can I help you today?</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="px-4 py-2 bg-white border-t border-gray-200">
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button onclick="sendQuickMessage('Show products')" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs whitespace-nowrap hover:bg-blue-200 transition">
                    🛍️ Products
                </button>
                <button onclick="sendQuickMessage('Contact info')" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs whitespace-nowrap hover:bg-blue-200 transition">
                    📞 Contact
                </button>
                <button onclick="sendQuickMessage('Gallery')" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs whitespace-nowrap hover:bg-blue-200 transition">
                    📸 Gallery
                </button>
            </div>
        </div>
        
        <!-- Input -->
        <div class="p-4 bg-white border-t border-gray-200">
            <form id="chatbot-form" class="flex gap-2">
                <input type="text" id="chatbot-input" placeholder="Type your message..." class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <button type="submit" class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-blue-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
    
    // Update cart count on page load
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        
        const cartBadge = document.getElementById('cart-count');
        if (cartBadge && totalItems > 0) {
            cartBadge.textContent = totalItems;
            cartBadge.classList.remove('hidden');
        }
    }
    
    document.addEventListener('DOMContentLoaded', updateCartCount);
</script>

<script>
// Chatbot functionality
const chatbotToggle = document.getElementById('chatbot-toggle');
const chatbotWindow = document.getElementById('chatbot-window');
const chatbotClose = document.getElementById('chatbot-close');
const chatbotForm = document.getElementById('chatbot-form');
const chatbotInput = document.getElementById('chatbot-input');
const chatbotMessages = document.getElementById('chatbot-messages');

chatbotToggle.addEventListener('click', () => {
    chatbotWindow.classList.toggle('hidden');
});

chatbotClose.addEventListener('click', () => {
    chatbotWindow.classList.add('hidden');
});

chatbotForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const message = chatbotInput.value.trim();
    if (message) {
        sendMessage(message);
        chatbotInput.value = '';
    }
});

function sendMessage(message) {
    // Add user message
    addMessage(message, 'user');
    
    // Simulate bot response
    setTimeout(() => {
        const response = getBotResponse(message.toLowerCase());
        addMessage(response, 'bot');
    }, 800);
}

function sendQuickMessage(message) {
    sendMessage(message);
}

function addMessage(text, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start gap-3 animate-slide-up';
    
    if (sender === 'user') {
        messageDiv.className += ' flex-row-reverse';
        messageDiv.innerHTML = `
            <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none p-3 shadow-sm max-w-[75%]">
                <p class="text-sm">${text}</p>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <div class="bg-white rounded-2xl rounded-tl-none p-3 shadow-sm max-w-[75%]">
                <p class="text-sm text-gray-800">${text}</p>
            </div>
        `;
    }
    
    chatbotMessages.appendChild(messageDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

function getBotResponse(message) {
    const responses = {
        'products': 'You can browse our products <a href="pages/products.php" class="text-blue-600 underline">here</a>. We offer CCTV cameras, security systems, and IT equipment.',
        'show products': 'You can browse our products <a href="pages/products.php" class="text-blue-600 underline">here</a>. We offer CCTV cameras, security systems, and IT equipment.',
        'contact': 'You can reach us at: <br>📧 Email: vensvargas1984@gmail.com<br>📞 Mobile: 0967-256-1040<br>☎️ Tel: 263-1843<br>📍 Cebu City, Philippines',
        'contact info': 'You can reach us at: <br>📧 Email: vensvargas1984@gmail.com<br>📞 Mobile: 0967-256-1040<br>☎️ Tel: 263-1843<br>📍 Cebu City, Philippines',
        'email': 'You can email us at: vensvargas1984@gmail.com',
        'phone': 'You can call us at:<br>📞 Mobile: 0967-256-1040<br>☎️ Tel: 263-1843',
        'address': 'We are located in Cebu City, Philippines',
        'location': 'We are located in Cebu City, Philippines',
        'facebook': 'Visit our Facebook page: <a href="https://www.facebook.com/kris.ian.3781" target="_blank" class="text-blue-600 underline">facebook.com/kris.ian.3781</a>',
        'fb': 'Visit our Facebook page: <a href="https://www.facebook.com/kris.ian.3781" target="_blank" class="text-blue-600 underline">facebook.com/kris.ian.3781</a>',
        'social media': 'Follow us on Facebook: <a href="https://www.facebook.com/kris.ian.3781" target="_blank" class="text-blue-600 underline">facebook.com/kris.ian.3781</a><br>📧 Email: vensvargas1984@gmail.com',
        'gallery': 'Check out our project gallery <a href="pages/gallery.php" class="text-blue-600 underline">here</a> to see our completed installations!',
        'price': 'Please contact us directly for pricing information. We offer competitive rates based on your specific needs.<br>📞 Call: 0967-256-1040',
        'help': 'I can help you with: <br>• Browse products<br>• Contact information<br>• Facebook page<br>• View gallery<br>• Learn about our services',
        'hello': 'Hello! 👋 How can I assist you today?',
        'hi': 'Hi there! 👋 How can I help you?',
        'thanks': 'You\'re welcome! 😊 Is there anything else I can help you with?',
        'thank you': 'You\'re welcome! 😊 Feel free to ask if you need anything else!'
    };
    
    for (let key in responses) {
        if (message.includes(key)) {
            return responses[key];
        }
    }
    
    return 'I\'m here to help! You can ask me about our products, contact information, Facebook page, gallery, or any other questions. 😊';
}
</script>

<!-- No footer on index page -->
</body>
</html>