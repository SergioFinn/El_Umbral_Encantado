<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly'=>true, 'cookie_samesite'=>'Lax']);
}
date_default_timezone_set('America/Mexico_City');
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

function volver(string $pagina, string $mensaje, bool $error=false): void {
    $_SESSION['aviso']=['texto'=>$mensaje,'error'=>$error];
    header('Location: '.$pagina, true, 303); exit;
}

function aviso(): void {
    if (isset($_SESSION['aviso'])) {
        echo '<p class="aviso" role="status">'.e($_SESSION['aviso']['texto']).'</p>';
        unset($_SESSION['aviso']);
    }
}

function post_texto(string $key, int $max, bool $required=true): string {
    $value=$_POST[$key] ?? '';
    if (!is_string($value)) throw new DomainException('Datos inválidos.');
    $value=trim($value);
    if (($required && $value==='') || mb_strlen($value)>$max) throw new DomainException('Revisa el campo '.$key.'.');
    return $value;
}

function post_entero(string $key, int $min, int $max): int {
    $value=$_POST[$key] ?? null;
    if (!is_string($value) || filter_var($value,FILTER_VALIDATE_INT)===false || (int)$value<$min || (int)$value>$max)
        throw new DomainException('Revisa el campo '.$key.'.');
    return (int)$value;
}

function validar_post(): void {
    if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); header('Allow: POST'); exit('Utiliza el formulario.'); }
    $token=$_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'],$token)) {
        http_response_code(403); exit('El formulario expiró o ya fue enviado. Vuelve a abrirlo.');
    }
}

function cliente(PDO $pdo): int {
    $nombre=post_texto('nombre',120); $correo=post_texto('correo',190); $telefono=post_texto('telefono',25,false);
    if (!filter_var($correo,FILTER_VALIDATE_EMAIL)) throw new DomainException('Escribe un correo válido.');
    $q=$pdo->prepare('INSERT INTO clientes(nombre,correo,telefono) VALUES(?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
    $q->execute([$nombre,$correo,$telefono ?: null]);
    return (int)$pdo->lastInsertId();
}

function encabezado(string $titulo): void {
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>' . e($titulo) . ' - El Umbral Encantado</title>
    <style>
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:#1c1a17;color:#f5f5dc;font:18px/1.7 Georgia,serif}a{color:#e7c760}a:focus-visible,button:focus-visible{outline:3px solid white;outline-offset:5px}nav{display:flex;flex-wrap:wrap;gap:12px 26px;justify-content:center;padding:20px;background:#2b2620;border-bottom:1px solid #62532c}nav a{text-decoration:none;font-size:17px}nav a:hover,nav a[aria-current]{text-decoration:underline;text-underline-offset:6px}h1,h2,h3{font-weight:normal;line-height:1.15;color:#e7c760}h1{font-size:clamp(2.8rem,5.8vw,5.5rem);margin:24px 0}h2{font-size:clamp(2rem,3.5vw,3rem);margin:0 0 22px}h3{font-size:1.55rem;margin:0 0 15px}.hero{max-width:1400px;margin:auto;display:grid;grid-template-columns:1fr 1.05fr;align-items:center;min-height:690px;padding:55px 40px;gap:48px;background:radial-gradient(ellipse at 80% 50%,#43351c 0,transparent 65%)}.logo{width:min(440px,100%);height:auto;display:block}.intro{max-width:520px;color:#d9cfbb}.imagen{width:100%;display:block;border:1px solid #a78a32;border-radius:200px 200px 8px 8px;box-shadow:0 25px 70px #0008}.pie-imagen{text-align:center;font-size:15px;margin:12px 0}.botones{display:flex;flex-wrap:wrap;gap:15px;margin-top:30px}.boton{display:inline-block;background:#d4af37;color:#17130d;padding:12px 24px;border:1px solid #d4af37;border-radius:4px;text-decoration:none}.secundario{background:transparent;color:#e7c760}.franja{border-block:1px solid #645326;background:#282218;padding:12px 20px;display:flex;align-items:center;gap:15px}.franja marquee{flex:1;font-style:italic}.pausa{background:transparent;color:#e7c760;border:1px solid #917935;padding:7px 12px;cursor:pointer;font:inherit;font-size:14px}.seccion{max-width:1180px;margin:auto;padding:65px 28px}.subtitulo{max-width:720px;color:#d9cfbb}.tres{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;margin-top:35px}.tarjeta{padding:26px 0;border-top:2px solid #a78a32}.numero{color:#b49a58;font-size:14px;letter-spacing:2px;display:block;margin-bottom:20px}.tarjeta p{color:#d9cfbb}.zonas{display:grid;grid-template-columns:1fr 1fr;gap:0 50px;margin:32px 0}.zona{border-bottom:1px solid #534a37;padding:22px 0}.zona h3{font-size:1.25rem;margin-bottom:9px}.zona p{margin:0;font-size:16px;color:#d9cfbb}.fondo{background:#15130f;border-block:1px solid #4b4028}.dos{display:grid;grid-template-columns:1fr 1fr;gap:55px}.dos p{font-size:17px;color:#d9cfbb}.horarios{list-style:none;padding:0}.horarios li{padding:10px 0;border-bottom:1px solid #4b4028}.cierre{text-align:center;max-width:780px}.cierre .botones{justify-content:center}footer{border-top:1px solid #756127;text-align:center;padding:26px;font-size:15px}.saltar{position:absolute;left:-9999px}.saltar:focus{left:10px;top:10px;background:#000;padding:12px;z-index:10}
        @media(max-width:850px){.hero{grid-template-columns:1fr;padding:38px 24px;gap:32px}.hero figure{max-width:650px;margin:0 auto}.hero h1{max-width:650px}.tres{grid-template-columns:1fr}.dos{grid-template-columns:1fr;gap:30px}.seccion{padding:44px 24px}}
        @media(max-width:520px){.zonas{grid-template-columns:1fr}.hero{min-height:auto}.boton{width:100%;text-align:center}nav{gap:10px 16px}.franja{padding:10px}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
    </style>
    <link rel="stylesheet" href="tema_castillo.css">
</head>
<body id="arriba">
    <nav aria-label="Navegación principal">
        <a href="portada.html">Inicio</a>
        <a href="carta.php">Nuestro Menú</a>
        <a href="historia.html">Sobre Nosotros</a>
        <a href="reservaciones.php">Reservaciones</a>
        <a href="seguridad_de_pagos.html">Pagos Seguros</a>
    </nav>
    <main id="contenido" style="padding: 30px;">
        <h1>'.e($titulo).'</h1>';
    
    aviso();
}

function pie(): void {
    echo '</main>
    <footer id="abajo">El Umbral Encantado · Proyecto escolar · Sin cobros en línea<br><a href="#arriba">Volver arriba</a></footer>
</body>
</html>';
}
