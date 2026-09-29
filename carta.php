<?php
require_once __DIR__.'/utilidades.php';
require __DIR__.'/base_datos.php';
$categorias=$pdo->query('SELECT id,nombre FROM categorias ORDER BY id')->fetchAll();
$id=filter_input(INPUT_GET,'categoria',FILTER_VALIDATE_INT) ?: 0;
$q=$pdo->prepare('SELECT m.* FROM vista_menu m JOIN productos p ON p.id=m.producto_id WHERE (?=0 OR p.categoria_id=?) ORDER BY p.categoria_id,m.producto_id');
$q->execute([$id,$id]); $catalogo=$q->fetchAll();
encabezado('Menú de El Umbral Encantado');
?>
<p>Platillos tradicionales, opciones veganas e infantiles, postres, pociones y tesoros para llevar. Precios en MXN.</p>
<form method="get" class="filtro"><label for="categoria">Explorar categoría</label><select id="categoria" name="categoria"><option value="0">Todas las categorías</option><?php foreach($categorias as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $id==$c['id']?'selected':'' ?>><?= e($c['nombre']) ?></option><?php endforeach ?></select><button>Ver menú</button></form>
<div class="scroll"><table><thead><tr><th>Producto</th><th>Descripción</th><th>Precio</th><th>Disponibilidad</th></tr></thead><tbody>
<?php foreach($catalogo as $item): ?><tr><td><strong><?= e($item['producto']) ?></strong><br><small><?= e($item['categoria']) ?></small>
<?php if ($item['imagen'] && is_file(__DIR__.'/'.$item['imagen'])): ?><br><img class="producto-img" src="<?= e($item['imagen']) ?>" alt="<?= e($item['producto']) ?>"><?php endif ?>
</td><td><?= e($item['descripcion']) ?><br><small><?php $tags=[]; foreach(['especialidad'=>'Especialidad','vegano'=>'Vegano','picante'=>'Ligeramente picante','infantil'=>'Infantil','contiene_alcohol'=>'Solo mayores de 18 años'] as $k=>$label) if($item[$k]) $tags[]=$label; echo e(implode(' · ',$tags)); ?></small></td><td class="precio">$<?= number_format((float)$item['precio'],2) ?></td><td><?= $item['stock']===null?'Disponibilidad por confirmar':((int)$item['stock']>0?e($item['stock']).' disponibles':'Agotado') ?></td></tr><?php endforeach ?>
<?php if(!$catalogo): ?><tr><td colspan="4">No hay productos en esta categoría.</td></tr><?php endif ?></tbody></table></div>
<p class="nota">Los platillos infantiles incluyen fruta y agua fresca pequeña. Informa al personal sobre alergias o restricciones alimentarias.</p>
<section class="form-container"><h2>Solicitar un pedido</h2><p>Solo se pueden pedir productos con existencias confirmadas. No se realizan cargos.</p>
<?php $disponibles=$pdo->query('SELECT * FROM vista_menu WHERE stock>0 ORDER BY producto_id')->fetchAll(); ?>
<?php if(!$disponibles): ?><p>Estamos confirmando las existencias. Puedes consultar el menú y reservar tu visita.</p><?php else: ?>
<form action="guardar_pedido.php" method="post"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<label for="nombre">Nombre</label><input id="nombre" name="nombre" maxlength="120" required>
<label for="correo">Correo electrónico</label><input id="correo" type="email" name="correo" maxlength="190" required>
<label for="telefono">Teléfono (opcional)</label><input id="telefono" name="telefono" maxlength="25">
<label for="variante">Producto</label><select id="variante" name="variante_id"><?php foreach($disponibles as $v): ?><option value="<?= (int)$v['variante_id'] ?>"><?= e($v['producto']) ?> — $<?= number_format((float)$v['precio'],2) ?></option><?php endforeach ?></select>
<label for="cantidad">Cantidad</label><input id="cantidad" type="number" name="cantidad" value="1" min="1" max="999" required>
<label><input type="checkbox" name="mayor_edad" value="1"> Soy mayor de 18 años (obligatorio para bebidas con alcohol; se verificará al entregar).</label>
<button>Enviar pedido</button></form><?php endif ?></section>
<?php pie(); ?>
