<?php
session_start();
$page_title = "Terms and Conditions";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<style>
    body.terms-page {
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
    
    .animate-slide-up {
        animation: slideUp 1s ease-out forwards;
    }
    
    nav#main-nav {
        position: fixed;
        z-index: 9999 !important;
    }
    
    .hero-content {
        position: relative;
        z-index: 10;
        min-height: 40vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    .content-section {
        line-height: 1.8;
    }
    
    .content-section h2 {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1f2937;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    .content-section h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .content-section p {
        color: #4b5563;
        margin-bottom: 1rem;
    }
    
    .content-section ul {
        list-style-type: disc;
        margin-left: 1.5rem;
        margin-bottom: 1rem;
        color: #4b5563;
    }
    
    .content-section ul li {
        margin-bottom: 0.5rem;
    }
</style>

<script>
    document.body.classList.add('terms-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <div class="gradient-overlay"></div>
    
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-8">
        <div class="max-w-3xl mx-auto w-full">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight animate-slide-up">
                <span class="text-blue-600 block italic" style="font-style: italic;">TERMS &</span>
                <span class="text-black block">CONDITIONS</span>
            </h1>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-8">
        <div class="bg-gray-50 border-l-4 border-blue-600 p-6 mb-8">
            <p class="text-sm text-gray-700">
                <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
            </p>
            <p class="text-sm text-gray-700 mt-2">
                Please read these terms and conditions carefully before using our services.
            </p>
        </div>

        <div class="content-section">
            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing and using the services of Iancris Electronics ("we," "us," or "our"), you accept and agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.
            </p>

            <h2>2. Services Provided</h2>
            <p>
                Iancris Electronics provides CCTV installation, IT solutions, and related electronic services. Our services include but are not limited to:
            </p>
            <ul>
                <li>CCTV camera installation and configuration</li>
                <li>Security system design and implementation</li>
                <li>Network setup and maintenance</li>
                <li>Technical support and consultation</li>
                <li>Product sales and warranty services</li>
            </ul>

            <h2>3. User Accounts</h2>
            <h3>3.1 Registration</h3>
            <p>
                To access certain features, you may be required to register for an account. You agree to provide accurate, current, and complete information during registration and to update such information to keep it accurate, current, and complete.
            </p>
            
            <h3>3.2 Account Security</h3>
            <p>
                You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account.
            </p>

            <h2>4. Orders and Payments</h2>
            <h3>4.1 Product Orders</h3>
            <p>
                All orders are subject to acceptance and availability. We reserve the right to refuse or cancel any order for any reason, including but not limited to product availability, errors in pricing or product information, or suspected fraudulent activity.
            </p>
            
            <h3>4.2 Pricing</h3>
            <p>
                Prices for our products and services are subject to change without notice. We strive to provide accurate pricing information, but errors may occur. If a product is listed at an incorrect price, we reserve the right to refuse or cancel orders placed at that price.
            </p>
            
            <h3>4.3 Payment Terms</h3>
            <p>
                Payment must be made according to the terms specified in your order or service agreement. We accept various payment methods as displayed during checkout. Full payment may be required before service delivery or installation.
            </p>

            <h2>5. Installation Services</h2>
            <h3>5.1 Site Assessment</h3>
            <p>
                Prior to installation, we will conduct a site assessment to determine the feasibility and requirements of the project. Installation quotes are based on this assessment.
            </p>
            
            <h3>5.2 Installation Schedule</h3>
            <p>
                Installation dates and times will be agreed upon mutually. We will make reasonable efforts to meet the agreed schedule, but delays may occur due to unforeseen circumstances.
            </p>
            
            <h3>5.3 Site Access</h3>
            <p>
                You agree to provide safe and adequate access to the installation site. You are responsible for ensuring that the site is ready for installation as specified in the service agreement.
            </p>

            <h2>6. Warranties and Guarantees</h2>
            <h3>6.1 Product Warranty</h3>
            <p>
                Products sold by us are covered by the manufacturer's warranty unless otherwise stated. The warranty period and terms vary by product and manufacturer.
            </p>
            
            <h3>6.2 Service Warranty</h3>
            <p>
                We warrant our installation and service work for a period specified in your service agreement. This warranty covers defects in workmanship but does not cover damage caused by misuse, accidents, or unauthorized modifications.
            </p>
            
            <h3>6.3 Warranty Exclusions</h3>
            <p>
                Warranties do not cover:
            </p>
            <ul>
                <li>Normal wear and tear</li>
                <li>Damage caused by accidents, misuse, or abuse</li>
                <li>Unauthorized repairs or modifications</li>
                <li>Acts of nature or force majeure events</li>
                <li>Failure to follow maintenance instructions</li>
            </ul>

            <h2>7. Limitation of Liability</h2>
            <p>
                To the maximum extent permitted by law, Iancris Electronics shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including but not limited to loss of profits, data, or business opportunities arising from:
            </p>
            <ul>
                <li>Use or inability to use our services</li>
                <li>Equipment malfunction or failure</li>
                <li>Security breaches despite installed systems</li>
                <li>Any errors or omissions in technical or service information</li>
            </ul>

            <h2>8. Privacy and Data Protection</h2>
            <p>
                Your use of our services is also governed by our Privacy Policy. By using our services, you consent to the collection and use of your information as described in our Privacy Policy.
            </p>

            <h2>9. Intellectual Property</h2>
            <p>
                All content on our website and in our materials, including but not limited to text, graphics, logos, images, and software, is the property of Iancris Electronics or its content suppliers and is protected by copyright and other intellectual property laws.
            </p>

            <h2>10. Cancellation and Refunds</h2>
            <h3>10.1 Order Cancellation</h3>
            <p>
                Orders may be cancelled before shipment or installation begins. Cancellation fees may apply depending on the stage of order processing.
            </p>
            
            <h3>10.2 Service Cancellation</h3>
            <p>
                Scheduled services may be cancelled with at least 48 hours notice. Late cancellations may be subject to cancellation fees.
            </p>
            
            <h3>10.3 Refunds</h3>
            <p>
                Refunds will be processed according to our refund policy. Custom orders and installed systems may not be eligible for refunds.
            </p>

            <h2>11. Dispute Resolution</h2>
            <p>
                Any disputes arising from these Terms and Conditions or our services shall be resolved through good faith negotiation. If negotiation fails, disputes shall be subject to the jurisdiction of the courts in Cebu, Philippines.
            </p>

            <h2>12. Changes to Terms</h2>
            <p>
                We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting on our website. Your continued use of our services after changes are posted constitutes acceptance of the modified terms.
            </p>

            <h2>13. Contact Information</h2>
            <p>
                If you have any questions about these Terms and Conditions, please contact us:
            </p>
            <ul>
                <li><strong>Phone:</strong> +63 912 345 6789</li>
                <li><strong>Email:</strong> info@iancriselectronics.com</li>
                <li><strong>Facebook:</strong> @iancriselectronics</li>
            </ul>

            <div class="mt-12 pt-8 border-t-2 border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    By using our services, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
