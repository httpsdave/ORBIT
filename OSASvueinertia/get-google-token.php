<?php
/**
 * Google Drive Refresh Token Generator
 * Run this once to get your refresh token for backups
 */

require __DIR__ . '/vendor/autoload.php';

// Replace these with your credentials from Google Cloud Console
$clientId = '331466588840-9nbockn6m32f2vf0j78eqciqoeamm914.apps.googleusercontent.com';
$clientSecret = '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';

$client = new \Google\Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri('http://localhost:9999');
$client->addScope(\Google\Service\Drive::DRIVE_FILE);
$client->setAccessType('offline');
$client->setPrompt('consent');
$client->setApprovalPrompt('force');

// Check if URL was passed as command line argument
$code = null;
if (isset($argv[1])) {
    // Parse the URL from command line argument
    $urlParts = parse_url($argv[1]);
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $queryParams);
        $code = $queryParams['code'] ?? null;
    }
} elseif (isset($_GET['code'])) {
    $code = $_GET['code'];
}

if (!$code) {
    // Step 1: Get authorization URL
    $authUrl = $client->createAuthUrl();
    echo "\n";
    echo "========================================\n";
    echo "STEP 1: AUTHORIZE THE APPLICATION\n";
    echo "========================================\n\n";
    echo "1. Copy this URL and open it in your browser:\n\n";
    echo $authUrl . "\n\n";
    echo "2. Sign in with: lspuorbit@gmail.com\n";
    echo "3. Click 'Continue' and 'Allow'\n";
    echo "4. You'll be redirected to localhost (page won't load - that's OK!)\n";
    echo "5. Copy the ENTIRE URL from your browser's address bar\n";
    echo "6. The URL will look like: http://localhost/?code=4/0A...&scope=...\n\n";
    echo "========================================\n";
    echo "STEP 2: GET YOUR REFRESH TOKEN\n";
    echo "========================================\n\n";
    echo "Run this command:\n";
    echo 'php get-google-token.php "PASTE_THE_ENTIRE_URL_HERE"' . "\n\n";
} else {
    try {
        // Step 2: Exchange code for tokens
        echo "\nExchanging authorization code for tokens...\n";
        $token = $client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['error'])) {
            echo "\n❌ ERROR: " . $token['error_description'] . "\n";
            exit(1);
        }
        
        if (!isset($token['refresh_token'])) {
            echo "\n❌ ERROR: No refresh token received.\n";
            echo "This might happen if you've already authorized this app before.\n";
            echo "To fix:\n";
            echo "1. Go to: https://myaccount.google.com/permissions\n";
            echo "2. Remove 'LSPU Orbit Backup'\n";
            echo "3. Run this script again from Step 1\n\n";
            exit(1);
        }
        
        echo "\n✓ SUCCESS! Here are your credentials:\n\n";
        echo "========================================\n";
        echo "ADD THESE TO YOUR .env FILE:\n";
        echo "========================================\n\n";
        echo "GOOGLE_DRIVE_CLIENT_ID=\"{$clientId}\"\n";
        echo "GOOGLE_DRIVE_CLIENT_SECRET=\"{$clientSecret}\"\n";
        echo "GOOGLE_DRIVE_REFRESH_TOKEN=\"{$token['refresh_token']}\"\n";
        echo "GOOGLE_DRIVE_FOLDER=\"Backups/LSPU-Orbit\"\n\n";
        echo "========================================\n";
        echo "NEXT STEPS:\n";
        echo "========================================\n\n";
        echo "1. Copy the lines above to your .env file\n";
        echo "2. Also add them to Railway environment variables\n";
        echo "3. Create folder 'Backups/LSPU-Orbit' in Google Drive\n";
        echo "4. Test backup: php artisan backup:run\n\n";
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}
