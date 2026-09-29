<?php
require_once __DIR__.'/utilidades.php'; validar_post(); require __DIR__.'/base_datos.php';
try {
    $variante=post_entero('variante_id',1,2147483647); $cantidad=post_entero('cantidad',1,999);
    $pdo->beginTransaction();
    $q=$pdo->prepare('SELECT p.nombre,v.nombre AS variante,v.precio,v.stock,p.contiene_alcohol FROM variantes v JOIN productos p ON p.id=v.producto_id WHERE v.id=? AND v.activo=1 AND p.activo=1 FOR UPDATE');
    $q->execute([$variante]); $item=$q->fetch();
    if(!$item) throw new DomainException('Producto no disponible.');
    if($item['stock']===null) throw new DomainException('La disponibilidad de este producto todavía no está confirmada.');
    if((int)$item['stock']<$cantidad) throw new DomainException('No hay suficientes existencias.');
    if($item['contiene_alcohol'] && ($_POST['mayor_edad']??'')!=='1') throw new DomainException('Las bebidas con alcohol son exclusivas para adultos.');
    $cliente=cliente($pdo);
    $q=$pdo->prepare('INSERT INTO pedidos(cliente_id,notas) VALUES (?,?)'); $q->execute([$cliente,'Solicitud web, sin cobro en línea.']); $pedido=$pdo->lastInsertId();
    $q=$pdo->prepare('INSERT INTO detalle_pedido(pedido_id,variante_id,cantidad,nombre_producto,precio_unitario) VALUES(?,?,?,?,?)');
    $q->execute([$pedido,$variante,$cantidad,$item['nombre'].' - '.$item['variante'],$item['precio']]);
    $q=$pdo->prepare('UPDATE variantes SET stock=stock-? WHERE id=? AND stock>=?'); $q->execute([$cantidad,$variante,$cantidad]);
    if($q->rowCount()!==1) throw new DomainException('La disponibilidad cambió. Intenta nuevamente.');
    $pdo->commit(); $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    volver('carta.php','Pedido #'.$pedido.' guardado por $'.number_format($cantidad*(float)$item['precio'],2).' MXN. Pago pendiente; no se ha realizado ningún cargo.');
} catch(DomainException $e) {
    if($pdo->inTransaction()) $pdo->rollBack(); volver('carta.php',$e->getMessage(),true);
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack(); error_log('Error de pedido: '.$e->getMessage()); volver('carta.php','No se pudo guardar el pedido. Intenta nuevamente.',true);
}
