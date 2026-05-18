<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$msg = ""; $tipo_alerta = "";
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Guardar Datos Personales normalizados
    if (isset($_POST['accion_datos_personales'])) {
        $genero = htmlspecialchars(trim($_POST['genero']));
        $direccion = htmlspecialchars(trim($_POST['direccion']));
        $ciudad = htmlspecialchars(trim($_POST['ciudad']));
        $pais = htmlspecialchars(trim($_POST['pais']));
        
        $stmt = $pdo->prepare("UPDATE usuarios SET genero = ?, direccion = ?, ciudad = ?, pais = ? WHERE id = ?");
        $stmt->execute([$genero, $direccion, $ciudad, $pais, $usuario_id]);
        $msg = "Datos personales actualizados con éxito."; $tipo_alerta = "exito";
    }

    // 2. Cargar Foto
    if (isset($_POST['accion_foto'])) {
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
            $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['foto_perfil']['type'], $permitidos)) {
                $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
                $nombre_foto = 'avatar_' . $usuario_id . '_' . time() . '.' . $ext;
                
                if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], 'uploads/' . $nombre_foto)) {
                    $stmt_foto = $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
                    $stmt_foto->execute([$nombre_foto, $usuario_id]);
                    $msg = "Fotografía de perfil actualizada."; $tipo_alerta = "exito";
                }
            } else {
                $msg = "Formato de imagen no válido."; $tipo_alerta = "error";
            }
        }
    }

    // 3. Registrar Carrera del listado variado
    if (isset($_POST['accion_agregar_carrera'])) {
        $nueva_carrera = htmlspecialchars(trim($_POST['carrera_seleccionada']));
        
        if (!empty($nueva_carrera)) {
            $chk = $pdo->prepare("SELECT id FROM usuario_carreras WHERE usuario_id = ? AND carrera_nombre = ?");
            $chk->execute([$usuario_id, $nueva_carrera]);
            
            if ($chk->rowCount() == 0) {
                $ins_car = $pdo->prepare("INSERT INTO usuario_carreras (usuario_id, carrera_nombre) VALUES (?, ?)");
                $ins_car->execute([$usuario_id, $nueva_carrera]);
                $msg = "Inscripción exitosa en la carrera seleccionada."; $tipo_alerta = "exito";
            } else {
                $msg = "Ya se encuentra inscrito en esta titulación."; $tipo_alerta = "error";
            }
        }
    }

    // 4. Subir archivo a Carrera/Materia
    if (isset($_POST['accion_subir_archivo'])) {
        $carrera_ref = htmlspecialchars(trim($_POST['archivo_carrera']));
        $materia_ref = htmlspecialchars(trim($_POST['archivo_materia']));
        
        if (!empty($carrera_ref) && !empty($materia_ref) && isset($_FILES['guia_archivo']) && $_FILES['guia_archivo']['error'] == 0) {
            $nombre_original = $_FILES['guia_archivo']['name'];
            $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_servidor = 'guia_' . time() . '_' . uniqid() . '.' . $ext;
            $ruta_destino = 'uploads/' . $nombre_servidor;
            
            if (move_uploaded_file($_FILES['guia_archivo']['tmp_name'], $ruta_destino)) {
                $ins = $pdo->prepare("INSERT INTO archivos (usuario_id, carrera_nombre, materia, nombre_original, nombre_servidor, ruta) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->execute([$usuario_id, $carrera_ref, $materia_ref, $nombre_original, $nombre_servidor, $ruta_destino]);
                $msg = "Recurso académico catalogado con éxito."; $tipo_alerta = "exito";
            }
        } else {
            $msg = "Por favor complete los campos requeridos."; $tipo_alerta = "error";
        }
    }
}

// Consultas finales
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$edad_calculada = (new DateTime())->diff(new DateTime($usuario['fecha_nacimiento']))->y;

$stmt_mis_carreras = $pdo->prepare("SELECT carrera_nombre FROM usuario_carreras WHERE usuario_id = ? ORDER BY carrera_nombre ASC");
$stmt_mis_carreras->execute([$usuario_id]);
$mis_carreras = $stmt_mis_carreras->fetchAll(PDO::FETCH_ASSOC);

$stmt_archivos = $pdo->prepare("SELECT * FROM archivos WHERE usuario_id = ? ORDER BY carrera_nombre ASC, materia ASC");
$stmt_archivos->execute([$usuario_id]);
$todos_los_archivos = $stmt_archivos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal Académico UTPL</title>
    <link rel="stylesheet" href="css_estilos.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tabs-navegacion { display: flex; gap: 5px; margin-bottom: 1.5rem; border-bottom: 2px solid var(--gris-borde); }
        .tab-link { padding: 12px 20px; background: #E9ECEF; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; color: var(--texto-oscuro); transition: all 0.3s; width: auto; margin-top: 0; }
        .tab-link:hover { background: #DEE2E6; }
        .tab-link.activo { background: var(--azul-utpl); color: white; }
        .contenido-tab { display: none; padding: 10px 0; }
        .contenido-tab.activo { display: block; }
        .bloque-flex { display: flex; gap: 2rem; }
        .col-4 { width: 35%; }
        .col-8 { width: 65%; }
        select { width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--gris-borde); background-color: #FAFAFA; color: var(--texto-oscuro); font-size: 0.95rem; margin-bottom: 1.25rem; }
        .tarjeta-carrera-contenedor { background: var(--gris-fondo); padding: 20px; border-radius: 8px; margin-bottom: 1.5rem; border-top: 4px solid var(--amarillo-utpl); }
        .badge-carrera { display: inline-block; background: var(--azul-utpl); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin: 5px; }
    </style>
</head>
<body>
    <div class="contenedor-privado">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--gris-borde); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <div><span style="font-size: 1.6rem; font-weight: bold; color: var(--azul-utpl);">Portal Inteligente de Gestión Académica UTPL</span></div>
            <div>
                <a href="cambiar_password.php" style="color: var(--azul-utpl); margin-right: 15px; text-decoration: none; font-weight: 600; font-size: 0.9rem;"><i class="fa-solid fa-shield-halved"></i> Seguridad</a>
                <a href="logout.php" style="color: var(--error); text-decoration: none; font-weight: 600; font-size: 0.9rem;"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
            </div>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="alerta alerta-<?= $tipo_alerta ?>"><?= $msg ?></div>
        <?php endif; ?>

        <div class="tabs-navegacion">
            <button class="tab-link activo" onclick="abrirTab(event, 'tab-perfil')"><i class="fa-solid fa-user"></i> Datos Personales</button>
            <button class="tab-link" onclick="abrirTab(event, 'tab-carrera')"><i class="fa-solid fa-graduation-cap"></i> Mis Carreras</button>
            <button class="tab-link" onclick="abrirTab(event, 'tab-materias')"><i class="fa-solid fa-book"></i> Materias y Recursos</button>
        </div>

        <div id="tab-perfil" class="contenido-tab activo">
            <div class="bloque-flex">
                <div class="col-4" style="text-align: center; border-right: 1px solid var(--gris-borde); padding-right: 20px;">
                    <div class="avatar-contenedor">
                        <?php $ruta_foto = ($usuario['foto'] == 'default.png' || empty($usuario['foto'])) ? 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png' : 'uploads/' . $usuario['foto']; ?>
                        <img src="<?= $ruta_foto ?>" alt="Foto de Perfil" class="avatar-img">
                    </div>
                    <form method="POST" action="" enctype="multipart/form-data" style="margin-top: 10px;">
                        <label style="font-size: 0.8rem; text-align: left;">Actualizar foto:</label>
                        <input type="file" name="foto_perfil" accept="image/*" style="font-size:0.8rem; padding:5px; margin-bottom: 5px;" required>
                        <button type="submit" name="accion_foto" style="padding: 5px; font-size: 0.8rem;">Subir Imagen</button>
                    </form>
                </div>
                
                <div class="col-8">
                    <form method="POST" action="">
                        <h3 style="color: var(--azul-utpl); margin-bottom: 1rem;">Ficha de Matrícula</h3>
                        
                        <div style="display: flex; gap: 10px; margin-bottom: 0;">
                            <div class="grupo-input" style="flex: 1;"><label>Nombres</label><input type="text" value="<?= $usuario['nombres'] ?>" disabled style="background:#ECEFF1;"></div>
                            <div class="grupo-input" style="flex: 1;"><label>Apellidos</label><input type="text" value="<?= $usuario['apellidos'] ?>" disabled style="background:#ECEFF1;"></div>
                        </div>
                        
                        <div style="display: flex; gap:1rem; margin-bottom:0;">
                            <div class="grupo-input" style="flex:1;"><label>Cédula</label><input type="text" value="<?= $usuario['cedula'] ?>" disabled style="background:#ECEFF1;"></div>
                            <div class="grupo-input" style="flex:1;"><label>Edad</label><input type="text" value="<?= $edad_calculada ?> años" disabled style="background:#ECEFF1;"></div>
                        </div>

                        <div class="grupo-input">
                            <label>Género</label>
                            <select name="genero">
                                <option value="No especificado" <?= $usuario['genero'] == 'No especificado' ? 'selected' : '' ?>>Seleccione un género...</option>
                                <option value="Masculino" <?= $usuario['genero'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="Femenino" <?= $usuario['genero'] == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                             </select>
                        </div>

                        <div class="grupo-input"><label>Dirección Domicilio</label><input type="text" name="direccion" value="<?= htmlspecialchars($usuario['direccion'] != 'No especificada' ? $usuario['direccion'] : '') ?>" placeholder="Ej. Av. Universitaria y Azuay" required></div>
                        
                        <div style="display: flex; gap:1rem; margin-bottom:0;">
                            <div class="grupo-input" style="flex:1;"><label>Ciudad</label><input type="text" name="ciudad" value="<?= htmlspecialchars($usuario['ciudad'] != 'No especificada' ? $usuario['ciudad'] : '') ?>" placeholder="Ej. Loja" required></div>
                            <div class="grupo-input" style="flex:1;"><label>País</label><input type="text" name="pais" value="<?= htmlspecialchars($usuario['pais'] != 'No especificado' ? $usuario['pais'] : '') ?>" placeholder="Ej. Ecuador" required></div>
                        </div>

                        <button type="submit" name="accion_datos_personales">Guardar Cambios de Perfil</button>
                    </form>
                </div>
            </div>
        </div>

        <div id="tab-carrera" class="contenido-tab">
            <h3 style="color: var(--azul-utpl); margin-bottom: 0.5rem;">Inscripción de Carreras Universitarias</h3>
            <p style="font-size: 0.9rem; color:#555; margin-bottom: 1.5rem;">Seleccione e inscríbase en las distintas titulaciones académicas que ofrece la UTPL en sus diversas facultades.</p>
            
            <div class="tarjeta-carrera-contenedor">
                <span style="font-size: 0.85rem; font-weight: bold; color: var(--azul-utpl); display: block; margin-bottom: 10px;">Carreras en curso:</span>
                <div>
                    <?php if(count($mis_carreras) == 0): ?>
                        <span style="color:#888; font-style:italic; font-size:0.95rem;">No inscrito en ninguna carrera.</span>
                    <?php else: ?>
                        <?php foreach($mis_carreras as $c): ?>
                            <span class="badge-carrera"><i class="fa-solid fa-graduation-cap"></i> <?= htmlspecialchars($c['carrera_nombre']) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" action="" style="background: white; border: 1px solid var(--gris-borde); padding: 20px; border-radius: 8px;">
                <label>Seleccione una Titulación Académica Variada:</label>
                <select name="carrera_seleccionada" required>
                    <option value="">-- Oferta Académica UTPL Multi-Facultad --</option>
                    <option value="Ingeniería en Tecnologías de la Información">Ingeniería en Tecnologías de la Información</option>
                    <option value="Licenciatura en Contabilidad y Auditoría">Licenciatura en Contabilidad y Auditoría</option>
                    <option value="Licenciatura en Derecho / Jurisprudencia">Licenciatura en Derecho / Jurisprudencia</option>
                    <option value="Licenciatura en Administración de Empresas">Licenciatura en Administración de Empresas</option>
                    <option value="Medicina Humana">Medicina Humana</option>
                    <option value="Licenciatura en Psicología">Licenciatura en Psicología</option>
                    <option value="Ingeniería Civil">Ingeniería Civil</option>
                </select>
                <button type="submit" name="accion_agregar_carrera">Inscribirse en la Carrera</button>
            </form>
        </div>

        <div id="tab-materias" class="contenido-tab">
            <h3 style="color: var(--azul-utpl); margin-bottom: 1rem;">Repositorio de Asignaturas de Pregrado</h3>
            
            <div class="bloque-flex">
                <div class="col-4" style="background: var(--gris-fondo); padding: 15px; border-radius: 8px; height: fit-content;">
                    <h4 style="color: var(--azul-utpl); margin-bottom: 1rem;">Añadir Material</h4>
                    
                    <?php if(count($mis_carreras) == 0): ?>
                        <p style="font-size:0.85rem; color:var(--error); font-weight:bold;">⚠️ Inscríbase en una carrera antes de catalogar recursos.</p>
                    <?php else: ?>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="grupo-input">
                                <label>Vincular a Carrera</label>
                                <select name="archivo_carrera" required style="background:white; margin-bottom:0;">
                                    <?php foreach($mis_carreras as $c): ?>
                                        <option value="<?= htmlspecialchars($c['carrera_nombre']) ?>"><?= htmlspecialchars($c['carrera_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="grupo-input">
                                <label>Nombre de la Asignatura</label>
                                <input type="text" name="archivo_materia" placeholder="Ej. Auditoría Financiera I o Programación" required style="background:white;">
                            </div>
                            <div class="grupo-input">
                                <label>Seleccionar Documento</label>
                                <input type="file" name="guia_archivo" required style="background:white; padding:5px;">
                            </div>
                            <button type="submit" name="accion_subir_archivo" style="width:100%;">Subir al Repositorio</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="col-8">
                    <h4 style="color: var(--azul-utpl); margin-bottom: 1rem;">Estructura Académica de Archivos</h4>
                    
                    <?php if(count($todos_los_archivos) == 0): ?>
                        <p style="color: #888; font-style: italic; font-size: 0.95rem;">No se han registrado recursos por el momento.</p>
                    <?php else: ?>
                        <?php 
                        $arbol = [];
                        foreach ($todos_los_archivos as $arc) { $arbol[$arc['carrera_nombre']][$arc['materia']][] = $arc; }
                        foreach ($arbol as $nombre_car => $materias): ?>
                            <div style="margin-bottom: 2rem; border: 1px solid var(--gris-borde); border-radius: 8px; overflow:hidden;">
                                <div style="background: var(--azul-utpl); color: white; padding: 10px 15px; font-weight: bold; font-size: 1.05rem;">
                                    <i class="fa-solid fa-graduation-cap" style="color:var(--amarillo-utpl);"></i> Titulación: <?= htmlspecialchars($nombre_car) ?>
                                </div>
                                <div style="padding: 15px; background: white;">
                                    <?php foreach ($materias as $nombre_mat => $archivos_mat): ?>
                                        <div style="margin-bottom: 1.25rem; background: var(--gris-fondo); padding: 12px; border-radius: 6px;">
                                            <h5 style="color: var(--azul-utpl); font-size: 0.95rem; margin-bottom: 8px; font-weight: 700;">
                                                <i class="fa-solid fa-folder-open" style="color:#E2B100;"></i> Asignatura: <?= htmlspecialchars($nombre_mat) ?>
                                            </h5>
                                            <table style="width: 100%; border-collapse: collapse; background: white; font-size: 0.85rem;">
                                                <tbody>
                                                    <?php foreach ($archivos_mat as $f): ?>
                                                        <tr style="border-bottom: 1px solid #EEE;">
                                                            <td style="padding: 8px; color:#333;"><i class="fa-regular fa-file" style="color:var(--azul-utpl); margin-right:5px;"></i> <?= htmlspecialchars($f['nombre_original']) ?></td>
                                                            <td style="padding: 8px; text-align: right;">
                                                                <a href="<?= $f['ruta'] ?>" download style="color: var(--exito); text-decoration: none; font-weight: bold;"><i class="fa-solid fa-download"></i> Descargar</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirTab(evt, nombreTab) {
            const contenidos = document.getElementsByClassName("contenido-tab");
            for (let i = 0; i < contenidos.length; i++) { contenidos[i].className = contenidos[i].className.replace(" activo", ""); }
            const botones = document.getElementsByClassName("tab-link");
            for (let i = 0; i < botones.length; i++) { botones[i].className = botones[i].className.replace(" activo", ""); }
            document.getElementById(nombreTab).className += " activo";
            evt.currentTarget.className += " activo";
        }
    </script>
</body>
</html>