<?php
/**
 * DicomRouter Webhook untuk SatuSehat
 * Menerima notifikasi dari dicomrouter dan mengirim ke Telegram
 */

// Konfigurasi Telegram
define('TELEGRAM_BOT_TOKEN', '8678020787:AAF0e8op8SirCgCViIwRTcF1nWlqOdinryQ');
define('TELEGRAM_CHAT_ID', '169199600');

// Konfigurasi Database
define('DB_HOST', 'HOSTNAME DATABASE');
define('DB_USER', 'USERNAME DATABASE');
define('DB_PASS', 'PASSWORD DATABASE');
define('DB_NAME', 'NAMA DATABASE');
define('DB_PORT', 3306);

// Konfigurasi Logging
define('LOG_FILE', __DIR__ . '/webhook.log');

// Set header sebagai JSON
header('Content-Type: application/json');

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_FILE);

/**
 * Function untuk logging
 */
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}

/**
 * Function untuk query data pasien dari database
 */
function queryPatientData($accessionNumber) {
    try {
        logMessage("Querying patient data untuk accession: {$accessionNumber}", 'DEBUG');
        
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset("utf8mb4");
        
        // Prepare query
        $query = "SELECT 
                    a.subject->>'$.display' AS PASIEN,
                    a.subject->>'$.reference' AS ID_PASIEN,
                    a.code->>'$.text' AS TINDAKAN
                  FROM `" . DB_NAME . "`.service_request a 
                  WHERE a.refId = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        // Bind parameter
        $stmt->bind_param("s", $accessionNumber);
        
        // Execute
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Get result
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        if ($data) {
            logMessage("Patient data found: " . json_encode($data), 'DEBUG');
            return $data;
        } else {
            logMessage("No patient data found untuk accession: {$accessionNumber}", 'WARNING');
            return null;
        }
        
    } catch (Exception $e) {
        logMessage("Database error: " . $e->getMessage(), 'ERROR');
        return null;
    }
}

/**
 * Function untuk mengirim pesan ke Telegram
 */
function sendTelegramNotification($message, $parseMode = 'HTML') {
    $telegramApiUrl = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    
    $payload = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => $parseMode,
        'disable_web_page_preview' => true
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($payload),
            'timeout' => 10
        ]
    ];
    
    try {
        $context = stream_context_create($options);
        $response = file_get_contents($telegramApiUrl, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to send Telegram message');
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['ok']) || !$result['ok']) {
            throw new Exception('Telegram API error: ' . ($result['description'] ?? 'Unknown error'));
        }
        
        logMessage('Notifikasi Telegram berhasil dikirim');
        return true;
    } catch (Exception $e) {
        logMessage('Error mengirim ke Telegram: ' . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Function untuk format notifikasi
 */
function formatNotification($payload, $patientData = null) {
    $message = "<b>🏥 NOTIFIKASI DICOM ROUTER</b>\n";
    $message .= "<b>RSUD LAWANG MALANG</b>";
    $message .= "\n";
    
    // Extract data dari payload DicomRouter
    $dicomData = $payload['data'] ?? [];
    $status = $payload['status'] ?? false;
    $stage = $payload['stage'] ?? 'unknown';
    
    // Status indicator
    $statusEmoji = $status ? '✅' : '❌';
    $message .= "<b>{$statusEmoji} Status:</b> " . ($status ? 'SUCCESS' : 'FAILED') . "\n";
    $message .= "<b>📊 Stage:</b> " . htmlspecialchars($stage) . "\n\n";
    
    // Patient Information Section (dari database)
    if ($patientData) {
        $message .= "<b>👤 INFORMASI PASIEN</b>\n";
        $message .= "\n";
        
        if (isset($patientData['PASIEN'])) {
            $message .= "  Nama: " . htmlspecialchars($patientData['PASIEN'] ?? '-') . "\n";
        }
        if (isset($patientData['ID_PASIEN'])) {
            $message .= "  ID: <code>" . htmlspecialchars($patientData['ID_PASIEN'] ?? '-') . "</code>\n";
        }
        $message .= "\n";
    }
    
    // Procedure Information (dari database)
    if ($patientData && isset($patientData['TINDAKAN'])) {
        $message .= "<b>🏨 TINDAKAN MEDIS</b>\n";
        $message .= "\n";
        $message .= "  Tindakan: " . htmlspecialchars($patientData['TINDAKAN']) . "\n\n";
    }
    
    // DICOM Study Information
    $message .= "<b>📋 INFORMASI STUDI DICOM</b>\n";
    $message .= "\n";
    
    if (isset($dicomData['accessionNumber'])) {
        $message .= "  Accession #: <code>" . htmlspecialchars($dicomData['accessionNumber']) . "</code>\n";
    }
    if (isset($dicomData['studyInstanceUID'])) {
        $message .= "  Study UID: <code>" . htmlspecialchars($dicomData['studyInstanceUID']) . "</code>\n";
    }
    if (isset($dicomData['imagingStudyId'])) {
        $message .= "  Imaging Study ID: <code>" . htmlspecialchars($dicomData['imagingStudyId']) . "</code>\n";
    }
    $message .= "\n";
    
    // Message
    if (isset($payload['message'])) {
        $message .= "<b>📝 Pesan:</b> " . htmlspecialchars($payload['message']) . "\n";
    }
    
    // Error (jika ada)
    if (!empty($payload['error'])) {
        $message .= "<b>⚠️ Error:</b> " . htmlspecialchars(json_encode($payload['error'])) . "\n";
    }
    
    $message .= "\n";
    
    return $message;
}

/**
 * Main Handler
 */
function handleWebhook() {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        logMessage('Request method tidak valid: ' . $_SERVER['REQUEST_METHOD'], 'WARNING');
        return;
    }
    
    // Get raw input
    $input = file_get_contents('php://input');
    $inputLength = strlen($input);
    logMessage("Raw input received ({$inputLength} bytes)", 'DEBUG');
    logMessage('Payload: ' . $input, 'DEBUG');
    
    // Parse JSON
    $payload = json_decode($input, true);
    
    if ($payload === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON', 'details' => json_last_error_msg()]);
        logMessage('JSON parsing error: ' . json_last_error_msg(), 'ERROR');
        return;
    }
    
    // Verify payload not empty
    if (empty($payload)) {
        http_response_code(400);
        echo json_encode(['error' => 'Empty payload']);
        logMessage('Payload kosong', 'WARNING');
        return;
    }
    
    logMessage('Payload valid, structure: ' . json_encode([
        'status' => $payload['status'] ?? null,
        'stage' => $payload['stage'] ?? null,
        'has_data' => isset($payload['data']),
        'has_error' => isset($payload['error'])
    ]), 'DEBUG');
    
    // Extract accessionNumber dari payload
    $accessionNumber = $payload['data']['accessionNumber'] ?? null;
    $patientData = null;
    
    if ($accessionNumber) {
        logMessage("Accession number found: {$accessionNumber}", 'INFO');
        
        // Query database untuk dapatkan patient data
        $patientData = queryPatientData($accessionNumber);
        
        if ($patientData) {
            logMessage('Patient data retrieved successfully', 'INFO');
        } else {
            logMessage('Patient data not found di database', 'WARNING');
        }
    } else {
        logMessage('Accession number tidak ditemukan dalam payload', 'WARNING');
    }
    
    // Format notifikasi dengan data dari payload dan database
    $notification = formatNotification($payload, $patientData);
    
    // Kirim ke Telegram
    $success = sendTelegramNotification($notification);
    
    // Response
    if ($success) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Webhook processed successfully',
            'accession_number' => $accessionNumber,
            'patient_data_found' => ($patientData !== null),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        logMessage('Webhook processed successfully - Accession: ' . ($accessionNumber ?? 'N/A'));
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send notification'
        ]);
        logMessage('Failed to send Telegram notification', 'ERROR');
    }
}

/**
 * Health Check Endpoint
 */
function handleHealthCheck() {
    if ($_GET['check'] === 'health') {
        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'telegram_configured' => !empty(TELEGRAM_BOT_TOKEN) && !empty(TELEGRAM_CHAT_ID)
        ]);
        return;
    }
    return false;
}

// Router
if (isset($_GET['check'])) {
    handleHealthCheck();
} else {
    handleWebhook();
}
?>
