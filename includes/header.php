<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - IanCris Electronics' : 'IanCris Electronics - CCTV & IT Solutions'; ?></title>
    
    <!-- Emoji Favicon (Quick solution) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📹</text></svg>">
    
    <!-- Favicon -->
    <link rel="alternate icon" href="<?php echo $base_path ?? '../'; ?>favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $base_path ?? '../'; ?>apple-touch-icon.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            background: white;
        }
        
        .nav-link {
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #3b82f6;
        }

        .cart-icon {
            transition: color 0.3s ease;
        }

        .cart-icon:hover {
            color: #3b82f6;
        }

        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .mobile-menu.active {
            max-height: 400px;
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #111827;
            margin: 5px 0;
            transition: 0.3s;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .btn-primary {
            background: white;
            color: #111827;
            border: 2px solid #111827;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #111827;
            color: white;
        }
    </style>
</head>
<body class="bg-white"></body></body>