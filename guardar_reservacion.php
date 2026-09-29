<?php
require_once __DIR__.'/utilidades.php'; validar_post(); require __DIR__.'/base_datos.php';
try {
    $tipo=post_texto('tipo',30);
    if(!in_array($tipo,['comida_cena','cumpleanos','celebracion_especial','evento_privado'],true)) throw new DomainException('Tipo de reservación inválido.');
    $zona=post_entero('zona_id',0,2147483647); $adultos=post_entero('adultos',1,600); $ninos=post_entero('ninos',0,599);
    if($adultos+$ninos>600) throw new DomainException('El máximo total es 600 personas.');
    $fecha=post_texto('fecha',10); $hora=post_texto('hora',5);
    $visita=DateTimeImmutable::createFromFormat('!Y-m-d H:i',$fecha.' '.$hora);
    if(!$visita || $visita->format('Y-m-d H:i')!==$fecha.' '.$hora || $visita<=new DateTimeImmutable()) throw new DomainException('Elige una fecha y hora futuras válidas.');
    $dia=(int)$visita->format('N'); $minutos=(int)$visita->format('H')*60+(int)$visita->format('i');
    $abre=$dia===7?720:780; $cierra=in_array($dia,[5,6])?1440:($dia===7?1260:1320);
    if($dia===1 || $minutos<$abre || $minutos>=$cierra) throw new DomainException('El horario elegido está fuera del horario de apertura.');
    if(($_POST['datos_revisados']??'')!=='1' || ($_POST['acepta_politicas']??'')!=='1') throw new DomainException('Revisa los datos y acepta las políticas.');
    post_texto('telefono',25);
    $paquete=post_texto('paquete_evento',150,false); $motivo=post_texto('motivo',255,false);
    $restricciones=post_texto('restricciones_alimentarias',2000,false); $comentarios=post_texto('comentarios',500,false);
    $pdo->beginTransaction();
    if($zona) {
        $q=$pdo->prepare('SELECT * FROM zonas WHERE id=?'); $q->execute([$zona]); $z=$q->fetch();
        if(!$z) throw new DomainException('Zona inválida.');
        if($adultos+$ninos>(int)$z['capacidad']) throw new DomainException('La zona admite hasta '.$z['capacidad'].' personas.');
        if($z['solo_adultos'] && $ninos>0) throw new DomainException('La Torre del Dragón es exclusiva para adultos.');
    }
    $cliente=cliente($pdo);
    $q=$pdo->prepare('INSERT INTO reservaciones(cliente_id,zona_id,tipo,fecha,hora,adultos,ninos,paquete_evento,motivo,restricciones_alimentarias,comentarios,datos_revisados,acepta_politicas,acepta_promociones) VALUES (?,?,?,?,?,?,?,?,?,?,?,1,1,?)');
    $q->execute([$cliente,$zona?:null,$tipo,$fecha,$hora,$adultos,$ninos,$paquete?:null,$motivo?:null,$restricciones?:null,$comentarios?:null,(int)(($_POST['acepta_promociones']??'')==='1')]);
    $id=$pdo->lastInsertId(); $pdo->commit();
    $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    volver('reservaciones.php','Solicitud #'.$id.' guardada. Está pendiente de revisión; todavía no confirma una mesa ni se ha enviado un correo.');
} catch(DomainException $e) {
    if($pdo->inTransaction()) $pdo->rollBack(); volver('reservaciones.php',$e->getMessage(),true);
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack(); error_log('Error de reservación: '.$e->getMessage()); volver('reservaciones.php','No se pudo guardar la solicitud. Intenta nuevamente.',true);
}
