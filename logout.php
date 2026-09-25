<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logging Out · EduSphere</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ===== RESET & BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* ===== ANIMATED BACKGROUND ===== */
body::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 50%;
    top: -150px;
    right: -150px;
    animation: floatBubble 25s infinite ease-in-out;
}

body::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 50%;
    bottom: -100px;
    left: -100px;
    animation: floatBubble 30s infinite ease-in-out reverse;
}

@keyframes floatBubble {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(60px, -40px) scale(1.1); }
}

/* ===== FLOATING PARTICLES ===== */
.particles {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
}

.particle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: particleFloat 20s infinite linear;
}

.particle:nth-child(1) { left: 5%; animation-duration: 22s; animation-delay: 0s; width: 10px; height: 10px; }
.particle:nth-child(2) { left: 15%; animation-duration: 18s; animation-delay: 2s; }
.particle:nth-child(3) { left: 25%; animation-duration: 25s; animation-delay: 4s; width: 12px; height: 12px; }
.particle:nth-child(4) { left: 40%; animation-duration: 20s; animation-delay: 1s; }
.particle:nth-child(5) { left: 55%; animation-duration: 23s; animation-delay: 3s; width: 9px; height: 9px; }
.particle:nth-child(6) { left: 70%; animation-duration: 19s; animation-delay: 5s; }
.particle:nth-child(7) { left: 80%; animation-duration: 24s; animation-delay: 2s; width: 11px; height: 11px; }
.particle:nth-child(8) { left: 90%; animation-duration: 21s; animation-delay: 4s; }

@keyframes particleFloat {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-10vh) rotate(720deg); opacity: 0; }
}

/* ===== LOGOUT CARD ===== */
.logout-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 28px;
    padding: 50px 45px 40px;
    max-width: 460px;
    width: 100%;
    box-shadow: 
        0 30px 80px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    position: relative;
    z-index: 2;
    text-align: center;
    animation: slideUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9) rotate(-2deg);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1) rotate(0);
    }
}

/* ===== LOGO ===== */
.logout-logo {
    font-size: 34px;
    font-weight: 800;
    color: #2d3436;
    letter-spacing: -1px;
    margin-bottom: 4px;
}

.logout-logo span {
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.logout-sub {
    color: #636e72;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 30px;
}

/* ===== ICON ===== */
.logout-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    position: relative;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.logout-icon i {
    font-size: 48px;
    color: #667eea;
    animation: spin 8s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===== SUCCESS MESSAGE ===== */
.logout-message {
    margin: 20px 0 25px;
}

.logout-message h2 {
    font-size: 24px;
    font-weight: 700;
    color: #2d3436;
    margin-bottom: 8px;
}

.logout-message p {
    color: #636e72;
    font-size: 15px;
    line-height: 1.6;
}

.logout-message .highlight {
    color: #667eea;
    font-weight: 600;
}

/* ===== PROGRESS BAR ===== */
.progress-container {
    margin: 30px 0 20px;
    background: #f0f2f5;
    border-radius: 50px;
    height: 6px;
    overflow: hidden;
    position: relative;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 50px;
    width: 0%;
    animation: progress 3s ease-in-out forwards;
}

@keyframes progress {
    0% { width: 0%; }
    20% { width: 25%; }
    40% { width: 50%; }
    60% { width: 75%; }
    80% { width: 90%; }
    100% { width: 100%; }
}

.progress-text {
    font-size: 13px;
    color: #b2bec3;
    margin-top: 8px;
}

/* ===== BUTTONS ===== */
.logout-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 14px 30px;
    border: none;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    font-family: inherit;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-secondary {
    background: #f0f2f5;
    color: #2d3436;
}

.btn-secondary:hover {
    background: #e4e6eb;
    transform: translateY(-2px);
}

.btn i {
    font-size: 16px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
    .logout-card {
        padding: 35px 25px 30px;
        border-radius: 20px;
    }
    
    .logout-icon {
        width: 80px;
        height: 80px;
    }
    
    .logout-icon i {
        font-size: 38px;
    }
    
    .logout-logo {
        font-size: 28px;
    }
    
    .logout-message h2 {
        font-size: 20px;
    }
    
    .logout-actions {
        flex-direction: column;
    }
    
    .btn {
        padding: 12px 24px;
        justify-content: center;
    }
}

@media (max-width: 380px) {
    .logout-card {
        padding: 25px 18px 20px;
    }
}

/* ===== DARK MODE ===== */
@media (prefers-color-scheme: dark) {
    body {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    }
    
    .logout-card {
        background: rgba(26, 26, 46, 0.95);
        backdrop-filter: blur(20px);
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    }
    
    .logout-logo {
        color: #ffffff;
    }
    
    .logout-sub {
        color: #a8a8b3;
    }
    
    .logout-message h2 {
        color: #ffffff;
    }
    
    .logout-message p {
        color: #a8a8b3;
    }
    
    .progress-container {
        background: #2a2a4a;
    }
    
    .progress-text {
        color: #6c6c8a;
    }
    
    .btn-secondary {
        background: #2a2a4a;
        color: #e0e0e0;
    }
    
    .btn-secondary:hover {
        background: #3a3a5a;
    }
    
    .logout-icon {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
    }
}

/* ===== CONFETTI ANIMATION ===== */
.confetti-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -10px;
    animation: confettiFall linear forwards;
}

.confetti:nth-child(1) { left: 10%; animation-duration: 3s; animation-delay: 0.5s; background: #667eea; transform: rotate(45deg); }
.confetti:nth-child(2) { left: 20%; animation-duration: 4s; animation-delay: 1s; background: #764ba2; border-radius: 50%; }
.confetti:nth-child(3) { left: 30%; animation-duration: 3.5s; animation-delay: 1.5s; background: #10b981; transform: rotate(60deg); }
.confetti:nth-child(4) { left: 40%; animation-duration: 4.5s; animation-delay: 0.8s; background: #f59e0b; border-radius: 50%; }
.confetti:nth-child(5) { left: 50%; animation-duration: 3.8s; animation-delay: 1.2s; background: #ef4444; transform: rotate(30deg); }
.confetti:nth-child(6) { left: 60%; animation-duration: 4.2s; animation-delay: 0.3s; background: #8b5cf6; border-radius: 50%; }
.confetti:nth-child(7) { left: 70%; animation-duration: 3.2s; animation-delay: 1.8s; background: #ec4899; transform: rotate(90deg); }
.confetti:nth-child(8) { left: 80%; animation-duration: 4.8s; animation-delay: 0.6s; background: #3b82f6; border-radius: 50%; }
.confetti:nth-child(9) { left: 90%; animation-duration: 3.6s; animation-delay: 1.4s; background: #10b981; transform: rotate(45deg); }
.confetti:nth-child(10) { left: 95%; animation-duration: 4s; animation-delay: 0.2s; background: #f59e0b; border-radius: 50%; }

@keyframes confettiFall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(110vh) rotate(720deg);
        opacity: 0;
    }
}

/* ===== ACCESSIBILITY ===== */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
</head>
<body>

<!-- Confetti Animation -->
<div class="confetti-container">
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
</div>

<!-- Floating Particles -->
<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<!-- Logout Card -->
<div class="logout-card">
    <div class="logout-logo">Edu<span>Sphere</span></div>
    <div class="logout-sub">
        <i class="fas fa-shield-alt" style="color: #667eea;"></i>
        Secure Logout
    </div>

    <div class="logout-icon">
        <i class="fas fa-sign-out-alt"></i>
    </div>

    <div class="logout-message">
        <h2>You're Signed Out!</h2>
        <p>
            You have been successfully logged out of <span class="highlight">EduSphere</span>.
            <br>
            Your session has been securely terminated.
        </p>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    <div class="progress-text">
        <i class="fas fa-spinner fa-spin"></i>
        Redirecting to login page...
    </div>

    <!-- Action Buttons -->
    <div class="logout-actions">
        <a href="login.php" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Sign In Again
        </a>
        <a href="#" class="btn btn-secondary" onclick="goBack()">
            <i class="fas fa-arrow-left"></i>
            Go Back
        </a>
    </div>

    <div style="margin-top: 20px; font-size: 12px; color: #b2bec3;">
        <i class="fas fa-lock"></i>
        Your session has been securely closed
    </div>
</div>

<script>
// ===== AUTO REDIRECT =====
let countdown = 5;
const progressText = document.querySelector('.progress-text');

// Update progress text with countdown
function updateCountdown() {
    if (countdown > 0) {
        progressText.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            Redirecting in ${countdown} second${countdown > 1 ? 's' : ''}...
        `;
        countdown--;
        setTimeout(updateCountdown, 1000);
    } else {
        // Redirect to login page after countdown
        window.location.href = 'login.php';
    }
}

// Start countdown after 1 second delay
setTimeout(updateCountdown, 1500);

// ===== GO BACK FUNCTION =====
function goBack() {
    // Check if there's a previous page in history
    if (document.referrer && document.referrer.includes(window.location.hostname)) {
        window.history.back();
    } else {
        window.location.href = 'login.php';
    }
}

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', function(e) {
    // Press 'Enter' or 'Space' to go to login
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.location.href = 'login.php';
    }
    
    // Press 'Escape' to go back
    if (e.key === 'Escape') {
        e.preventDefault();
        goBack();
    }
});

// ===== PREVENT BACK BUTTON (optional) =====
// This prevents the user from going back to a cached page after logout
window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
        window.location.reload();
    }
});

// ===== REMOVE CONFETTI AFTER ANIMATION =====
setTimeout(() => {
    document.querySelector('.confetti-container').style.opacity = '0';
    setTimeout(() => {
        document.querySelector('.confetti-container').style.display = 'none';
    }, 1000);
}, 5000);

// ===== TRACK LOGOUT EVENT (Optional Analytics) =====
console.log('User logged out successfully at', new Date().toLocaleString());

// ===== DARK MODE DETECTION =====
if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    // Dark mode specific adjustments if needed
    document.querySelector('.logout-card').style.border = '1px solid rgba(255,255,255,0.05)';
}
</script>
</body>
</html>