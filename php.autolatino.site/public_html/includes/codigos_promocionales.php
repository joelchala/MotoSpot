<?php
/**
 * MotoSpot - Sistema de Codigos Promocionales
 * 
 * Caracteristicas:
 * - Solo aplican para el plan Premium Plus (dealers)
 * - Duracion: 1 mes (30 dias)
 * - Un solo canje por codigo
 * - Panel para generar codigos
 */

if (!defined('MOTO_SPOT')) {
    die('Acceso no autorizado');
}

require_once __DIR__ . '/db.php';

/**
 * Genera un codigo promocional unico
 */
function generarCodigoPromocional(int $creadoPor, string $notas = '', int $duracionDias = 30): array {
    global $pdo;
    
    try {
        $prefijo = 'DEALER';
        $sufijo = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        $codigo = $prefijo . '-' . $sufijo;
        
        $stmt = $pdo->prepare("SELECT id FROM " . table('codigos_promocionales') . " WHERE codigo = ?");
        $stmt->execute([$codigo]);
        
        while ($stmt->fetch()) {
            $sufijo = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
            $codigo = $prefijo . '-' . $sufijo;
            $stmt->execute([$codigo]);
        }
        
        $stmt = $pdo->prepare("INSERT INTO " . table('codigos_promocionales') . " 
            (codigo, plan_destino, duracion_dias, creado_por, notas, fecha_expiracion) 
            VALUES (?, 'premium_plus', ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
        
        $stmt->execute([$codigo, $duracionDias, $creadoPor, $notas]);
        
        return [
            'success' => true,
            'codigo' => $codigo,
            'message' => 'Codigo generado exitosamente'
        ];
        
    } catch (PDOException $e) {
        error_log('[MotoSpot][Codigos] Error: ' . $e->getMessage());
        return [
            'success' => false,
            'codigo' => null,
            'message' => 'Error al generar el codigo'
        ];
    }
}

/**
 * Valida un codigo promocional
 */
function validarCodigoPromocional(string $codigo): array {
    global $pdo;
    
    try {
        $codigo = strtoupper(trim($codigo));
        
        if (empty($codigo)) {
            return ['valido' => false, 'message' => 'Ingrese un codigo valido', 'data' => null];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM " . table('codigos_promocionales') . " WHERE codigo = ? AND activo = 1");
        $stmt->execute([$codigo]);
        $codigoData = $stmt->fetch();
        
        if (!$codigoData) {
            return ['valido' => false, 'message' => 'El codigo no existe o esta inactivo', 'data' => null];
        }
        
        if ($codigoData['usado']) {
            return ['valido' => false, 'message' => 'Este codigo ya ha sido utilizado', 'data' => null];
        }
        
        if ($codigoData['fecha_expiracion'] && strtotime($codigoData['fecha_expiracion']) < time()) {
            return ['valido' => false, 'message' => 'Este codigo ha expirado', 'data' => null];
        }
        
        return ['valido' => true, 'message' => 'Codigo valido', 'data' => $codigoData];
        
    } catch (PDOException $e) {
        error_log('[MotoSpot][Codigos] Error: ' . $e->getMessage());
        return ['valido' => false, 'message' => 'Error al validar', 'data' => null];
    }
}

/**
 * Canjea un codigo promocional
 */
function canjearCodigoPromocional(string $codigo, int $usuarioId): array {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $validacion = validarCodigoPromocional($codigo);
        
        if (!$validacion['valido']) {
            return ['success' => false, 'message' => $validacion['message']];
        }
        
        $codigoData = $validacion['data'];
        $fechaInicio = date('Y-m-d');
        $fechaFin = date('Y-m-d', strtotime("+" . $codigoData['duracion_dias'] . " days"));
        
        $stmt = $pdo->prepare("UPDATE " . table('codigos_promocionales') . " 
            SET usado = 1, usado_por = ?, usado_en = NOW(), fecha_inicio = ? WHERE id = ?");
        $stmt->execute([$usuarioId, $fechaInicio, $codigoData['id']]);
        
        $stmt = $pdo->prepare("INSERT INTO " . table('historial_codigos') . " 
            (codigo_id, usuario_id, codigo, plan_asignado, duracion_dias, fecha_inicio, fecha_fin) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $codigoData['id'], $usuarioId, $codigoData['codigo'], 
            $codigoData['plan_destino'], $codigoData['duracion_dias'],
            $fechaInicio, $fechaFin
        ]);
        
        $stmt = $pdo->prepare("UPDATE " . table('usuarios') . " 
            SET plan = 'premium_plus', codigo_promo_activo = 1, codigo_promo_hasta = ? WHERE id = ?");
        $stmt->execute([$fechaFin, $usuarioId]);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Codigo canjeado! Tienes acceso Premium Plus por ' . $codigoData['duracion_dias'] . ' dias.'
        ];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('[MotoSpot][Codigos] Error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error al canjear'];
    }
}

/**
 * Obtiene todos los codigos
 */
function obtenerCodigosPromocionales(): array {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT cp.*, u1.nombre as creado_por_nombre, u2.nombre as usado_por_nombre 
            FROM " . table('codigos_promocionales') . " cp 
            LEFT JOIN " . table('usuarios') . " u1 ON cp.creado_por = u1.id 
            LEFT JOIN " . table('usuarios') . " u2 ON cp.usado_por = u2.id 
            ORDER BY cp.creado_en DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('[MotoSpot][Codigos] Error: ' . $e->getMessage());
        return [];
    }
}
