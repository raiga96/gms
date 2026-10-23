<?php
/**
 * Skrip untuk menghantar Push Notification melalui FCM HTTP v1
 */

class FirebaseNotification {
    private $project_id = 'smartcut-fe996'; // Gantikan dengan ID Projek anda
    private $service_account_file = 'google-services.json'; // Path ke fail JSON Firebase

    // Fungsi untuk mendapatkan Access Token daripada Google
    private function getAccessToken() {
        $json = json_decode(file_get_contents($this->service_account_file), true);
        $now = time();
        
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        openssl_sign("$header.$payload", $signature, $json['private_key'], 'sha256');
        $jwt = "$header.$payload." . base64_encode($signature);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        
        $response = json_decode(curl_exec($ch), true);
        return $response['access_token'];
    }

    public function send($deviceToken, $title, $body, $data = []) {
        $accessToken = $this->getAccessToken();
        $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";

        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $data, // Contoh: ['type' => 'ready', 'si_id' => '123']
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => ['sound' => 'default']
                    ]
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }
}

// --- CARA PENGGUNAAN ---
/*
$fcm = new FirebaseNotification();
$response = $fcm->send(
    'TOKEN_PERANTI_DARI_DB', 
    'Prescription Ready', 
    'Your prescription is now available for download.',
    ['type' => 'ready']
);
echo $response;
*/
?>
