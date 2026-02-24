<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOOC BPS - Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
        }
        
        /* ===== FIXED NAVBAR STYLES ===== */
        .main-nav {
            background: rgba(255, 255, 255, 0.98);
            padding: 15px 60px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #1e3c72;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .nav-brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e3c72;
            text-decoration: none;
            position: relative;
            padding-right: 25px;
        }

        .nav-brand::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60px;
            width: 1.5px;
            background: linear-gradient(to bottom, rgba(42, 82, 152, 0.7));
            border-radius: 2px;
        }
        
        .nav-brand span {
            color: #2a5298;
        }
        
        /* Logo MOOC BPS sebagai gambar */
        .logo-image {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
        }
        
        .logo-image:hover {
            transform: scale(1.05);
        }
        
        .nav-menu {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Style untuk ikon navigasi */
        .nav-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e3c72;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(30, 60, 114, 0.1);
        }

        .nav-icon:hover {
            background: rgba(30, 60, 114, 0.2);
            color: #2a5298;
            transform: scale(1.1);
        }

        /* Badge notifikasi */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .nav-item {
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            color: #1e3c72;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .nav-item:hover, .nav-item.active {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            transform: translateY(-2px);
        }

        /* Mobile Menu Button - Hidden by default */
        .mobile-menu-btn {
            display: none !important;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #1e3c72;
            padding: 5px 10px;
            cursor: pointer;
            z-index: 1001;
        }

        /* User Profile & Avatar Styles - DIPERBAIKI */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(30, 60, 114, 0.05);
            transition: all 0.3s ease;
            margin-left: 15px;
            text-decoration: none;
            border: 1px solid rgba(30, 60, 114, 0.1);
        }

        .user-profile:hover {
            background: rgba(30, 60, 114, 0.1);
            text-decoration: none;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-initials {
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            color: #1e3c72;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
            line-height: 1.2;
        }

        .user-status {
            font-size: 0.7rem;
            color: #5a6c7d;
            display: flex;
            align-items: center;
            gap: 4px;
            line-height: 1.2;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background: #28a745;
            border-radius: 50%;
            display: inline-block;
        }

        /* CSS untuk Fallback Image */
        .avatar-image[src=""],
        .avatar-image:not([src]) {
            opacity: 0;
        }

        .avatar-image:not([src]) + .avatar-initials,
        .avatar-image[src=""] + .avatar-initials {
            display: flex !important;
        }
        
        /* Slider Styles - Tanpa Border Kotak */
        .slider-container {
            position: flex;
            max-width: 1900px;
            margin: 0 auto;
            overflow: hidden;
            margin-bottom: 100px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .slider {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slider-arrow.prev {
            left: 25px;
        }
        
        .slider-arrow.next {
            right: 25px;
        }
        
        .slide {
            min-width: 100%;
            display: flex;
            flex-direction: column;
            padding: 60px 90px;
            background: transparent;
        }
        
        .slide-content {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 80px;
            align-items: start;
        }
        
        .text-content {
            text-align: left;
            padding-top: 20px;
        }
        
        .image-content {
            text-align: right;
            position: relative;
            margin-right: 50px;
            margin-top: 10px;
        }
        
        .welcome-title {
            font-size: 3.4rem;
            font-weight: 800;
            color: white;
            margin-bottom: 30px;
            line-height: 1.2;
        }
        
        .welcome-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            line-height: 1.7;
        }
        
        .divider {
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            margin: 35px 0;
            max-width: 180px;
        }
        
        
        .MOOC-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Profile Image - Larger and Right Aligned */
        .profile-image-container {
            position: relative;
            display: inline-block;
            margin-right: -50px;
        }
        
        .profile-image {
            width: 500px;
            height: 600px;
            border-radius: 25px;
            object-fit: cover;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border: 8px solid rgba(255, 255, 255, 0.25);
            transition: transform 0.3s ease;
        }
        
        .profile-image:hover {
            transform: scale(1.02);
        }
        
        .image-decoration {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            top: 20px;
            right: -20px;
            z-index: -1;
        }
        
        /* Slider Navigation */
        .slider-nav {
            position: absolute;
            bottom: 30px;
            left: -50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            z-index: 10;
        }
        
        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .slider-dot.active {
            background: white;
            transform: scale(1.2);
        }
        
        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        
        .slider-arrow:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }
        
        .slider-arrow.prev {
            left: 25px;
        }
        
        .slider-arrow.next {
            right: 25px;
        }
        
        /* Stats Mini */
        .stats-mini {
            display: flex;
            gap: 25px;
            margin-top: 40px;
            justify-content: flex-start;
        }
        
        .stat-mini-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px 25px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 120px;
        }
        
        .stat-mini-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            display: block;
        }
        
        .stat-mini-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
        }
        
        /* Floating Elements */
        .floating-elements {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
        }
        
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 75%;
            left: 85%;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 25%;
            left: 90%;
            animation-delay: 4s;
        }
        
        .floating-element:nth-child(4) {
            width: 70px;
            height: 70px;
            top: 60%;
            left: 10%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-25px) rotate(180deg);
            }
        }
        
        /* Additional Content untuk Scroll */
        .additional-content {
            max-width: 1900px;
            color: white;
        }
        
        .content-grid {
            margin-left: 30px;
            margin-right: 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .content-icon {
            font-size: 3rem;
            color: white;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .content-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: white;
        }
        
        .content-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 50px;
            color: white;
        }
        
        /* Footer */
        .main-footer {
            background: #1a365d;
            color: white;
            padding: 50px 0 25px;
            margin-top: 100px;
            width: 100%;
            font-size: 14px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
            border-bottom: 2px solid #2d74da;
            padding-bottom: 8px;
        }

        .footer-section h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: white;
        }

        .footer-address {
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .footer-address p {
            margin-bottom: 8px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 14px;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: underline;
        }

        .footer-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            margin: 30px 0;
        }

        .footer-bottom {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .bps-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }

        .contact-info {
            margin-top: 15px;
        }

        .contact-info p {
            margin-bottom: 5px;
        }

        .berakhlak-container {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .berakhlak-image {
            max-width: 200px;
        }

        .berakhlak-links {
            list-style: none;
            margin-top: 10px;
        }

        .berakhlak-links li {
            margin-bottom: 8px;
        }

        .berakhlak-links a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 14px;
        }

        .berakhlak-links a:hover {
            color: white;
            text-decoration: underline;
        }

        /* ===== RESPONSIVE FIXES ===== */
        @media (max-width: 1200px) {
            .main-nav {
                padding: 12px 20px;
            }
            
            .nav-menu {
                gap: 8px;
            }
            
            .nav-item {
                padding: 4px 7px;
                font-size: 0.9rem;
            }
            
            .slide-content {
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
            }
            
            .text-content {
                text-align: center;
                padding-top: 0;
            }
            
            .image-content {
                text-align: center !important;
            }
            
            .profile-image {
                width: 400px;
                height: 500px;
            }
            
            .stats-mini {
                justify-content: center;
            }
            
            /* Sedikit menyesuaikan untuk tablet */
            .stat-mini-item {
                padding: 18px 20px;
            }
            
            .stat-mini-number {
                font-size: 1.6rem;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .footer-content {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }
        }

        @media (max-width: 992px) {
            .nav-menu {
                display: none !important;
                position: absolute;
                top: 100%;
                left: -48px;
                right: 0;
                width: 100%; /* Pastikan full width */
                max-width: 100%; /* Pastikan tidak dibatasi */
                background: rgba(255, 255, 255, 0.98);
                flex-direction: column;
                padding: 20px;
                gap: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                border-top: 1px solid rgba(30, 60, 114, 0.1);
                
                /* TAMBAHKAN INI: */
                align-items: flex-start !important; /* Pastikan item rata kiri */
            }
            
            .nav-menu.show {
                display: flex !important;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
            
            .nav-item {
                text-align: left;
                padding: 12px 20px;
                border-radius: 10px;
                justify-content: flex-start;
                width: 100%; /* Pastikan item memenuhi lebar */
            }
        }

        @media (max-width: 768px) {
            .nav-menu {
                gap: 5px;
            }
            
            .logo-image {
                height: 45px;
            }
            
            .user-profile {
                margin-left: 10px;
                padding: 5px 10px;
            }
            
            .user-avatar {
                width: 35px;
                height: 35px;
            }
            
            .avatar-initials {
                font-size: 0.75rem;
            }
            
            .user-name {
                font-size: 0.8rem;
                max-width: 100px;
            }
            
            .user-status {
                font-size: 0.65rem;
            }

            .nav-item {
                padding: 8px 15px;
                font-size: 0.9rem;
            }

            .nav-icon {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .slider-container {
                margin-bottom: 50px;
            }
            
            .slide {
                padding: 30px 20px !important;
                min-height: auto;
            }
            
            .slide-content {
                grid-template-columns: 1fr !important;
                gap: 30px !important;
                text-align: center;
            }
            
            .text-content {
                padding-top: 0 !important;
                order: 2;
            }
            
            .image-content {
                text-align: center;
            }
            
            .profile-image-container {
                margin-right: 0 !important;
                display: flex;
                justify-content: center;
            }
            
            .profile-image {
                width: 280px !important;
                height: 350px !important;
                max-width: 100%;
            }
            
            .image-decoration {
                display: none;
            }
            
            .welcome-title {
                font-size: 2rem !important;
                margin-bottom: 20px !important;
            }
            
            .welcome-subtitle {
                font-size: 1rem !important;
                margin-bottom: 25px !important;
            }
            
            .MOOC-badge {
                font-size: 0.9rem !important;
                padding: 10px 20px !important;
            }
            
            .stats-mini {
                display: flex;
                justify-content: center;
                align-items: stretch;
                gap: clamp(10px, 2vw, 15px);
                margin-top: 25px !important;
                width: 100%;
            }
            
            .stat-mini-item {
                flex: 1;
                min-width: 0;
                padding: clamp(10px, 2.5vw, 14px) clamp(8px, 2vw, 12px) !important;
                border-radius: 12px; /* Tetap rounded, ukuran proporsional */
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .stat-mini-number {
                font-size: clamp(1.2rem, 4vw, 1.5rem) !important;
                font-weight: 700;
                line-height: 1.2;
            }
            
            .stat-mini-label {
                font-size: clamp(0.7rem, 2.5vw, 0.8rem) !important;
                margin-top: 4px;
                line-height: 1.2;
            }
            
            .slider-arrow {
                width: 40px !important;
                height: 40px !important;
                font-size: 1.2rem !important;
            }
            
            .slider-arrow.prev {
                left: 10px !important;
            }
            
            .slider-arrow.next {
                right: 10px !important;
            }
            
            .slider-nav {
                bottom: 15px !important;
            }

            .content-grid {
                grid-template-columns: 1fr !important;
                gap: 20px;
                margin: 0 15px 50px 15px !important;
            }
            
            .content-card {
                padding: 25px 20px !important;
            }
            
            .content-icon {
                font-size: 2.5rem !important;
            }
            
            .content-title {
                font-size: 1.2rem !important;
            }
            
            .section-title {
                font-size: 1.8rem !important;
                margin-bottom: 30px !important;
                padding: 0 20px;
            }

            .footer-content {
                grid-template-columns: 1fr !important;
                gap: 30px;
            }
            
            .footer-section {
                text-align: left !important; /* Ubah dari center ke left */
                order: 0; /* Reset order */
            }
            
            /* Urutkan section sesuai kebutuhan */
            .footer-section:first-child {
                order: 1; /* bps-title + footer-address + berakhlak-container */
            }
            
            .footer-section:nth-child(3) {
                order: 2; /* Tentang Kami */
            }
            
            .footer-section:nth-child(2) {
                order: 3; /* Kontak Kami */
            }
            
            /* Style untuk elemen dalam section pertama */
            .bps-title {
                text-align: left;
                margin-bottom: 15px;
            }
            
            .footer-address {
                text-align: left;
                margin-bottom: 20px;
            }
            
            .berakhlak-container {
                text-align: left;
                margin-top: 20px;
            }
            
            .berakhlak-image {
                max-width: 150px !important;
                margin: 0 !important; /* Hilangkan margin center */
            }
            
            /* Style untuk section lainnya */
            .footer-section h3 {
                text-align: left;
                margin-bottom: 15px;
            }
            
            .contact-info {
                text-align: left;
            }
            
            .footer-links {
                text-align: left;
                padding-left: 0;
                list-style-position: inside;
            }
            
            .footer-links li {
                margin-bottom: 8px;
            }
            
            .footer-links a {
                text-align: left;
                justify-content: flex-start !important;
            }
            
            .main-footer {
                margin-top: 50px !important;
                padding: 30px 0 15px !important;
            }
            
            .footer-bottom {
                text-align: left !important;
                margin-top: 30px;
            }
            
            .copyright {
                text-align: left;
            }
        }

        @media (max-width: 576px) {
            .user-info {
                display: none;
            }
            
            .user-profile {
                padding: 6px;
                background: transparent;
                border: none;
            }
            
            .user-profile:hover {
                background: rgba(30, 60, 114, 0.1);
            }

            .main-nav {
                padding: 10px 15px !important;
            }
            
            .logo-image {
                height: 40px !important;
            }
            
            .slide {
                padding: 20px 15px !important;
            }
            
            .profile-image {
                width: 250px !important;
                height: 300px !important;
            }
            
            .welcome-title {
                font-size: 1.8rem !important;
            }
            
            .stat-mini-number {
                font-size: 1.5rem !important;
            }
            
            .content-card {
                padding: 20px 15px !important;
            }
        }

        /* Touch-friendly improvements */
        @media (max-width: 768px) {
            .nav-icon, 
            .slider-arrow, 
            .slider-dot,
            .content-card,
            .stat-mini-item {
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
            }
            
            .nav-icon,
            .slider-arrow {
                min-width: 44px;
                min-height: 44px;
            }
            
            button, 
            .btn-simple {
                min-height: 44px;
                padding: 12px 20px;
            }
        }

        /* Prevent horizontal scroll */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }

        /* Improve text readability on mobile */
        @media (max-width: 768px) {
            body {
                font-size: 14px;
                line-height: 1.5;
            }
            
            .welcome-subtitle,
            .content-description {
                line-height: 1.6;
            }
        }

        @media (max-width: 1000px) {
            /* ATUR URUTAN ELEMEN SPECIFIC */
            .slide-content {
                display: grid !important;
                grid-template-columns: 1fr !important;
                text-align: left !important;
                gap: 15px !important;
                margin:auto;
            }
            
            /* URUTAN SESUAI KEINGINAN ANDA */
            .MOOC-badge {
                order: 1 !important;
                grid-row: 1 !important;
            }
            
            .welcome-title {
                order: 2 !important;
                grid-row: 2 !important;
            }
            
            .welcome-subtitle {
                order: 3 !important;
                grid-row: 3 !important;
            }
            
            .image-content {
                order: 4 !important;
                grid-row: 4 !important;
                text-align: center !important;
                margin-right: 0px;
                margin-top: 3px;
            }
            
            .divider {
                order: 5 !important;
                grid-row: 5 !important;
                margin: 25px auto !important; /* PERUBAHAN: auto untuk center, 25px atas-bawah */
                max-width: 150px; /* Sedikit lebih kecil di mobile */
            }
            
            .stats-mini {
                order: 6 !important;
                grid-row: 6 !important;
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 25px !important;
            }
            
            .stat-mini-item {
                flex: 1;
                min-width: 0;
                padding: 15px 10px !important;
                border-radius: 15px; /* Sedikit lebih kecil */
            }
            
            .stat-mini-number {
                font-size: 1.5rem !important;
            }
            
            .stat-mini-label {
                font-size: 0.8rem !important;
            }
        }

        /* Mobile kecil (576px ke bawah) - Tetap berjejer 3 */
        @media (max-width: 576px) {
            .stats-mini {
            gap: 8px;
            padding: 0 5px;
        }
        
        .stat-mini-item {
            padding: 10px 6px !important;
            border-radius: 10px; /* Lebih kecil tapi tetap rounded */
        }
        
        .stat-mini-number {
            font-size: clamp(1.1rem, 3.5vw, 1.3rem) !important;
        }
        
        .stat-mini-label {
            font-size: clamp(0.65rem, 2vw, 0.75rem) !important;
        }
        }

        /* ===== PERBAIKAN KHUSUS UNTUK 400px KE BAWAH (DEVICE SANGAT KECIL) ===== */
    @media (max-width: 400px) {
        .main-nav {
            padding: 8px 8px !important;
        }
        
        .logo-image {
            height: 28px !important;
            margin-left: 4px !important;
            margin-right: 8px !important;
        }
        
        /* JANGAN SEMBUNYIKAN ICON WORLD - semua icon tetap ada */
        .nav-icon {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.8rem !important;
            margin-right: 4px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* PERBAIKAN: Lingkaran biru pada icon world dan notif - HAPUS background color */
        .nav-icon {
            background-color: transparent !important; /* HAPUS lingkaran biru */
            width: 12px;
            height: 12px;
        }
        
        /* PERBAIKAN: Icon sendiri yang berwarna biru */
        .nav-icon i {
            font-size: 0.9rem !important;
        }
        
        .notification-badge {
            width: 12px;
            height: 12px;
            font-size: 0.5rem;
            top: 6px;
            right: 6px;
        }
        
        .user-avatar {
            width: 28px !important;
            height: 28px !important;
        }
        
        .avatar-initials {
            font-size: 0.6rem !important;
        }
        
        /* PERBAIKAN: Tambah jarak untuk hamburger menu */
        .mobile-menu-btn {
            font-size: 1.1rem;
            padding: 4px 4px;
            margin-left: 15px !important; /* TAMBAH jarak dari 4px ke 10px */
        }
        
        /* PERBAIKAN: Garis separator - sesuaikan posisi */
        .nav-brand::after {
            height: 28px;
            width: 2px;
            background-color: #d1d5db;
            right: -4px !important; /* PERUBAHAN: dari -8px ke -4px */
            opacity: 0.7;
            position: absolute;
            content: '';
        }
        
        /* PERBAIKAN: Atur posisi container logo */
        .nav-brand {
            position: relative;
            padding-right: 8px !important; /* KURANGI dari 12px ke 8px */
        }
        
        /* Kurangi gap di user profile */
        .user-profile {
            gap: 6px;
            padding: 4px;
        }
    }
    </style>
</head>
<body>
    <!-- Navigation - Sticky -->
    <nav class="main-nav">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Logo MOOC BPS sebagai gambar -->
                <a href="#" class="nav-brand">
                    <img src="{{ asset('img/Logo_E-Learning.png') }}" alt="MOOC BPS Logo" class="logo-image">
                </a>
                <div class="nav-menu ms-5">
                    <a href="#" class="nav-item active">Beranda</a>
                    <a href="{{ route('mitra.dashboard') }}" class="nav-item">Dashboard</a>
                    <a href="{{ route('mitra.kursus.index') }}" class="nav-item">Kursus</a>
                    <a href="{{ route('mitra.kursus.saya') }}" class="nav-item">Kursus Saya</a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <!-- Tambahkan bagian ikon di sini -->
            <div class="d-flex align-items-center">
                
                <!-- User Profile dengan Foto - VERSI DIPERBAIKI -->
                <a href="{{ route('profil.index') }}" class="user-profile">
                    <div class="user-avatar">
                        @auth
                            @php
                                $user = auth()->user();
                                $biodata = $user->biodata ?? null;
                                $initials = strtoupper(substr($user->name, 0, 2));
                            @endphp
                            
                            @if($biodata && $biodata->foto_profil)
                                <img src="{{ asset('storage/' . $biodata->foto_profil) }}" 
                                     alt="Foto Profil" 
                                     class="avatar-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-initials" style="display: none;">{{ $initials }}</div>
                            @else
                                <div class="avatar-initials">{{ $initials }}</div>
                            @endif
                        @endauth
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            {{ auth()->user()->biodata->nama_lengkap ?? auth()->user()->name }}
                        </div>
                        <div class="user-status">
                            <span class="status-dot"></span>
                            Online
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <!-- Floating Elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <!-- Main Content - Enhanced with Scroll -->
    <main class="main-content">
        <!-- Slider Container - Tanpa Border Kotak -->
        <div class="slider-container">
            <div class="slider">
                <!-- Slide 1 - Welcome -->
                <div class="slide">
                    <div class="slide-content">
                        <!-- Text Content -->
                        <div class="text-content">
                            <div class="MOOC-badge">
                                MOOC BPS - Massive Open Online Course
                            </div>
                            
                            <h1 class="welcome-title">
                                Selamat Datang, di MOOC BPS!
                            </h1>
                            
                            <p class="welcome-subtitle">
                                Platform pembelajaran mandiri bagi Mitra Statistik untuk mempersiapkan diri sebelum pelatihan tatap muka dan penugasan di lapangan dengan sistem belajar adaptif.
                            </p>
                            
                            <div class="divider"></div>
                            

                            <!-- Mini Stats -->
                            <div class="stats-mini">
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $pesertaAktif }}</span>
                                    <span class="stat-mini-label">Peserta Aktif</span>
                                </div>
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $kursusTersedia }}</span>
                                    <span class="stat-mini-label">Kursus Tersedia</span>
                                </div>
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $materiOnline }}</span>
                                    <span class="stat-mini-label">Materi Online</span>
                                </div>
                            </div>
                        </div>

                        <!-- Image Content - Larger and Right Aligned -->
                        <div class="image-content">
                            <div class="profile-image-container">
                                <div class="image-decoration"></div>
                                <!-- GANTI PATH GAMBAR DI BAWAH INI -->
                                <img src="/img/foto.webp" alt="Mentor MOOC BPS" class="profile-image">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2 - Course Features -->
                <div class="slide">
                    <div class="slide-content">
                        <!-- Text Content -->
                        <div class="text-content">
                            <div class="MOOC-badge">
                                Fitur Unggulan MOOC BPS
                            </div>
                            
                            <h1 class="welcome-title">
                                Pembelajaran Terstruktur
                            </h1>
                            
                            <p class="welcome-subtitle">
                                Seluruh materi disusun berdasarkan kebutuhan kompetensi mitra, mulai dari konsep dasar hingga prosedur pendataan di lapangan yang relevan dan terstruktur.
                            </p>
                            
                            <div class="divider"></div>
                        

                            <!-- Mini Stats -->
                            <div class="stats-mini">
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $pesertaAktif }}</span>
                                    <span class="stat-mini-label">Peserta Aktif</span>
                                </div>
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $kursusTersedia }}</span>
                                    <span class="stat-mini-label">Kursus Tersedia</span>
                                </div>
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $materiOnline }}</span>
                                    <span class="stat-mini-label">Materi Online</span>
                                </div>
                            </div>
                        </div>

                        <!-- Image Content - Larger and Right Aligned -->
                        <div class="image-content">
                            <div class="profile-image-container">
                                <div class="image-decoration"></div>
                                <!-- GANTI PATH GAMBAR DI BAWAH INI -->
                                <img src="/img/foto1.webp" alt="Fitur MOOC BPS" class="profile-image">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3 - Community -->
                <div class="slide">
                    <div class="slide-content">
                        <!-- Text Content -->
                        <div class="text-content">
                            <div class="MOOC-badge">
                                Progress Pembelajaran
                            </div>
                            
                            <h1 class="welcome-title">
                                Pantau Progress Kursus Anda
                            </h1>
                            
                            <p class="welcome-subtitle">
                                Setiap mitra dapat memantau progres belajar dan menyelesaikan evaluasi sebagai syarat sebelum mengikuti pelatihan dan bertugas sebagai mitra yang baik.
                            </p>
                            
                            <div class="divider"></div>
                        

                            <!-- Mini Stats -->
                            <div class="stats-mini">
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $pesertaAktif }}</span>
                                    <span class="stat-mini-label">Peserta Aktif</span>
                                </div>
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $kursusTersedia }}</span>
                                    <span class="stat-mini-label">Kursus Tersedia</span>
                                </div>
                                <div class="stat-mini-item">
                                    <span class="stat-mini-number">{{ $materiOnline }}</span>
                                    <span class="stat-mini-label">Materi Online</span>
                                </div>
                            </div>
                        </div>

                        <!-- Image Content - Larger and Right Aligned -->
                        <div class="image-content">
                            <div class="profile-image-container">
                                <div class="image-decoration"></div>
                                <!-- GANTI PATH GAMBAR DI BAWAH INI -->
                                <img src="img/foto2.webp" alt="Komunitas MOOC BPS" class="profile-image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slider Navigation -->
            <div class="slider-arrow prev">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="slider-arrow next">
                <i class="fas fa-chevron-right"></i>
            </div>
            
            <div class="slider-nav">
                <div class="slider-dot active"></div>
                <div class="slider-dot"></div>
                <div class="slider-dot"></div>
            </div>
        </div>

        <!-- Additional Content untuk Scroll -->
        <div class="additional-content">
            <h2 class="section-title">Apa Yang Mitra Dapatkan?</h2>
            
            <div class="content-grid">
                <div class="content-card">
                    <div class="content-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="content-title">Materi Terarah</h3>
                    <p class="content-description">
                        Akses materi pembelajaran yang dirancang sesuai kebutuhan kompetensi mitra sebelum turun ke lapangan.
                    </p>
                </div>
                
                <div class="content-card">
                    <div class="content-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="content-title">Evaluasi Terstandar</h3>
                    <p class="content-description">
                        Selesaikan evaluasi terstruktur sebagai prasyarat resmi mengikuti pelatihan dan penugasan berikutnya.
                    </p>
                </div>
                
                <div class="content-card">
                    <div class="content-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="content-title">Progress Terpantau</h3>
                    <p class="content-description">
                        Setiap mitra dapat memantau perkembangan belajar secara mandiri dari kursus yang diikuti melalui menu Kursus Saya.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Alamat BPS + BerAKHLAK -->
                <div class="footer-section">
                    <div class="bps-title">BADAN PUSAT STATISTIK</div>
                    <div class="footer-address">
                        <p>Badan Pusat Statistik Kabupaten Tanah Laut (BPS-Statistics of Tanah Laut Regency)</p>
                        <p>Alamat: Jalan A. Syairani No. 9 Pelaihari Kab. Tanah Laut</p>
                        <p>Prov. Kalimantan Selatan</p>
                        <p>76914</p>
                        <p>Indonesia</p>
                    </div>
                    
                    <!-- Gambar BerAKHLAK -->
                    <div class="berakhlak-container">
                        <img src="{{ asset('img/cover.jpg') }}" alt="BerAKHLAK" class="berakhlak-image">
                        <ul class="berakhlak-links">
                    </div>
                </div>

                <!-- Kontak Kami -->
                <div class="footer-section">
                    <h3>Kontak Kami</h3>
                    <div class="contact-info">
                        <p>Telepon: +62 512 21092</p>
                        <p>Fax: +62 512 3113</p>
                        <p>Email: bps6301@bps.go.id</p>
                        <p>bps6301@gmail.com</p>
                    </div>
                </div>

                <!-- Tentang Kami -->
                <div class="footer-section">
                    <h3>Tentang Kami</h3>
                    <ul class="footer-links">
                        <li><a href="https://ppid.bps.go.id/app/konten/6301/Profil-BPS.html?_gl=1*15t609r*_ga*MjQxOTY0MDAzLjE3NjEyNzM4MzU.*_ga_XXTTVXWHDB*czE3NjEyNzM4MzQkbzEkZzAkdTE3NjEyNzM4MzQkajYwJGwwJGgw">Profil BPS</a></li>
                        <li><a href="https://ppid.bps.go.id/?mfd=6301&_gl=1*1yli45g*_ga*MTQyMzAzNDgwMC4xNzQwMjk0NzU4*_ga_XXTTVXWHDB*czE3NjQyMDY0ODIkbzEwJGcwJHQxNzY0MjA2NDkyJGo1MCRsMCRoMA..">PPID</a></li>
                        <li><a href="https://ppid.bps.go.id/app/konten/0000/Layanan-BPS.html?_gl=1*8lxw4a*_ga*MTQyMzAzNDgwMC4xNzQwMjk0NzU4*_ga_XXTTVXWHDB*czE3NjQyMDY0ODIkbzEwJGcwJHQxNzY0MjA2NDkyJGo1MCRsMCRoMA..#pills-3">Kebijakan Diseminasi</a></li>
                    </ul>
                </div>

            </div>

            <div class="footer-bottom">
                <div class="copyright">
                    Hak Cipta © 2025 Badan Pusat Statistik
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navMenu = document.querySelector('.nav-menu');
            
            if (mobileMenuBtn && navMenu) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('show');
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        navMenu.classList.remove('show');
                    }
                });
            }
            
            // Slider functionality
            const slider = document.querySelector('.slider');
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.slider-dot');
            const prevBtn = document.querySelector('.slider-arrow.prev');
            const nextBtn = document.querySelector('.slider-arrow.next');
            
            let currentSlide = 0;
            const slideCount = slides.length;
            
            // Function to update slider position
            function updateSlider() {
                slider.style.transform = `translateX(-${currentSlide * 100}%)`;
                
                // Update active dot
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentSlide);
                });
            }
            
            // Next slide
            function nextSlide() {
                currentSlide = (currentSlide + 1) % slideCount;
                updateSlider();
            }
            
            // Previous slide
            function prevSlide() {
                currentSlide = (currentSlide - 1 + slideCount) % slideCount;
                updateSlider();
            }
            
            // Event listeners
            if (nextBtn) nextBtn.addEventListener('click', nextSlide);
            if (prevBtn) prevBtn.addEventListener('click', prevSlide);
            
            // Dot navigation
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentSlide = index;
                    updateSlider();
                });
            });
            
            // Auto slide (optional)
            // setInterval(nextSlide, 5000);

            // Handle image loading errors
            document.querySelectorAll('.avatar-image').forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const initials = this.nextElementSibling;
                    if (initials && initials.classList.contains('avatar-initials')) {
                        initials.style.display = 'flex';
                    }
                });
            });
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>