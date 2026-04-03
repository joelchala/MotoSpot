<?php
/**
 * MotoSpot — Cron: Procesador de cola de emails
 * Ejecutar cada 5 minutos desde hPanel:
 *   php /home/u986675534/domains/php.autolatino.site/public_html/cron/process_emails.php
 */

// Solo CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado');
}

define('MOTOSPOT_CRON', true);
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/includes/env.php';
require_once ROOT_PATH . '/includes/logger.php';
require_once ROOT_PATH . '/includes/db.php';

loadEnv();

const BATCH_SIZE     = 20;   // emails por ejecución
const MAX_RETRIES    = 3;
const RETRY_DELAY    = 300;  // segundos entre reintentos

logInfo('[cron:emails] Iniciando procesador de cola');

try {
    $pdo = getDB();

    // Tomar emails pendientes (o fallidos con retraso)
    $stmt = $pdo->prepare("
        SELECT id, to_email, to_name, subject, body_html, body_text, retries
        FROM ms_email_queue
        WHERE status = 'pending'
          AND (next_retry_at IS NULL OR next_retry_at <= NOW())
        ORDER BY created_at ASC
        LIMIT :batch
    ");
    $stmt->bindValue(':batch', BATCH_SIZE, PDO::PARAM_INT);
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($emails)) {
        logInfo('[cron:emails] Cola vacía, nada que procesar');
        exit(0);
    }

    logInfo('[cron:emails] ' . count($emails) . ' emails en cola');

    $sent   = 0;
    $failed = 0;

    foreach ($emails as $email) {
        // Marcar como procesando para evitar doble envío en ejecuciones paralelas
        $pdo->prepare("UPDATE ms_email_queue SET status = 'processing' WHERE id = ?")
            ->execute([$email['id']]);

        $ok = sendEmail($email);

        if ($ok) {
            $pdo->prepare("UPDATE ms_email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?")
                ->execute([$email['id']]);
            $sent++;
            logInfo('[cron:emails] Enviado OK: ' . $email['to_email'] . ' — ' . $email['subject']);
        } else {
            $retries     = $email['retries'] + 1;
            $newStatus   = $retries >= MAX_RETRIES ? 'failed' : 'pending';
            $nextRetry   = date('Y-m-d H:i:s', time() + RETRY_DELAY * $retries);

            $pdo->prepare("
                UPDATE ms_email_queue
                SET status = ?, retries = ?, next_retry_at = ?
                WHERE id = ?
            ")->execute([$newStatus, $retries, $nextRetry, $email['id']]);

            $failed++;
            logWarning('[cron:emails] Fallo envío: ' . $email['to_email'] . " (intento $retries)");
        }
    }

    logInfo("[cron:emails] Fin: $sent enviados, $failed fallidos");

} catch (Throwable $e) {
    logCritical('[cron:emails] Error fatal: ' . $e->getMessage());
    exit(1);
}

// ── Función de envío ──────────────────────────────────────────────────────────

function sendEmail(array $email): bool
{
    $host       = env('MAIL_HOST', '');
    $port       = (int) env('MAIL_PORT', 587);
    $user       = env('MAIL_USER', '');
    $pass       = env('MAIL_PASS', '');
    $fromEmail  = env('MAIL_FROM', 'noreply@php.autolatino.site');
    $fromName   = env('MAIL_FROM_NAME', 'MotoSpot');
    $encryption = env('MAIL_ENCRYPTION', 'tls');

    if (empty($host) || empty($user) || empty($pass)) {
        // Fallback: mail() nativo de PHP
        return sendWithPhpMail($email, $fromEmail, $fromName);
    }

    return sendWithSmtp($email, $host, $port, $user, $pass, $fromEmail, $fromName, $encryption);
}

function sendWithPhpMail(array $email, string $fromEmail, string $fromName): bool
{
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>\r\n";
    $headers .= "X-Mailer: MotoSpot/1.0\r\n";

    $subject = '=?UTF-8?B?' . base64_encode($email['subject']) . '?=';
    $body    = !empty($email['body_html']) ? $email['body_html'] : nl2br(htmlspecialchars($email['body_text']));

    return mail($email['to_email'], $subject, $body, $headers);
}

function sendWithSmtp(
    array $email,
    string $host,
    int $port,
    string $user,
    string $pass,
    string $fromEmail,
    string $fromName,
    string $encryption
): bool {
    $prefix = ($encryption === 'ssl') ? 'ssl://' : '';
    $errno  = 0;
    $errstr = '';

    $socket = @fsockopen($prefix . $host, $port, $errno, $errstr, 10);
    if (!$socket) {
        logWarning("[SMTP] No se pudo conectar a $host:$port — $errstr");
        return false;
    }

    stream_set_timeout($socket, 10);

    try {
        smtpRead($socket);                                                      // 220
        smtpCmd($socket, "EHLO " . gethostname());                              // 250
        if ($encryption === 'tls') {
            smtpCmd($socket, "STARTTLS");                                       // 220
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            smtpCmd($socket, "EHLO " . gethostname());                          // 250
        }
        smtpCmd($socket, "AUTH LOGIN");                                         // 334
        smtpCmd($socket, base64_encode($user));                                 // 334
        smtpCmd($socket, base64_encode($pass));                                 // 235
        smtpCmd($socket, "MAIL FROM:<$fromEmail>");                             // 250
        smtpCmd($socket, "RCPT TO:<{$email['to_email']}>");                     // 250
        smtpCmd($socket, "DATA");                                               // 354

        $body    = !empty($email['body_html']) ? $email['body_html'] : nl2br(htmlspecialchars($email['body_text']));
        $subject = '=?UTF-8?B?' . base64_encode($email['subject']) . '?=';
        $msg  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>\r\n";
        $msg .= "To: {$email['to_name']} <{$email['to_email']}>\r\n";
        $msg .= "Subject: $subject\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $msg .= $body . "\r\n.\r\n";

        smtpCmd($socket, $msg, false);                                          // 250
        smtpCmd($socket, "QUIT");                                               // 221
    } finally {
        fclose($socket);
    }

    return true;
}

function smtpCmd($socket, string $cmd, bool $newline = true): string
{
    fwrite($socket, $cmd . ($newline ? "\r\n" : ''));
    return smtpRead($socket);
}

function smtpRead($socket): string
{
    $response = '';
    $deadline = time() + 12; // máx 12s leyendo respuesta
    while (time() < $deadline) {
        $line = fgets($socket, 515);
        if ($line === false) break; // timeout o conexión cerrada
        $response .= $line;
        if (substr($line, 3, 1) === ' ') break; // fin de respuesta multi-línea
    }
    return $response;
}
