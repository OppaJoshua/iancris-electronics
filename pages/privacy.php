<?php
session_start();
$page_title = "Privacy Policy";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<style>
    body.privacy-page {
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
    document.body.classList.add('privacy-page');
</script>

<!-- Hero Section -->
<section class="hero-background">
    <div class="gradient-overlay"></div>
    
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-8">
        <div class="max-w-3xl mx-auto w-full">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight animate-slide-up">
                <span class="text-blue-600 block italic" style="font-style: italic;">PRIVACY</span>
                <span class="text-black block">POLICY</span>
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
                Your privacy is important to us. This Privacy Policy explains how we collect, use, and protect your personal information.
            </p>
        </div>

        <div class="content-section">
            <h2>1. Introduction</h2>
            <p>
                Iancris Electronics ("we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website, use our services, or interact with us.
            </p>

            <h2>2. Information We Collect</h2>
            <h3>2.1 Personal Information</h3>
            <p>
                We may collect personal information that you voluntarily provide to us when you:
            </p>
            <ul>
                <li>Register for an account</li>
                <li>Place an order for products or services</li>
                <li>Contact us for support or inquiries</li>
                <li>Subscribe to our newsletter or marketing communications</li>
                <li>Participate in surveys or promotions</li>
            </ul>
            <p>
                This information may include:
            </p>
            <ul>
                <li>Name (first and last)</li>
                <li>Email address</li>
                <li>Phone number</li>
                <li>Physical address</li>
                <li>Payment information</li>
                <li>Account credentials</li>
            </ul>

            <h3>2.2 Automatically Collected Information</h3>
            <p>
                When you visit our website, we may automatically collect certain information about your device and browsing activity:
            </p>
            <ul>
                <li>IP address and location data</li>
                <li>Browser type and version</li>
                <li>Operating system</li>
                <li>Pages visited and time spent on pages</li>
                <li>Referring website addresses</li>
                <li>Device information</li>
            </ul>

            <h3>2.3 Cookies and Tracking Technologies</h3>
            <p>
                We use cookies, web beacons, and similar tracking technologies to enhance your experience, analyze site usage, and assist in our marketing efforts. You can control cookie preferences through your browser settings.
            </p>

            <h2>3. How We Use Your Information</h2>
            <p>
                We use the information we collect for various purposes, including:
            </p>
            <ul>
                <li><strong>Service Delivery:</strong> To process orders, provide installations, and deliver products/services</li>
                <li><strong>Account Management:</strong> To create and manage your account, authenticate users, and provide customer support</li>
                <li><strong>Communication:</strong> To send order confirmations, updates, technical notices, and respond to inquiries</li>
                <li><strong>Marketing:</strong> To send promotional materials, newsletters, and special offers (with your consent)</li>
                <li><strong>Improvement:</strong> To analyze usage patterns, improve our website, and develop new features</li>
                <li><strong>Security:</strong> To protect against fraud, unauthorized access, and ensure system security</li>
                <li><strong>Legal Compliance:</strong> To comply with legal obligations and enforce our terms</li>
            </ul>

            <h2>4. How We Share Your Information</h2>
            <p>
                We do not sell your personal information to third parties. We may share your information in the following circumstances:
            </p>
            
            <h3>4.1 Service Providers</h3>
            <p>
                We may share information with trusted third-party service providers who assist us in operating our business, such as:
            </p>
            <ul>
                <li>Payment processors</li>
                <li>Shipping and delivery services</li>
                <li>Cloud hosting providers</li>
                <li>Email service providers</li>
                <li>Analytics services</li>
            </ul>

            <h3>4.2 Legal Requirements</h3>
            <p>
                We may disclose your information if required by law, court order, or government regulation, or to protect our rights, property, or safety.
            </p>

            <h3>4.3 Business Transfers</h3>
            <p>
                In the event of a merger, acquisition, or sale of assets, your information may be transferred to the acquiring entity.
            </p>

            <h2>5. Data Security</h2>
            <p>
                We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
            </p>
            <ul>
                <li>Encryption of sensitive data (SSL/TLS)</li>
                <li>Secure password storage (hashing)</li>
                <li>Regular security assessments</li>
                <li>Access controls and authentication</li>
                <li>Employee training on data protection</li>
            </ul>
            <p>
                However, no method of transmission over the internet or electronic storage is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.
            </p>

            <h2>6. Data Retention</h2>
            <p>
                We retain your personal information only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. When information is no longer needed, we will securely delete or anonymize it.
            </p>

            <h2>7. Your Privacy Rights</h2>
            <p>
                Depending on your location, you may have certain rights regarding your personal information:
            </p>
            <ul>
                <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
                <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                <li><strong>Restriction:</strong> Request restriction of processing in certain circumstances</li>
                <li><strong>Portability:</strong> Request transfer of your data to another service</li>
                <li><strong>Objection:</strong> Object to processing of your information for certain purposes</li>
                <li><strong>Withdraw Consent:</strong> Withdraw consent for marketing communications at any time</li>
            </ul>
            <p>
                To exercise these rights, please contact us using the information provided below.
            </p>

            <h2>8. Third-Party Links</h2>
            <p>
                Our website may contain links to third-party websites or services. We are not responsible for the privacy practices of these external sites. We encourage you to review their privacy policies before providing any personal information.
            </p>

            <h2>9. Children's Privacy</h2>
            <p>
                Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If we become aware that we have collected information from a child, we will take steps to delete it promptly.
            </p>

            <h2>10. International Data Transfers</h2>
            <p>
                Your information may be transferred to and processed in countries other than your country of residence. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.
            </p>

            <h2>11. Email Marketing</h2>
            <p>
                If you subscribe to our marketing communications, we will send you promotional emails about our products, services, and special offers. You can unsubscribe at any time by:
            </p>
            <ul>
                <li>Clicking the "unsubscribe" link in any marketing email</li>
                <li>Updating your email preferences in your account settings</li>
                <li>Contacting us directly</li>
            </ul>

            <h2>12. Changes to This Privacy Policy</h2>
            <p>
                We may update this Privacy Policy from time to time to reflect changes in our practices or for legal, regulatory, or operational reasons. We will notify you of any material changes by:
            </p>
            <ul>
                <li>Posting the updated policy on our website with a new "Last Updated" date</li>
                <li>Sending you an email notification (for significant changes)</li>
            </ul>
            <p>
                Your continued use of our services after changes are posted constitutes acceptance of the updated Privacy Policy.
            </p>

            <h2>13. Contact Us</h2>
            <p>
                If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:
            </p>
            <ul>
                <li><strong>Email:</strong> info@iancriselectronics.com</li>
                <li><strong>Phone:</strong> +63 912 345 6789</li>
                <li><strong>Facebook:</strong> @iancriselectronics</li>
            </ul>

            <h2>14. Consent</h2>
            <p>
                By using our website and services, you consent to the collection and use of your information as described in this Privacy Policy. If you do not agree with this policy, please do not use our services.
            </p>

            <div class="mt-12 pt-8 border-t-2 border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    Thank you for trusting Iancris Electronics with your personal information. We are committed to protecting your privacy and providing transparent information about our data practices.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
