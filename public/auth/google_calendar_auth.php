<?php
session_start();
require_once __DIR__ . '/../../app/config/config.php'; // Defines BASE_URL
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/GoogleClientService.php';

// Check Login
if (!isset($_SESSION['user'])) {
    die("Silakan login terlebih dahulu.");
}

$action = $_GET['action'] ?? 'connect';
$clientService = new GoogleClientService();
$client = $clientService->getClient();

// UPDATE REDIRECT URI DYNAMICALLY FOR THIS ACTION
// Because .env might have the main login callback. We need specific callback.
// Or we can simple use the same controller logic.
// Let's assume user puts THIS file as redirect URI in Google Console: BASE_URL . '/auth/google_calendar_auth.php?action=callback'
$redirectUri = BASE_URL . '/auth/google_calendar_auth.php?action=callback';
$client->setRedirectUri($redirectUri);

if ($action === 'connect') {
    // 1. Redirect to Google
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit;
} 
elseif ($action === 'callback') {
    // 2. Handle Callback
    if (isset($_GET['code'])) {
        try {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            
            if (!isset($token['error'])) {
                // Save to DB (gcal_ columns)
                $db = new Database();
                $pdo = $db->connect();
                
                $accessToken = $token['access_token'];
                $refreshToken = $token['refresh_token'] ?? null; // Null if re-auth without prompt
                $expiresIn = $token['expires_in'];
                $created = $token['created']; // timestamp
                
                $userId = $_SESSION['user']['id'];
                
                // If Refresh Token is null, we might want to keep the old one IF it exists? 
                // Google doesn't send refresh token on every login unless prompt=consent.
                // My ClientService sets prompt=consent, so we SHOULD get refresh token.
                
                $sql = "UPDATE users SET 
                        gcal_access_token = :at, 
                        gcal_token_expires = :exp";
                
                $params = [
                    ':at' => $accessToken,
                    ':exp' => $created + $expiresIn,
                    ':id' => $userId
                ];

                if ($refreshToken) {
                    $sql .= ", gcal_refresh_token = :rt";
                    $params[':rt'] = $refreshToken;
                }

                $sql .= " WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // Update Session flag
                $_SESSION['user']['gcal_connected'] = true;

                // Redirect back to dashboard
                $role = $_SESSION['user']['role']; // dosen or mahasiswa
                $dashUrl = ($role === 'dosen') ? '/views/dosen/dashboard.php' : '/views/mahasiswa/dashboard_mahasiswa.php';
                
                header("Location: " . BASE_URL . $dashUrl . "?msg=" . urlencode("Google Calendar Berhasil Terhubung! 📅"));
                exit;

            } else {
                throw new Exception("Google Token Error: " . json_encode($token));
            }

        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    } else {
        // Cancelled or Error
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}
