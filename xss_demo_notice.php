<?php
// Local classroom phishing simulation reached through the Stored XSS test link.
// This page intentionally does NOT authenticate users, read field values,
// save credentials, or send information anywhere.
require_once 'config.php';
require_once 'functions.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Web Development Business System</title>
    <link rel="stylesheet" href="style.css">
    <script src="xss_demo.js" defer></script>
</head>
<body>
<header class="topbar">
    <div class="brand">Web Development Business System</div>
</header>
<main class="container">
    <section class="auth-card narrow" aria-labelledby="signin-title">
        <h1 id="signin-title">Your Session Has Expired</h1>
        <p class="muted">Please sign in again to continue using the Web Development Business System.</p>

        <div class="message error" role="status">
            For your security, your session needs to be verified before you continue.
        </div>

        <!-- These are visual-only fields. They have no name, form action, or submit behavior. -->
        <div class="form-grid one-column">
            <label for="demo-email">Email Address
                <input id="demo-email" type="email" autocomplete="off" placeholder="Enter your email address">
            </label>
            <label for="demo-password">Password
                <input id="demo-password" type="password" autocomplete="new-password" placeholder="Enter your password">
            </label>
            <button id="demo-signin" type="button">Sign In</button>
        </div>
        <p class="muted demo-small-note">Web Development Business System · Account Verification</p>
        <div id="training-result" class="message success" role="alert" hidden>
            <strong>Security training notice:</strong> This was a simulated phishing page opened through an injected link. No field values were read, collected, stored, or transmitted. A real malicious page could try to steal credentials. Close this page and report the suspicious link.
            <p><a href="programmers.php">Return to the Programmers page</a></p>
        </div>
    </section>
</main>
<footer class="footer">Web Development Business System</footer>
</body>
</html>
