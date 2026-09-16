<?php
header('Content-Type: application/json; charset=utf-8');

function reply_json(bool $ok, string $message, array $extra = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(array_merge(['ok' => $ok, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply_json(false, 'POST requests only.', [], 405);
}

$configFile = __DIR__ . '/momo_config.php';
if (!is_file($configFile)) {
    reply_json(false, 'Momo WhatsApp is not configured on the server yet.', [], 503);
}

$momo = require $configFile;
$apiKey = trim((string)($momo['api_key'] ?? ''));
$notifyNumber = preg_replace('/[^0-9]/', '', (string)($momo['notify_number'] ?? ''));

if ($apiKey === '') {
    reply_json(false, 'Momo API key is missing from the server configuration.', [], 503);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) {
    $data = $_POST;
}

$type = trim((string)($data['type'] ?? 'booking'));
$name = trim((string)($data['name'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));

if ($name === '' || $phone === '') {
    reply_json(false, 'Name and phone number are required.', [], 422);
}

$reference = 'PRM-' . date('ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

if ($type === 'transfer') {
    $message = "🏨 NEW GUEST TRANSFER REQUEST\n\nReference: {$reference}\nName: {$name}\nPhone: {$phone}\nPickup: " . trim((string)($data['pickup'] ?? 'Not provided')) . "\nDate: " . trim((string)($data['date'] ?? 'Not provided')) . "\nGuests: " . trim((string)($data['guests'] ?? '1')) . "\n\nPlease contact the guest to confirm the transfer.";
} elseif ($type === 'trip') {
    $message = "🧭 NEW TRIP REQUEST\n\nReference: {$reference}\nName: {$name}\nPhone: {$phone}\nTrip: " . trim((string)($data['trip'] ?? 'Not provided')) . "\nGuests: " . trim((string)($data['guests'] ?? '1')) . "\n\nPlease contact the guest to confirm the trip.";
} else {
    $message = "🏨 NEW HOTEL BOOKING REQUEST\n\nReference: {$reference}\nGuest: {$name}\nPhone: {$phone}\nRoom: " . trim((string)($data['room'] ?? 'Any room')) . "\nCheck-in: " . trim((string)($data['check_in'] ?? 'Not provided')) . "\nCheck-out: " . trim((string)($data['check_out'] ?? 'Not provided')) . "\nGuests: " . trim((string)($data['guests'] ?? '2')) . "\n\nPlease contact the guest to confirm availability and price.";
}

if ($notifyNumber === '') {
    reply_json(false, 'The WhatsApp notification number is not configured yet.', [], 503);
}

$payload = json_encode([
    'recipient' => $notifyNumber,
    'message' => $message
], JSON_UNESCAPED_UNICODE);

$ch = curl_init('https://business.momo.tz/api/v3/whatsapp/send');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => $payload
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError !== '') {
    reply_json(false, 'Could not reach Momo Business: ' . $curlError, [], 502);
}

$result = json_decode((string)$response, true);
if ($httpCode < 200 || $httpCode >= 300 || !is_array($result) || ($result['status'] ?? '') === 'error') {
    $providerMessage = (string)($result['message'] ?? 'Momo Business rejected the WhatsApp request.');
    reply_json(false, $providerMessage, ['provider_status' => $httpCode], 502);
}

$messages = $result['data']['messages'] ?? [];
$status = is_array($messages) && isset($messages[0]['status']) ? (string)$messages[0]['status'] : 'queued';
if ($status === 'failed') {
    $error = is_array($messages) ? (string)($messages[0]['error_message'] ?? 'WhatsApp delivery failed.') : 'WhatsApp delivery failed.';
    reply_json(false, $error, ['reference' => $reference, 'provider_status' => $httpCode], 502);
}

reply_json(true, 'Your request was sent to hotel reception on WhatsApp.', [
    'reference' => $reference,
    'status' => $status
]);
