<?php
/**
 * DicomRouter Webhook untuk SatuSehat
 * Menerima notifikasi dari dicomrouter dan mengirim ke Telegram
 */

// Konfigurasi Telegram

//8678020787:AAFVohgkoSUii5FhXmnULa8nrYQFMx_C4Zo
// ID : 169199600
define('TELEGRAM_BOT_TOKEN', '8678020787:AAF0e8op8SirCgCViIwRTcF1nWlqOdinryQ');
define('TELEGRAM_CHAT_ID', '169199600');

// ================= FUNCTION =================

// Kirim ke Telegram
function sendTelegram($message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";

    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ]
    ];

    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

// Format pesan
function formatMessage($payload) {

    $status = $payload['status'] ?? false;
    $stage  = $payload['stage'] ?? '-';
    $data   = $payload['data'] ?? [];

    $msg  = "<b>🏥 NOTIF DICOM ROUTER</b>\n\n";

    $msg .= ($status ? "✅ SUCCESS\n" : "❌ FAILED\n");
    $msg .= "Stage: <b>{$stage}</b>\n\n";

    // Data DICOM
    if (!empty($data)) {
        $msg .= "<b>📋 DATA DICOM</b>\n";

        if (isset($data['accessionNumber'])) {
            $msg .= "Accession: <code>{$data['accessionNumber']}</code>\n";
        }

        if (isset($data['studyInstanceUID'])) {
            $msg .= "Study UID:\n<code>{$data['studyInstanceUID']}</code>\n";
        }

        if (isset($data['imagingStudyId'])) {
            $msg .= "Imaging ID: <code>{$data['imagingStudyId']}</code>\n";
        }

        $msg .= "\n";
    }

    // Pesan tambahan
    if (!empty($payload['message'])) {
        $msg .= "📝 " . $payload['message'] . "\n";
    }

    // Error
    if (!empty($payload['error'])) {
        $msg .= "⚠️ " . json_encode($payload['error']) . "\n";
    }

    return $msg;
}

// ================= MAIN =================

// hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// ambil payload
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

// validasi
if (!$payload) {
    http_response_code(400);
    echo "Invalid JSON";
    exit;
}

// format & kirim
$message = formatMessage($payload);
$response = sendTelegram($message);

// response ke dicomrouter
echo json_encode([
    'success' => true,
    'telegram_response' => $response
]);
?>
