<?php
/**
 * MotoSpot - Planes de Suscripcion
 */
defined('MOTO_SPOT') || define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Planes y Precios';

$planes = [
    'destacado' => [
        'nombre'     => 'Destacado',
        'icono'      => 'fa-star',
        'precio_mes' => 9.99,
        'precio_ano' => 7.99,
        'descripcion'=> 'Mayor visibilidad para vender mas rapido',
        'color'      => 'blue',
        'cta'        => 'Elegir Destacado',
        'cta_link'   => '/register.php?plan=destacado',
        'featured'   => true,
        'items' => [
            ['ok'=>true,  'txt'=>'3 publicaciones activas'],
            ['ok'=>true,  'txt'=>'Duracion: 45 dias'],
            ['ok'=>true,  'txt'=>'Fotos: 10 por vehiculo'],
            ['ok'=>true,  'txt'=>'Visibilidad destacada'],
            ['ok'=>true,  'txt'=>'Soporte prioritario'],
            ['ok'=>true,  'txt'=>'Estadisticas avanzadas'],
            ['ok'=>true,  'txt'=>'Badge "Destacado"'],
            ['ok'=>true,  'txt'=>'Posicion prioritaria en busquedas'],
            ['ok'=>false, 'txt'=>'Publicidad en redes sociales'],
        ],
    ],
    'premium' => [
        'nombre'     => 'Premium',
        'icono'      => 'fa-crown',
        'precio_mes' => 24.99,
        'precio_ano' => 19.99,
        'descripcion'=> 'Maxima exposicion para agencias y vendedores profesionales',
        'color'      => 'gold',
        'cta'        => 'Elegir Premium',
        'cta_link'   => '/register.php?plan=premium',
        'featured'   => false,
        'items' => [
            ['ok'=>true,  'txt'=>'Publicaciones ilimitadas'],
            ['ok'=>true,  'txt'=>'Duracion: 60 dias'],
            ['ok'=>true,  'txt'=>'Fotos: 20 por vehiculo'],
            ['ok'=>true,  'txt'=>'Maxima visibilidad'],
            ['ok'=>true,  'txt'=>'Soporte 24/7'],
            ['ok'=>true,  'txt'=>'Estadisticas profesionales'],
            ['ok'=>true,  'txt'=>'Badge "Premium"'],
            ['ok'=>true,  'txt'=>'Posicion top en busquedas'],
            ['ok'=>true,  'txt'=>'Exportar leads'],
            ['ok'=>true,  'txt'=>'Integracion con WhatsApp Business'],
            ['ok'=>false, 'txt'=>'Publicidad en redes sociales'],
        ],
    ],
    'premium_plus' => [
        'nombre'     => 'Premium Plus',
        'icono'      => 'fa-rocket',
        'precio_mes' => 49.99,
        'precio_ano' => 39.99,
        'descripcion'=> 'Visibilidad total: online y en nuestras redes sociales',
        'color'      => 'purple',
        'cta'        => 'Elegir Premium Plus',
        'cta_link'   => '/register.php?plan=premium_plus',
        'featured'   => false,
        'items' => [
            ['ok'=>true, 'txt'=>'Publicaciones ilimitadas'],
            ['ok'=>true, 'txt'=>'Duracion: 120 dias'],
            ['ok'=>true, 'txt'=>'Fotos: 20 por vehiculo'],
            ['ok'=>true, 'txt'=>'Maxima visibilidad'],
            ['ok'=>true, 'txt'=>'Soporte 24/7 dedicado'],
            ['ok'=>true, 'txt'=>'Estadisticas profesionales'],
            ['ok'=>true, 'txt'=>'Badge "Premium Plus"'],
            ['ok'=>true, 'txt'=>'Posicion #1 en busquedas'],
            ['ok'=>true, 'txt'=>'Exportar leads'],
            ['ok'=>true, 'txt'=>'Integracion con WhatsApp Business'],
            ['ok'=>true, 'txt'=>'Publicidad constante en redes sociales'],
            ['ok'=>true, 'txt'=>'Gestor de cuenta dedicado'],
            ['ok'=>true, 'txt'=>'Informe mensual de rendimiento'],
        ],
    ],
];

// Tabla comparativa
$comparativa = [
    'Publicaciones'       => ['3',          'Ilimitadas', 'Ilimitadas'],
    'Duracion'            => ['45 dias',    '60 dias',    '120 dias'],
    'Fotos por vehiculo'  => ['10',         '20',         '20'],
    'Visibilidad'         => ['Destacada',  'Maxima',     'Maxima'],
    'Soporte'             => ['Prioritario','24/7',       '24/7 dedicado'],
    'Estadisticas'        => ['Avanzadas',  'Profesionales','Profesionales'],
    'Badge'               => ['Destacado',  'Premium',    'Premium Plus'],
    'Exportar leads'      => ['—',          'Si',         'Si'],
    'WhatsApp Business'   => ['—',          'Si',         'Si'],
    'Publicidad en redes' => ['—',          '—',          'Si'],
    'Gestor dedicado'     => ['—',          '—',          'Si'],
];

$faq = [
    ['p'=>'Puedo cambiar de plan?',             'r'=>'Si, podas actualizar o cancelar tu plan en cualquier momento desde tu panel de usuario.'],
    ['p'=>'Hay contratos de permanencia?',       'r'=>'No, todos nuestros planes son mensuales y puedes cancelar cuando quieras sin penalizacion.'],
    ['p'=>'Que metodos de pago aceptan?',        'r'=>'Aceptamos tarjetas de credito/debito (Visa, Mastercard, Amex) y pagos locales segun tu pais.'],
    ['p'=>'Ofrecen descuentos para agencias?',   'r'=>'Si, las agencias con mas de 10 vehiculos activos obtienen descuentos especiales. Contactanos.'],
    ['p'=>'Como funciona la publicidad en redes?','r'=>'Con el plan Premium Plus publicamos tus vehiculos destacados en nuestras redes sociales de forma constante durante la vigencia de tu plan.'],
    ['p'=>'Que incluye el gestor de cuenta?',    'r'=>'Un asesor personal que te ayuda a optimizar tus publicaciones, responder consultas y maximizar tus ventas.'],
];

// Procesar codigo promo
require_once __DIR__ . '/../includes/codigos_promocionales.php';
$mensajePromo = '';
$tipoPromo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_promo'])) {
    // Verificar CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $mensajePromo = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
        $tipoPromo = 'error';
    } else {
        $codigo = strtoupper(trim($_POST['codigo_promo']));
        
        if (!estaAutenticado()) {
            $mensajePromo = 'Debes iniciar sesión para canjear un código.';
            $tipoPromo = 'error';
        } else {
            $resultado = canjearCodigoPromocional($codigo, $_SESSION['usuario_id']);
            $mensajePromo = $resultado['message'];
            $tipoPromo = $resultado['success'] ? 'success' : 'error';
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<style>
.plans-page{background:#0d0d1a;min-height:100vh;padding:6rem 1rem 4rem}
.plans-page .container{max-width:1300px;margin:0 auto}

/* Header */
.plans-page-header{text-align:center;margin-bottom:3rem}
.plans-page-header h1{font-size:clamp(2rem,5vw,3.5rem);font-weight:900;color:#fff;letter-spacing:-1.5px;margin-bottom:1rem}
.plans-page-header h1 span{background:linear-gradient(135deg,#3ABBE5,#e63946);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.plans-page-header p{color:rgba(255,255,255,.65);font-size:1.1rem;max-width:560px;margin:0 auto 2rem}

/* Toggle mensual/anual */
.billing-toggle{display:inline-flex;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:9999px;padding:.3rem;gap:.25rem;margin-bottom:3rem}
.billing-btn{padding:.5rem 1.5rem;border-radius:9999px;border:none;background:transparent;color:rgba(255,255,255,.6);font-size:.9rem;font-weight:600;cursor:pointer;transition:all .2s}
.billing-btn.active{background:#fff;color:#0d0d1a}
.billing-badge{background:#22c55e;color:#fff;font-size:.7rem;font-weight:700;padding:.2rem .5rem;border-radius:9999px;margin-left:.35rem}

/* Grid de planes */
.plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;margin-bottom:4rem;align-items:start}

/* Cards */
.plan-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2rem;position:relative;transition:transform .3s,box-shadow .3s}
.plan-card:hover{transform:translateY(-4px);box-shadow:0 20px 48px rgba(0,0,0,.4)}
.plan-card.featured{border-color:#3ABBE5;background:rgba(58,187,229,.06);transform:translateY(-8px);box-shadow:0 24px 64px rgba(58,187,229,.2)}
.plan-card.plan-purple{border-color:#a855f7;background:rgba(168,85,247,.06)}
.plan-card.plan-purple:hover{box-shadow:0 20px 48px rgba(168,85,247,.25)}
.plan-card.plan-gold{border-color:#f59e0b;background:rgba(245,158,11,.05)}
.plan-card.plan-gold:hover{box-shadow:0 20px 48px rgba(245,158,11,.2)}

.plan-badge{position:absolute;top:-1px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#3ABBE5,#2AA5CC);color:#fff;font-size:.7rem;font-weight:700;padding:.35rem 1.25rem;border-radius:0 0 10px 10px;white-space:nowrap;letter-spacing:.05em;text-transform:uppercase}
.plan-badge.purple{background:linear-gradient(135deg,#a855f7,#7c3aed)}

.plan-icon-wrap{width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin:0 auto 1.25rem}
.plan-icon-wrap.gray  {background:rgba(255,255,255,.08);color:rgba(255,255,255,.6)}
.plan-icon-wrap.blue  {background:rgba(58,187,229,.15);color:#3ABBE5}
.plan-icon-wrap.gold  {background:rgba(245,158,11,.15);color:#f59e0b}
.plan-icon-wrap.purple{background:rgba(168,85,247,.15);color:#a855f7}

.plan-name{font-size:1.4rem;font-weight:800;color:#fff;text-align:center;margin-bottom:.35rem}
.plan-desc{font-size:.85rem;color:rgba(255,255,255,.55);text-align:center;margin-bottom:1.5rem;line-height:1.5;min-height:40px}

.plan-price-wrap{text-align:center;margin-bottom:1.75rem}
.plan-price{font-size:3rem;font-weight:900;color:#fff;line-height:1}
.plan-price sup{font-size:1.2rem;vertical-align:super;font-weight:700}
.plan-price span{font-size:1rem;color:rgba(255,255,255,.5);font-weight:400}
.plan-price-free{font-size:2.5rem;font-weight:900;color:#fff}
.plan-price-anual{font-size:.8rem;color:#22c55e;margin-top:.35rem}

.plan-items{list-style:none;padding:0;margin:0 0 1.75rem;display:flex;flex-direction:column;gap:.6rem}
.plan-item{display:flex;align-items:center;gap:.65rem;font-size:.875rem}
.plan-item.ok   {color:rgba(255,255,255,.85)}
.plan-item.no   {color:rgba(255,255,255,.3);text-decoration:line-through}
.plan-item i.ok {color:#22c55e;flex-shrink:0}
.plan-item i.no {color:rgba(255,255,255,.25);flex-shrink:0}

.plan-cta{display:block;width:100%;padding:.85rem;border-radius:12px;font-weight:700;font-size:.95rem;text-align:center;text-decoration:none;border:none;cursor:pointer;transition:all .2s}
.plan-cta.ghost {background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.25)}
.plan-cta.ghost:hover{background:rgba(255,255,255,.08);border-color:#fff}
.plan-cta.blue  {background:linear-gradient(135deg,#3ABBE5,#2AA5CC);color:#fff}
.plan-cta.blue:hover{box-shadow:0 8px 24px rgba(58,187,229,.45);transform:translateY(-1px)}
.plan-cta.gold  {background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff}
.plan-cta.gold:hover{box-shadow:0 8px 24px rgba(245,158,11,.4);transform:translateY(-1px)}
.plan-cta.purple{background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff}
.plan-cta.purple:hover{box-shadow:0 8px 24px rgba(168,85,247,.45);transform:translateY(-1px)}

/* Tabla comparativa */
.compare-wrap{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:20px;overflow:hidden;margin-bottom:4rem}
.compare-wrap h2{font-size:1.6rem;font-weight:800;color:#fff;text-align:center;padding:2rem 2rem 1rem}
.compare-table{width:100%;border-collapse:collapse}
.compare-table th{background:rgba(255,255,255,.06);color:rgba(255,255,255,.6);font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:.9rem 1.25rem;text-align:center}
.compare-table th:first-child{text-align:left}
.compare-table th.col-blue  {color:#3ABBE5}
.compare-table th.col-gold  {color:#f59e0b}
.compare-table th.col-purple{color:#a855f7}
.compare-table td{padding:.85rem 1.25rem;border-top:1px solid rgba(255,255,255,.06);color:rgba(255,255,255,.75);font-size:.875rem;text-align:center}
.compare-table td:first-child{text-align:left;color:rgba(255,255,255,.9);font-weight:500}
.compare-table tr:hover td{background:rgba(255,255,255,.03)}
.compare-table .val-blue  {color:#3ABBE5;font-weight:600}
.compare-table .val-gold  {color:#f59e0b;font-weight:600}
.compare-table .val-purple{color:#a855f7;font-weight:600}
.compare-table .val-no    {color:rgba(255,255,255,.2)}
.compare-table .val-yes   {color:#22c55e;font-weight:600}

/* FAQ */
.faq-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:1.5rem;margin-bottom:4rem}
.faq-item{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:1.5rem}
.faq-item h4{font-size:.95rem;font-weight:700;color:#fff;margin-bottom:.6rem}
.faq-item p{font-size:.875rem;color:rgba(255,255,255,.6);line-height:1.6;margin:0}

/* Promo */
.promo-box{background:rgba(58,187,229,.06);border:1px solid rgba(58,187,229,.2);border-radius:20px;padding:2rem;text-align:center;margin-bottom:4rem}
.promo-box h3{font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:.5rem}
.promo-box p{color:rgba(255,255,255,.6);font-size:.9rem;margin-bottom:1.25rem}
.promo-form{display:flex;gap:.75rem;max-width:480px;margin:0 auto;flex-wrap:wrap;justify-content:center}
.promo-input{flex:1;min-width:200px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#fff;padding:.75rem 1rem;font-size:.95rem;outline:none}
.promo-input:focus{border-color:#3ABBE5}
.promo-input::placeholder{color:rgba(255,255,255,.4)}
.promo-submit{background:linear-gradient(135deg,#3ABBE5,#2AA5CC);color:#fff;border:none;border-radius:10px;padding:.75rem 1.75rem;font-weight:700;cursor:pointer;transition:all .2s}
.promo-submit:hover{box-shadow:0 6px 20px rgba(58,187,229,.4)}
.alert-success{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.alert-error  {background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}

/* CTA final */
.plans-cta{background:linear-gradient(135deg,rgba(58,187,229,.12),rgba(168,85,247,.1));border:1px solid rgba(58,187,229,.2);border-radius:24px;padding:3rem 2rem;text-align:center}
.plans-cta h2{font-size:1.8rem;font-weight:800;color:#fff;margin-bottom:.75rem}
.plans-cta p{color:rgba(255,255,255,.65);margin-bottom:1.75rem}
.plans-cta-btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.btn-ghost-white{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.3);padding:.85rem 2rem;border-radius:10px;font-weight:700;text-decoration:none;transition:all .2s}
.btn-ghost-white:hover{background:rgba(255,255,255,.1);border-color:#fff}
.btn-blue-grad{background:linear-gradient(135deg,#3ABBE5,#2AA5CC);color:#fff;padding:.85rem 2rem;border-radius:10px;font-weight:700;text-decoration:none;transition:all .2s}
.btn-blue-grad:hover{box-shadow:0 8px 24px rgba(58,187,229,.4);transform:translateY(-1px)}
</style>

<div class="plans-page">
<div class="container">

    <!-- Header -->
    <div class="plans-page-header">
        <h1>Planes y <span>Precios</span></h1>
        <p>Elige el plan que mejor se adapte a tus necesidades y empieza a vender hoy.</p>

        <!-- Toggle mensual / anual -->
        <div>
            <div class="billing-toggle">
                <button class="billing-btn active" id="btnMensual" onclick="setBilling('mensual')">Mensual</button>
                <button class="billing-btn" id="btnAnual" onclick="setBilling('anual')">
                    Anual <span class="billing-badge">-20%</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Grid de planes -->
    <div class="plans-grid">

        <!-- DESTACADO (featured) -->
        <div class="plan-card featured">
            <div class="plan-badge">Mas popular</div>
            <div class="plan-icon-wrap blue"><i class="fas fa-star"></i></div>
            <div class="plan-name">Destacado</div>
            <div class="plan-desc">Mayor visibilidad para vender mas rapido</div>
            <div class="plan-price-wrap">
                <div class="plan-price">
                    <sup>$</sup><span id="precio-destacado-num">9.99</span><span id="precio-destacado-per">/mes</span>
                </div>
                <div class="plan-price-anual" id="ahorro-destacado" style="display:none">
                    Ahorras $24/ano
                </div>
            </div>
            <ul class="plan-items">
                <?php foreach ($planes['destacado']['items'] as $it): ?>
                <li class="plan-item <?= $it['ok']?'ok':'no' ?>">
                    <i class="fas <?= $it['ok']?'fa-check-circle ok':'fa-times-circle no' ?>"></i>
                    <?= htmlspecialchars($it['txt']) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="/register.php?plan=destacado" class="plan-cta blue">Elegir Destacado</a>
        </div>

        <!-- PREMIUM -->
        <div class="plan-card plan-gold">
            <div class="plan-icon-wrap gold"><i class="fas fa-crown"></i></div>
            <div class="plan-name">Premium</div>
            <div class="plan-desc">Maxima exposicion para agencias y vendedores profesionales</div>
            <div class="plan-price-wrap">
                <div class="plan-price">
                    <sup>$</sup><span id="precio-premium-num">24.99</span><span id="precio-premium-per">/mes</span>
                </div>
                <div class="plan-price-anual" id="ahorro-premium" style="display:none">
                    Ahorras $60/ano
                </div>
            </div>
            <ul class="plan-items">
                <?php foreach ($planes['premium']['items'] as $it): ?>
                <li class="plan-item <?= $it['ok']?'ok':'no' ?>">
                    <i class="fas <?= $it['ok']?'fa-check-circle ok':'fa-times-circle no' ?>"></i>
                    <?= htmlspecialchars($it['txt']) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="/register.php?plan=premium" class="plan-cta gold">Elegir Premium</a>
        </div>

        <!-- PREMIUM PLUS -->
        <div class="plan-card plan-purple">
            <div class="plan-badge purple">Nuevo</div>
            <div class="plan-icon-wrap purple"><i class="fas fa-rocket"></i></div>
            <div class="plan-name">Premium Plus</div>
            <div class="plan-desc">Visibilidad total: online y en nuestras redes sociales</div>
            <div class="plan-price-wrap">
                <div class="plan-price" style="-webkit-text-fill-color:#a855f7">
                    <sup style="color:#a855f7">$</sup><span id="precio-plus-num">49.99</span><span id="precio-plus-per" style="color:rgba(255,255,255,.5)">/mes</span>
                </div>
                <div class="plan-price-anual" id="ahorro-plus" style="display:none">
                    Ahorras $120/ano
                </div>
            </div>
            <ul class="plan-items">
                <?php foreach ($planes['premium_plus']['items'] as $it): ?>
                <li class="plan-item ok">
                    <i class="fas fa-check-circle ok"></i>
                    <?= htmlspecialchars($it['txt']) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="/register.php?plan=premium_plus" class="plan-cta purple">Elegir Premium Plus</a>
        </div>

    </div><!-- /plans-grid -->

    <!-- Tabla comparativa -->
    <div class="compare-wrap">
        <h2>Que incluye cada plan?</h2>
        <div style="overflow-x:auto">
        <table class="compare-table">
            <thead>
                <tr>
                    <th style="width:220px">Caracteristica</th>
                    <th class="col-blue">Destacado</th>
                    <th class="col-gold">Premium</th>
                    <th class="col-purple">Premium Plus</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $colClass = ['val-blue', 'val-gold', 'val-purple'];
                foreach ($comparativa as $caract => $vals):
                    $cols = array_values($vals);
                ?>
                <tr>
                    <td><?= htmlspecialchars($caract) ?></td>
                    <?php foreach ($cols as $ci => $v):
                        $cls = '';
                        if ($v === '—')    $cls = 'val-no';
                        elseif ($v === 'Si') $cls = 'val-yes';
                        else   $cls = $colClass[$ci % count($colClass)]; // Usar modulo para seguridad
                    ?>
                    <td class="<?= $cls ?>"><?= htmlspecialchars($v) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- FAQ -->
    <div style="text-align:center;margin-bottom:2rem">
        <span style="display:inline-block;font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#3ABBE5;margin-bottom:.75rem">FAQ</span>
        <h2 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:800;color:#fff;letter-spacing:-1px">Preguntas frecuentes</h2>
    </div>
    <div class="faq-grid">
        <?php foreach ($faq as $f): ?>
        <div class="faq-item">
            <h4><i class="fas fa-question-circle" style="color:#3ABBE5;margin-right:.5rem"></i><?= htmlspecialchars($f['p']) ?></h4>
            <p><?= htmlspecialchars($f['r']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Codigo promo -->
    <div class="promo-box">
        <h3><i class="fas fa-ticket-alt" style="color:#3ABBE5;margin-right:.5rem"></i>Tenes un codigo promocional?</h3>
        <p>Sos dealer? Canjea tu codigo para acceder al plan Premium Plus por 30 dias.</p>
        <?php if ($mensajePromo): ?>
            <div class="alert-<?= $tipoPromo ?>"><?= htmlspecialchars($mensajePromo) ?></div>
        <?php endif; ?>
        <form action="/planes.php" method="POST" class="promo-form">
            <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
            <input type="text" name="codigo_promo" class="promo-input"
                   placeholder="Ej: DEALER-XXXXXXXX" maxlength="20" required>
            <button type="submit" class="promo-submit">Canjear</button>
        </form>
    </div>

    <!-- CTA final -->
    <div class="plans-cta">
        <h2>Tienes preguntas sobre nuestros planes?</h2>
        <p>Nuestro equipo esta listo para ayudarte a elegir el plan perfecto para ti.</p>
        <div class="plans-cta-btns">
            <a href="/listado-vehiculos.php" class="btn-blue-grad"><i class="fas fa-search" style="margin-right:.5rem"></i>Buscar vehiculos</a>
        </div>
    </div>

</div><!-- /container -->
</div><!-- /plans-page -->

<script>
const precios = {
    destacado: { mes: '9.99',  ano: '7.99'  },
    premium:   { mes: '24.99', ano: '19.99' },
    plus:      { mes: '49.99', ano: '39.99' },
};

function setBilling(type) {
    document.getElementById('btnMensual').classList.toggle('active', type === 'mensual');
    document.getElementById('btnAnual').classList.toggle('active', type === 'anual');

    ['destacado','premium','plus'].forEach(p => {
        document.getElementById('precio-' + p + '-num').textContent = precios[p][type === 'anual' ? 'ano' : 'mes'];
        document.getElementById('precio-' + p + '-per').textContent = type === 'anual' ? '/mes*' : '/mes';
        const ahorro = document.getElementById('ahorro-' + p);
        if (ahorro) ahorro.style.display = type === 'anual' ? 'block' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
