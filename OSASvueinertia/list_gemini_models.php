<?php

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GOOGLE_GEMINI_API_KEY'] ?? '';

if (empty($apiKey)) {
    die("Error: GOOGLE_GEMINI_API_KEY not found in .env file\n");
}

echo "Fetching available Google Gemini models...\n";
echo "API Key: " . substr($apiKey, 0, 10) . "...\n\n";

// List models endpoint
$endpoint = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: {$httpCode}\n";
echo str_repeat("=", 80) . "\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    if (isset($data['models']) && is_array($data['models'])) {
        echo "Available Models:\n\n";
        
        foreach ($data['models'] as $model) {
            $name = $model['name'] ?? 'Unknown';
            $displayName = $model['displayName'] ?? 'N/A';
            $description = $model['description'] ?? 'N/A';
            $supportedMethods = $model['supportedGenerationMethods'] ?? [];
            
            echo "Model Name: {$name}\n";
            echo "Display Name: {$displayName}\n";
            echo "Description: {$description}\n";
            echo "Supported Methods: " . implode(', ', $supportedMethods) . "\n";
            echo str_repeat("-", 80) . "\n\n";
        }
        
        // Find models that support generateContent
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "RECOMMENDED MODELS FOR generateContent:\n";
        echo str_repeat("=", 80) . "\n\n";
        
        foreach ($data['models'] as $model) {
            $supportedMethods = $model['supportedGenerationMethods'] ?? [];
            if (in_array('generateContent', $supportedMethods)) {
                $name = str_replace('models/', '', $model['name']);
                echo "✓ {$name}\n";
            }
        }
        
    } else {
        echo "No models found in response.\n";
        echo "Full Response:\n";
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
    
} else {
    echo "✗ ERROR! Failed to fetch models.\n\n";
    echo "Response:\n";
    echo json_encode(json_decode($response), JSON_PRETTY_PRINT);
    echo "\n\n";
    echo "Possible issues:\n";
    echo "1. The 'Generative Language API' is not enabled in Google Cloud Console\n";
    echo "2. Your API key doesn't have permission to access this API\n";
    echo "3. Billing is not enabled for your Google Cloud project\n";
    echo "4. API key is invalid or expired\n";
}
