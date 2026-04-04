<?php
/**
 * MotoSpot — Mailer helper
 * Encola emails en ms_email_queue para envío asíncrono vía cron.
 *
 * Uso:
 *   queueEmail(
 *       'usuario@ejemplo.com',
 *       'Juan Pérez',
 *       'Tu vehículo fue publicado',
 *       '<p>Hola Juan...</p>',
 *       ['vehiculo_id' => 42, 'tipo_evento' => 'publicacion']
 *   );
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

/**
 * Encola un email para envío asíncrono.
 *
 * @param string      $toEmail   Dirección destino
 * @param string      $toName    Nombre del destinatario
 * @param string      $subject   Asunto del email
 * @param string      $bodyHtml  Cuerpo HTML (recomendado)
 * @param array       $metadata  Datos extra para trazabilidad (opcional)
 * @param string|null $bodyText  Versión texto plano (opcional)
 * @return bool  true si se encoló correctamente
 */
function queueEmail(
    string $toEmail,
    string $toName,
    string $subject,
    string $bodyHtml,
    array  $metadata  = [],
    string $bodyText  = ''
): bool {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        logWarning("[mailer] Email inválido: $toEmail");
        return false;
    }

    try {
        $pdo  = getDB();
        $meta = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;

        $stmt = $pdo->prepare("
            INSERT INTO ms_email_queue
                (to_email, to_name, subject, body_html, body_text, metadata)
            VALUES
                (:to_email, :to_name, :subject, :body_html, :body_text, :metadata)
        ");

        $stmt->execute([
            ':to_email'  => $toEmail,
            ':to_name'   => $toName,
            ':subject'   => $subject,
            ':body_html' => $bodyHtml,
            ':body_text' => $bodyText,
            ':metadata'  => $meta,
        ]);

        logInfo("[mailer] Email encolado para $toEmail — $subject");
        return true;

    } catch (Throwable $e) {
        logError('[mailer] Error al encolar email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Plantilla HTML base para emails de MotoSpot.
 */
function emailTemplate(string $titulo, string $contenido, string $cta = '', string $ctaUrl = ''): string
{
    // Sanitizar inputs para prevenir HTML/JS injection
    $titulo_safe = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $contenido_safe = $contenido; // Ya debe estar sanitizado por el llamador
    $cta_safe = htmlspecialchars($cta, ENT_QUOTES, 'UTF-8');
    $ctaUrl_safe = htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8');
    
    $ctaBlock = '';
    if ($cta_safe && $ctaUrl_safe) {
        // Validar que la URL sea una ruta interna válida
        if (filter_var($ctaUrl_safe, FILTER_VALIDATE_URL) || str_starts_with($ctaUrl_safe, '/')) {
            $ctaBlock = "
        <p style='text-align:center;margin:30px 0'>
            <a href='" . $ctaUrl_safe . "'
               style='background:#e63946;color:#fff;padding:12px 28px;
                      border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px'>
                " . $cta_safe . "
            </a>
        </p>";
        }
    }

    return "<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif'>
  <table width='100%' cellpadding='0' cellspacing='0'>
    <tr><td align='center' style='padding:30px 15px'>
      <table width='600' cellpadding='0' cellspacing='0'
             style='background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)'>
        <!-- Header -->
        <tr><td style='background:#1a1a2e;padding:24px 32px'>
          <h1 style='margin:0;color:#e63946;font-size:22px'>MotoSpot</h1>
          <p style='margin:4px 0 0;color:#aaa;font-size:13px'>Marketplace de Vehículos</p>
        </td></tr>
        <!-- Body -->
        <tr><td style='padding:32px'>
          <h2 style='color:#1a1a2e;margin-top:0'>" . $titulo_safe . "</h2>
          " . $contenido_safe . "
          " . $ctaBlock . "
        </td></tr>
        <!-- Footer -->
        <tr><td style='background:#f9f9f9;padding:20px 32px;text-align:center;color:#999;font-size:12px;border-top:1px solid #eee'>
          <p style='margin:0'>MotoSpot &mdash; php.autolatino.site</p>
          <p style='margin:4px 0 0'>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";
}
?>
