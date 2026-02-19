<?php
// Konfigurasi Kirimi API
define('KIRIMI_USER_CODE', 'KM2I98226');
define('KIRIMI_SECRET_KEY', 'e8fa0cefcef55c08c5f148a484e13ee28e3bec076bef6de0941407648c2196d3');
define('KIRIMI_API_URL', 'https://api.kirimi.id/v1/');

// TODO: Isi dengan Device ID dari dashboard Kirimi Anda
// Cara mendapatkan Device ID:
// 1. Login ke dashboard Kirimi
// 2. Pilih device WhatsApp yang sudah terhubung
// 3. Copy Device ID nya
define('KIRIMI_DEVICE_ID', 'D-FYOYL');

// Fungsi untuk kirim pesan WhatsApp via Kirimi
function sendKirimiMessage($receiver, $message, $options = []) {
    // Validasi Device ID
    if (empty(KIRIMI_DEVICE_ID)) {
        error_log('Kirimi Error: Device ID belum diisi di kirimi_config.php');
        return [
            'status' => 0,
            'data' => null,
            'error' => 'Device ID belum dikonfigurasi'
        ];
    }
    
    $url = KIRIMI_API_URL . 'send-message';
    
    $payload = [
        'user_code' => KIRIMI_USER_CODE,
        'device_id' => KIRIMI_DEVICE_ID,
        'receiver' => $receiver,
        'message' => $message,
        'secret' => KIRIMI_SECRET_KEY,
        'enableTypingEffect' => isset($options['enableTypingEffect']) ? $options['enableTypingEffect'] : true,
        'typingSpeedMs' => isset($options['typingSpeedMs']) ? $options['typingSpeedMs'] : 350
    ];
    
    // Optional parameters
    if (isset($options['media_url'])) {
        $payload['media_url'] = $options['media_url'];
    }
    if (isset($options['fileName'])) {
        $payload['fileName'] = $options['fileName'];
    }
    if (isset($options['quotedMessageId'])) {
        $payload['quotedMessageId'] = $options['quotedMessageId'];
    }
    
    $headers = [
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout 10 detik
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Log untuk debugging
    if ($httpCode !== 200) {
        error_log("Kirimi API Error - Status: $httpCode, Response: $response, Error: $error");
    }
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true),
        'error' => $error
    ];
}
?>
