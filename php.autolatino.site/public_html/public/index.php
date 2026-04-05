<?php
defined('MOTO_SPOT') || define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/env.php';
loadEnv();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/stock_media.php';

function getHeroVideo(): array {
    try {
        $q = ['sports car driving night','motorcycle highway','luxury car speed'];
        $v = pixabaySearchVideos($q[array_rand($q)], 5);
        if (empty($v)) return ['url'=>'','thumb'=>''];
        usort($v, fn($a,$b) => $b['width']-$a['width']);
        return ['url'=>$v[0]['url_large']?:$v[0]['url_medium'], 'thumb'=>$v[0]['url_thumb']];
    } catch (Exception $e) {
        logError('Error fetching hero video', ['error' => $e->getMessage()]);
        return ['url'=>'','thumb'=>''];
    }
}
function getFeaturedImages(): array {
    $s = [
        ['label'=>'Autos',         'icon'=>'fa-car',        'link'=>'/listado-vehiculos.php?tipo=auto',        'gradient'=>'linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%)', 'texture'=>'radial-gradient(circle at 20% 80%, rgba(58,187,229,0.1) 0%, transparent 50%)'],
        ['label'=>'Motos',         'icon'=>'fa-motorcycle', 'link'=>'/listado-vehiculos.php?tipo=moto',        'gradient'=>'linear-gradient(135deg, #1a1a2e 0%, #2d1b4e 50%, #1a1a2e 100%)', 'texture'=>'radial-gradient(circle at 80% 20%, rgba(168,85,247,0.1) 0%, transparent 50%)'],
        ['label'=>'Pickups',       'icon'=>'fa-truck',      'link'=>'/listado-vehiculos.php?tipo=pickup',      'gradient'=>'linear-gradient(135deg, #1a1a2e 0%, #1e3a5f 50%, #0d2137 100%)', 'texture'=>'radial-gradient(circle at 50% 50%, rgba(59,130,246,0.08) 0%, transparent 60%)'],
        ['label'=>'SUVs',          'icon'=>'fa-car-side',   'link'=>'/listado-vehiculos.php?tipo=suv',         'gradient'=>'linear-gradient(135deg, #1a1a2e 0%, #2d3748 50%, #1a202c 100%)', 'texture'=>'radial-gradient(circle at 30% 70%, rgba(100,116,139,0.1) 0%, transparent 50%)'],
        ['label'=>'Embarcaciones', 'icon'=>'fa-ship',       'link'=>'/embarcaciones.php',                      'gradient'=>'linear-gradient(135deg, #0d1b2a 0%, #1b263b 50%, #0d1b2a 100%)', 'texture'=>'radial-gradient(circle at 70% 30%, rgba(56,189,248,0.1) 0%, transparent 50%)'],
        ['label'=>'Electricos',    'icon'=>'fa-bolt',       'link'=>'/listado-vehiculos.php?tipo=electrico',   'gradient'=>'linear-gradient(135deg, #064e3b 0%, #065f46 50%, #064e3b 100%)', 'texture'=>'radial-gradient(circle at 40% 60%, rgba(52,211,153,0.1) 0%, transparent 50%)'],
    ];
    return $s;
}
// Límite de 25s para las llamadas a APIs externas (evita que el servidor quede colgado)
set_time_limit(25);

$hero     = getHeroVideo();
$featured = getFeaturedImages();
$allVideos = [];
try {
    foreach (['sports car racing','motorcycle ride','luxury automobile'] as $vq) {
        $videos = pixabaySearchVideos($vq, 3);
        if (!empty($videos)) {
            $allVideos = array_merge($allVideos, $videos);
        }
    }
    $allVideos = array_slice($allVideos, 0, 6);
} catch (Exception $e) {
    logWarning('Error fetching featured videos', ['error' => $e->getMessage()]);
    $allVideos = []; // fallback: no videos
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="MotoSpot - La plataforma lider para comprar y vender vehiculos. Encontra autos, motos y embarcaciones al mejor precio.">
<title>MotoSpot - Compra y vende vehiculos</title>
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/landing-modern.css">
<style>
:root{--ms-blue:#3ABBE5;--ms-blue-d:#2AA5CC;--ms-dark:#0d0d1a;--ms-dark2:#1a1a2e}
.brand-logo{display:flex;align-items:center;gap:.75rem;text-decoration:none}
.brand-logo-icon{width:40px;height:40px;background:var(--ms-blue);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;box-shadow:0 2px 8px rgba(58,187,229,.3)}
.brand-logo-text{display:flex;align-items:baseline;font-size:1.5rem;font-weight:800;letter-spacing:-.5px}
.brand-logo-moto{color:#fff}.brand-logo-spot{color:var(--ms-blue)}
.brand-logo-tm{font-size:.5rem;color:var(--ms-blue);margin-left:2px;vertical-align:super;font-weight:600}
.footer-logo{font-size:1.75rem}.footer-logo .brand-logo-icon{width:48px;height:48px;font-size:1.5rem}
.hero{position:relative;min-height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;overflow:hidden}
.hero-video-wrap{position:absolute;inset:0;z-index:0}
.hero-video-wrap video{width:100%;height:100%;object-fit:cover;filter:brightness(.45) saturate(1.2)}
.hero-fallback{width:100%;height:100%;background:linear-gradient(135deg,#0d0d1a 0%,#1a1a2e 50%,#0f3460 100%)}
.hero-video-wrap::after{content:"";position:absolute;inset:0;background:linear-gradient(to bottom,rgba(13,13,26,.1) 0%,rgba(13,13,26,0) 40%,rgba(13,13,26,.7) 80%,rgba(13,13,26,1) 100%)}
.hero-glow{position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(58,187,229,.15) 0%,transparent 70%);top:-100px;left:-100px;pointer-events:none;z-index:1;animation:pulse-glow 6s ease-in-out infinite}
.hero-glow-2{right:-100px;bottom:-100px;left:auto;top:auto;background:radial-gradient(circle,rgba(230,57,70,.12) 0%,transparent 70%);animation-delay:3s}
@keyframes pulse-glow{0%,100%{transform:scale(1);opacity:.8}50%{transform:scale(1.15);opacity:1}}
.hero-content{position:relative;z-index:2;text-align:center;padding:2rem 1rem;max-width:900px}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(58,187,229,.15);border:1px solid rgba(58,187,229,.3);color:var(--ms-blue);padding:.5rem 1.25rem;border-radius:9999px;font-size:.875rem;font-weight:600;margin-bottom:1.5rem;backdrop-filter:blur(8px)}
.hero-title{font-size:clamp(2.5rem,7vw,5.5rem);font-weight:900;line-height:1.05;color:#fff;margin-bottom:1.5rem;letter-spacing:-2px}
.hero-title span{background:linear-gradient(135deg,var(--ms-blue),#e63946);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-subtitle{font-size:1.2rem;color:rgba(255,255,255,.75);max-width:600px;margin:0 auto 2.5rem;line-height:1.7}
.hero-search{display:flex;gap:.75rem;max-width:680px;margin:0 auto 3rem;background:rgba(255,255,255,.08);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:.75rem}
.hero-search-select,.hero-search-input{flex:1;background:transparent;border:none;outline:none;color:#fff;font-size:1rem;padding:.5rem .75rem}
.hero-search-select option{background:#1a1a2e;color:#fff}
.hero-search-select{max-width:160px;border-right:1px solid rgba(255,255,255,.15);cursor:pointer}
.hero-search-input::placeholder{color:rgba(255,255,255,.5)}
.hero-search-btn{background:linear-gradient(135deg,var(--ms-blue),#2AA5CC);color:#fff;border:none;border-radius:10px;padding:.75rem 1.75rem;font-size:1rem;font-weight:600;cursor:pointer;transition:transform .2s,box-shadow .2s;white-space:nowrap}
.hero-search-btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(58,187,229,.4)}
.hero-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-bottom:3rem}
.hero-stats{position:relative;z-index:2;display:flex;gap:3rem;justify-content:center;flex-wrap:wrap;padding:2rem;margin-top:auto}
.hero-stat{text-align:center}
.hero-stat-number{font-size:2.5rem;font-weight:800;color:#fff}
.hero-stat-label{font-size:.875rem;color:rgba(255,255,255,.6);margin-top:.25rem}
.hero-stat-divider{width:1px;background:rgba(255,255,255,.2)}
.scroll-indicator{position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);z-index:2;display:flex;flex-direction:column;align-items:center;gap:.5rem;color:rgba(255,255,255,.5);font-size:.8rem;animation:bounce 2s ease-in-out infinite}
@keyframes bounce{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(6px)}}
.categories-section{background:var(--ms-dark);padding:5rem 1rem}
.categories-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;max-width:1200px;margin:3rem auto 0}
.category-card{position:relative;border-radius:20px;overflow:hidden;height:220px;cursor:pointer;transition:transform .3s,box-shadow .3s;display:block;text-decoration:none}
.category-card:hover{transform:translateY(-6px);box-shadow:0 20px 48px rgba(0,0,0,.5)}
.category-card img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.category-card:hover img{transform:scale(1.08)}
.category-card-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85) 0%,rgba(0,0,0,.2) 60%,transparent 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:1.5rem;transition:background .3s}
.category-card:hover .category-card-overlay{background:linear-gradient(to top,rgba(58,187,229,.7) 0%,rgba(0,0,0,.2) 70%,transparent 100%)}
.category-card-icon{font-size:1.5rem;color:rgba(255,255,255,.8);margin-bottom:.5rem}
.category-card-label{font-size:1.25rem;font-weight:700;color:#fff}
.category-card-arrow{position:absolute;top:1rem;right:1rem;color:rgba(255,255,255,.6);font-size:1.1rem;transition:transform .3s,color .3s}
.category-card:hover .category-card-arrow{transform:translate(3px,-3px);color:#fff}
.category-card-fallback{width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgba(58,187,229,.4)}
.section-label{display:inline-block;font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--ms-blue);margin-bottom:.75rem}
.section-title{font-size:clamp(1.8rem,4vw,3rem);font-weight:800;color:#fff;margin-bottom:1rem;line-height:1.1;letter-spacing:-1px}
.section-subtitle{color:rgba(255,255,255,.6);font-size:1.1rem;max-width:600px;margin:0 auto}
.section-header{text-align:center;margin-bottom:3rem}
.video-section{background:#0a0a16;padding:5rem 1rem}
.video-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;max-width:1200px;margin:3rem auto 0}
.video-card{border-radius:16px;overflow:hidden;position:relative;background:#1a1a2e;cursor:pointer;transition:transform .3s}
.video-card:hover{transform:translateY(-4px)}
.video-thumb{width:100%;height:180px;object-fit:cover;display:block}
.video-thumb-fallback{width:100%;height:180px;background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:rgba(58,187,229,.3)}
.video-play-btn{position:absolute;top:50%;left:50%;transform:translate(-50%,-60%);width:56px;height:56px;border-radius:50%;background:rgba(58,187,229,.9);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;transition:transform .2s,background .2s}
.video-card:hover .video-play-btn{transform:translate(-50%,-60%) scale(1.1);background:var(--ms-blue)}
.video-duration{position:absolute;bottom:50px;right:.75rem;background:rgba(0,0,0,.75);color:#fff;font-size:.75rem;padding:.2rem .5rem;border-radius:4px}
.video-info{padding:1rem}
.video-tags{font-size:.8rem;color:rgba(255,255,255,.5);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.video-author{font-size:.75rem;color:var(--ms-blue);margin-top:.25rem}
.video-modal{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.9);align-items:center;justify-content:center}
.video-modal.open{display:flex}
.video-modal-inner{position:relative;width:90%;max-width:900px}
.video-modal-inner video{width:100%;border-radius:12px}
.video-modal-close{position:absolute;top:-2.5rem;right:0;color:#fff;font-size:1.5rem;cursor:pointer;background:none;border:none}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.75rem;border-radius:10px;font-weight:600;text-decoration:none;transition:all .2s;border:2px solid transparent;font-size:1rem;cursor:pointer}
.btn-primary{background:linear-gradient(135deg,var(--ms-blue),#2AA5CC);color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(58,187,229,.35)}
.btn-outline{background:transparent;color:#fff;border-color:rgba(255,255,255,.3)}
.btn-outline:hover{background:rgba(255,255,255,.1);border-color:#fff}
.btn-secondary{background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.2)}
.btn-secondary:hover{background:rgba(255,255,255,.2)}
.btn-large{padding:1rem 2.25rem;font-size:1.1rem}
.btn-glow:hover{box-shadow:0 0 30px rgba(58,187,229,.5)}

/* ── Responsive fixes móvil ── */
@media (max-width: 768px) {
    .navbar-menu { display:none; position:absolute; top:100%; left:0; right:0;
        background:rgba(10,10,22,.98); backdrop-filter:blur(20px);
        flex-direction:column; padding:1.5rem; gap:1rem;
        border-top:1px solid rgba(255,255,255,.1); z-index:999; }
    .navbar-menu.active { display:flex; }
    .hero-stat-divider { display:none; }
    .hero-stats { gap:1.5rem; padding:1.5rem 1rem; }
    .hero-stat { flex:0 0 45%; }
    .hero-content { padding:1.5rem .75rem; }
    .hero-search { flex-direction:column; gap:.5rem; padding:.5rem; border-radius:12px; }
    .hero-search-select { max-width:100%; border-right:none; border-bottom:1px solid rgba(255,255,255,.15); }
    .hero-search-btn { width:100%; justify-content:center; }
    .categories-grid { grid-template-columns:repeat(2,1fr); gap:.75rem; }
    .category-card { height:150px; }
    .category-card-label { font-size:1rem; }
    .features-grid { grid-template-columns:1fr; }
    .presentation-grid { grid-template-columns:1fr; }
    .presentation-image { order:-1; margin-bottom:1.5rem; }
    .footer-grid { grid-template-columns:1fr; gap:2rem; }
    .footer-bottom { flex-direction:column; text-align:center; gap:.5rem; }
    .section-title { font-size:clamp(1.5rem,5vw,2.2rem); }
    .hero-title { font-size:clamp(2rem,7vw,3.5rem); letter-spacing:-1px; }
    .hero-subtitle { font-size:1rem; }
}
@media (max-width: 400px) {
    .categories-grid { grid-template-columns:1fr; }
    .hero-stat { flex:0 0 100%; }
    .hero-cta .btn { max-width:100%; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="navbar-container">
        <a href="/" class="brand-logo">
            <div class="brand-logo-text">
                <span class="brand-logo-moto">MOTO</span><span class="brand-logo-spot">SPOT</span><span class="brand-logo-tm">TM</span>
            </div>
        </a>
        <div class="navbar-menu">
            <a href="#inicio"        class="nav-link">Inicio</a>
            <a href="#categorias"    class="nav-link">Categorias</a>
            <a href="#videos"        class="nav-link">Videos</a>
            <a href="#como-funciona" class="nav-link">Como funciona</a>
            <a href="/planes.php"    class="nav-link"><i class="fas fa-crown" style="color:#f59e0b;margin-right:.3rem"></i>Planes</a>
            <a href="/publicar-vehiculo.php" class="nav-link nav-cta">Publicar</a>
        </div>
        <button class="menu-toggle" id="menuToggle" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
</nav>

<!-- HERO CON VIDEO DE FONDO -->
<section class="hero" id="inicio">
    <div class="hero-video-wrap">
        <?php if (!empty($hero['url'])): ?>
            <video autoplay muted loop playsinline preload="auto"
                   poster="<?= htmlspecialchars($hero['thumb']) ?>">
                <source src="<?= htmlspecialchars($hero['url']) ?>" type="video/mp4">
            </video>
        <?php else: ?>
            <div class="hero-fallback"></div>
        <?php endif; ?>
    </div>
    <div class="hero-glow"></div>
    <div class="hero-glow hero-glow-2"></div>

    <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
        <div class="hero-badge">
            <i class="fas fa-star" style="color:#f59e0b"></i>
            La plataforma #1 en Argentina
        </div>
        <h1 class="hero-title">Encontra tu proximo<br><span>vehiculo ideal</span></h1>
        <p class="hero-subtitle">Miles de autos, motos y embarcaciones te esperan.<br>Compra y vende de forma segura con MotoSpot.</p>

        <form class="hero-search" action="/listado-vehiculos.php" method="GET" data-aos="fade-up" data-aos-delay="200">
            <select name="tipo" class="hero-search-select">
                <option value="">Todo</option>
                <option value="auto">Autos</option>
                <option value="moto">Motos</option>
                <option value="pickup">Pickups</option>
                <option value="suv">SUVs</option>
                <option value="electrico">Electricos</option>
                <option value="embarcacion">Embarcaciones</option>
            </select>
            <input type="text" name="q" class="hero-search-input" placeholder="Marca, modelo, anio...">
            <button type="submit" class="hero-search-btn"><i class="fas fa-search"></i> Buscar</button>
        </form>

        <div class="hero-cta" data-aos="fade-up" data-aos-delay="300">
            <a href="/listado-vehiculos.php" class="btn btn-primary btn-large btn-glow">
                <i class="fas fa-th-large"></i> Ver catalogo
            </a>
            <a href="/publicar-vehiculo.php" class="btn btn-outline btn-large">
                <i class="fas fa-plus-circle"></i> Vender mi vehiculo
            </a>
        </div>
    </div>

    <div class="hero-stats" data-aos="fade-up" data-aos-delay="500">
        <div class="hero-stat"><div class="hero-stat-number" data-target="15000">0+</div><div class="hero-stat-label">Vehiculos publicados</div></div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat"><div class="hero-stat-number" data-target="50000">0+</div><div class="hero-stat-label">Usuarios activos</div></div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat"><div class="hero-stat-number">24/7</div><div class="hero-stat-label">Soporte disponible</div></div>
    </div>

    <div class="scroll-indicator">
        <span>Scroll para descubrir</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- CATEGORIAS CON IMAGENES DE STOCK -->
<section class="categories-section" id="categorias">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Explorar</span>
            <h2 class="section-title">Que estas buscando?</h2>
            <p class="section-subtitle">Desde autos de lujo hasta embarcaciones. Encontra exactamente lo que necesitas.</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($featured as $i => $cat): ?>
            <a href="<?= htmlspecialchars($cat['link']) ?>" class="category-card"
               data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>"
               style="background: <?= htmlspecialchars($cat['gradient']) ?>; position: relative; overflow: hidden;">
                <div style="position: absolute; inset: 0; background: <?= htmlspecialchars($cat['texture']) ?>;"></div>
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, transparent 50%, rgba(0,0,0,0.4) 100%);"></div>
                <div style="position: absolute; top: 1rem; right: 1rem; width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.05); filter: blur(20px);"></div>
                <div class="category-card-overlay" style="z-index: 2;">
                    <div class="category-card-icon"><i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i></div>
                    <div class="category-card-label"><?= htmlspecialchars($cat['label']) ?></div>
                </div>
                <div class="category-card-arrow" style="z-index: 2;"><i class="fas fa-arrow-up-right"></i></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CANAL DE VIDEOS -->
<?php if (!empty($allVideos)): ?>
<section class="video-section" id="videos">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Canal de Videos</span>
            <h2 class="section-title">Vehiculos en accion</h2>
            <p class="section-subtitle">Los mejores autos y motos del mundo, en movimiento.</p>
        </div>
        <div class="video-grid">
            <?php foreach ($allVideos as $i => $vid): ?>
            <div class="video-card" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>"
                 onclick="openVideoModal('<?= htmlspecialchars($vid['url_medium'], ENT_QUOTES) ?>')">
                <?php if (!empty($vid['url_thumb'])): ?>
                    <img class="video-thumb" src="<?= htmlspecialchars($vid['url_thumb']) ?>"
                         alt="<?= htmlspecialchars($vid['tags']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="video-thumb-fallback"><i class="fas fa-video"></i></div>
                <?php endif; ?>
                <div class="video-play-btn"><i class="fas fa-play" style="margin-left:3px"></i></div>
                <?php if (!empty($vid['duration'])): ?>
                    <div class="video-duration"><?= gmdate('i:s', $vid['duration']) ?></div>
                <?php endif; ?>
                <div class="video-info">
                    <div class="video-tags"><i class="fas fa-tag" style="margin-right:.35rem;opacity:.5"></i><?= htmlspecialchars($vid['tags']) ?></div>
                    <div class="video-author"><i class="fas fa-user" style="margin-right:.35rem"></i><?= htmlspecialchars($vid['author']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Modal Video -->
<div class="video-modal" id="videoModal" onclick="closeVideoModal(event)">
    <div class="video-modal-inner">
        <button class="video-modal-close" onclick="closeVideoModal()"><i class="fas fa-times"></i></button>
        <video id="modalVideo" controls></video>
    </div>
</div>

<!-- BENEFICIOS -->
<section class="section" id="beneficios">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Beneficios</span>
            <h2 class="section-title">Por que elegir MotoSpot?</h2>
            <p class="section-subtitle">Las mejores herramientas para que compres o vendas rapido y seguro.</p>
        </div>
        <div class="features-grid">
            <?php
            $features = [
                ['icon'=>'fa-shield-alt',  'title'=>'Seguridad garantizada',   'text'=>'Verificamos todos los usuarios y ofrecemos consejos de seguridad para transacciones seguras.', 'delay'=>100],
                ['icon'=>'fa-camera',      'title'=>'Hasta 20 fotos',          'text'=>'Mostra tu vehiculo con fotos de alta calidad. Mas fotos = mas interesados.',                  'delay'=>200],
                ['icon'=>'fa-chart-line',  'title'=>'Maxima visibilidad',      'text'=>'Tu publicacion llega a miles de compradores potenciales en toda Argentina.',              'delay'=>300],
                ['icon'=>'fa-bolt',        'title'=>'Publicacion instantanea', 'text'=>'Crea tu anuncio en menos de 5 minutos y empeza a recibir consultas de inmediato.',           'delay'=>400],
                ['icon'=>'fa-mobile-alt',  'title'=>'100% responsive',         'text'=>'Accede desde cualquier dispositivo. Nuestra plataforma se adapta a tu pantalla.',            'delay'=>500],
                ['icon'=>'fa-headset',     'title'=>'Soporte 24/7',            'text'=>'Nuestro equipo esta disponible para ayudarte en todo momento.',                               'delay'=>600],
            ];
            foreach ($features as $f): ?>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="<?= $f['delay'] ?>">
                <div class="feature-icon"><i class="fas <?= $f['icon'] ?>"></i></div>
                <h3 class="feature-title"><?= $f['title'] ?></h3>
                <p class="feature-text"><?= $f['text'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COMO FUNCIONA -->
<section class="section presentation" id="como-funciona">
    <div class="container">
        <div class="presentation-grid">
            <div class="presentation-content" data-aos="fade-right" data-aos-duration="1000">
                <span class="section-label">Como funciona</span>
                <h2 class="presentation-title">Vender tu vehiculo nunca fue tan facil</h2>
                <p class="presentation-text">En MotoSpot simplificamos el proceso de compra y venta. Publica tu vehiculo en minutos, llega a miles de compradores potenciales y cierra el trato de forma segura.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="/publicar-vehiculo.php" class="btn btn-primary"><i class="fas fa-plus"></i> Publicar ahora</a>
                    <a href="#categorias" class="btn btn-secondary">Ver categorias</a>
                </div>
            </div>
            <div class="presentation-image" data-aos="fade-left" data-aos-duration="1000">
                <?php
                $presUrl = 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=600&h=400&fit=crop'; // default fallback
                try {
                    $presImg = stockSearch('car dealer showroom', 1, ['unsplash']);
                    if (!empty($presImg[0]['url_regular'])) {
                        $presUrl = $presImg[0]['url_regular'];
                    }
                } catch (Exception $e) {
                    logWarning('Error fetching presentation image', ['error' => $e->getMessage()]);
                }
                ?>
                <img src="<?= htmlspecialchars($presUrl) ?>" alt="Venta de vehiculos" style="width:100%;height:auto;border-radius:16px;">
            </div>
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="section cta-section" id="contacto">
    <div class="container">
        <div data-aos="fade-up">
            <h2 class="cta-title">Listo para empezar?</h2>
            <p class="cta-text">Unite a miles de personas que ya confian en MotoSpot. Encontra el vehiculo perfecto para vos.</p>
            <div class="cta-buttons">
                <a href="/listado-vehiculos.php" class="btn btn-primary btn-large btn-glow"><i class="fas fa-search"></i> Buscar vehiculos</a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div>
                <a href="/" class="brand-logo footer-logo">
                    <div class="brand-logo-text"><span class="brand-logo-moto">MOTO</span><span class="brand-logo-spot">SPOT</span><span class="brand-logo-tm">TM</span></div>
                </a>
                <p class="footer-description">La plataforma líder en Argentina para la compra y venta de vehículos y embarcaciones.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div>
                <h5 class="footer-title">Vehiculos</h5>
                <div class="footer-links">
                    <a href="/listado-vehiculos.php">Buscar vehiculos</a>
                    <a href="/publicar-vehiculo.php">Vender mi auto</a>
                    <a href="/listado-vehiculos.php?tipo=suv">SUVs</a>
                    <a href="/listado-vehiculos.php?tipo=sedan">Sedanes</a>
                    <a href="/listado-vehiculos.php?tipo=pickup">Pickups</a>
                </div>
            </div>
            <div>
                <h5 class="footer-title">Embarcaciones</h5>
                <div class="footer-links">
                    <a href="/embarcaciones.php">Ver embarcaciones</a>
                    <a href="/embarcaciones.php?tipo=lancha">Lanchas</a>
                    <a href="/embarcaciones.php?tipo=jet-ski">Jet Skis</a>
                    <a href="/embarcaciones.php?tipo=yate">Yates</a>
                    <a href="/publicar-embarcacion.php">Publicar</a>
                </div>
            </div>
            <div>
                <h5 class="footer-title">Soporte</h5>
                <div class="footer-links">
                    <a href="/planes.php">Planes</a>
                    <a href="#">Centro de ayuda</a>
                    <a href="#">Terminos y condiciones</a>
                    <a href="#">Politica de privacidad</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> MotoSpot. Todos los derechos reservados.</p>
            <p>Hecho con <i class="fas fa-heart" style="color:#ef4444"></i> en Argentina</p>
        </div>
    </div>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({duration:800,easing:'ease-out-cubic',once:true,offset:80});

const navbar=document.getElementById('navbar');
window.addEventListener('scroll',()=>navbar.classList.toggle('scrolled',window.scrollY>80));

document.getElementById('menuToggle').addEventListener('click',()=>{
    document.querySelector('.navbar-menu').classList.toggle('active');
});
// Cerrar menu al hacer click en un link
document.querySelectorAll('.navbar-menu .nav-link').forEach(l=>{
    l.addEventListener('click',()=>document.querySelector('.navbar-menu').classList.remove('active'));
});

document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',e=>{
        const t=document.querySelector(a.getAttribute('href'));
        if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth',block:'start'});}
    });
});

// Contador animado de stats
const counters=document.querySelectorAll('.hero-stat-number[data-target]');
const statsObs=new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
        if(!entry.isIntersecting)return;
        const el=entry.target,target=parseInt(el.dataset.target),dur=2000;
        let start=null;
        const step=ts=>{
            if(!start)start=ts;
            const prog=Math.min((ts-start)/dur,1);
            el.textContent=Math.floor(prog*target).toLocaleString('es-AR')+'+';
            if(prog<1)requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        statsObs.unobserve(el);
    });
},{threshold:0.5});
counters.forEach(c=>statsObs.observe(c));

// Lazy-load imagenes de categorias
document.querySelectorAll('img[data-src]').forEach(img=>{
    const obs=new IntersectionObserver(entries=>{
        entries.forEach(e=>{if(e.isIntersecting){img.src=img.dataset.src;obs.unobserve(img);}});
    },{rootMargin:'200px'});
    obs.observe(img);
});

// Modal de video
function openVideoModal(url){
    const modal=document.getElementById('videoModal');
    const video=document.getElementById('modalVideo');
    video.src=url;
    modal.classList.add('open');
    video.play();
}
function closeVideoModal(e){
    if(e&&e.target!==document.getElementById('videoModal')&&!e.target.closest('.video-modal-close'))return;
    const video=document.getElementById('modalVideo');
    video.pause();video.src='';
    document.getElementById('videoModal').classList.remove('open');
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeVideoModal();});
</script>
</body>
</html>
