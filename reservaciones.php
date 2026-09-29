<?php
require_once __DIR__.'/utilidades.php'; require __DIR__.'/base_datos.php';
$zonas=$pdo->query('SELECT * FROM zonas ORDER BY id')->fetchAll(); encabezado('Reserva tu aventura');
?>
<img class="mapa" src="img/mapa.png" alt="Mapa de las seis zonas de El Umbral Encantado">
<details><summary>Capacidad de las zonas del castillo</summary><ul><?php foreach($zonas as $z): ?><li><?= e($z['nombre']) ?>: <?= (int)$z['capacidad'] ?> personas<?= $z['solo_adultos']?' · Solo adultos':'' ?></li><?php endforeach ?></ul></details>
<p>Martes a jueves: 13:00–22:00. Viernes y sábado: 13:00–00:00. Domingo: 12:00–21:00. Lunes cerrado.</p>
<div class="form-container"><form action="procesar_reservaciones.php" method="post"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<label for="nombre">Nombre completo</label><input id="nombre" name="nombre" maxlength="120" required>
<label for="correo">Correo electrónico</label><input id="correo" name="correo" type="email" maxlength="190" required>
<label for="telefono">Teléfono</label><input id="telefono" name="telefono" maxlength="25" required>
<label for="tipo">Tipo de reservación</label><select id="tipo" name="tipo"><option value="comida_cena">Comida o cena</option><option value="cumpleanos">Cumpleaños</option><option value="celebracion_especial">Celebración especial</option><option value="evento_privado">Evento privado</option></select>
<label for="zona">Zona de preferencia</label><select id="zona" name="zona_id"><option value="0">Deseo recibir una recomendación</option><?php foreach($zonas as $z): ?><option value="<?= (int)$z['id'] ?>"><?= e($z['nombre']) ?><?= $z['solo_adultos']?' (solo adultos)':'' ?></option><?php endforeach ?></select>
<div class="columnas"><div><label for="fecha">Fecha</label><input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required></div><div><label for="hora">Hora</label><input type="time" id="hora" name="hora" required></div><div><label for="adultos">Adultos</label><input type="number" id="adultos" name="adultos" min="1" max="600" value="2" required></div><div><label for="ninos">Niñas y niños</label><input type="number" id="ninos" name="ninos" min="0" max="599" value="0" required></div></div>
<label for="paquete">Paquete solicitado (opcional)</label><input id="paquete" name="paquete_evento" maxlength="150">
<label for="motivo">Motivo de la celebración (opcional)</label><input id="motivo" name="motivo" maxlength="255">
<label for="restricciones">Alergias o restricciones alimentarias (opcional)</label><textarea id="restricciones" name="restricciones_alimentarias" maxlength="2000"></textarea>
<label for="comentarios">Comentarios (opcional)</label><textarea id="comentarios" name="comentarios" maxlength="500"></textarea>
<p>Las solicitudes están sujetas a disponibilidad y revisión. Las celebraciones y grupos de más de ocho personas requieren un anticipo del 30 % de la cotización. Esta página no cobra anticipos ni envía correos automáticamente.</p>
<details><summary>Políticas y uso de datos</summary><p>Los datos se usarán para gestionar esta solicitud y contactar contigo. No se publicarán. Las promociones son opcionales. Las reservaciones regulares pueden modificarse o cancelarse con seis horas de anticipación; eventos especiales, con 72 horas. Tolerancia: 15 minutos. La Torre del Dragón es exclusiva para adultos. Una solicitud pendiente no garantiza una mesa.</p></details>
<label><input type="checkbox" name="datos_revisados" value="1" required> He revisado mis datos.</label>
<label><input type="checkbox" name="acepta_politicas" value="1" required> Acepto las políticas y el uso de mis datos para gestionar la reservación.</label>
<label><input type="checkbox" name="acepta_promociones" value="1"> Deseo recibir promociones (opcional).</label>
<button>Enviar solicitud de reservación</button></form></div><?php pie(); ?>
