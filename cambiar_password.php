<?php
session_start();
require_once 'config/conexion.php';

// Muro de seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$msg = ""; $tipo_alerta = "";
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass_actual = $_POST['password_actual'];
    $pass_nueva = $_POST['password_nueva'];
    $pass_conf = $_POST['password_confirm'];

    if (!empty($pass_actual) && !empty($pass_nueva) && !empty($pass_conf)) {
        
        // Verificar la contraseña actual primero
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($pass_actual, $usuario['password'])) {
            
            // Validar políticas de seguridad de la nueva contraseña en el Backend
            if ($pass_nueva !== $pass_conf) {
                $msg = "La confirmación no coincide con la nueva contraseña."; $tipo_alerta = "error";
            } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,16}$/', $pass_nueva)) {
                $msg = "La nueva contraseña no cumple con los requisitos de seguridad exigidos."; $tipo_alerta = "error";
            } else {
                // Actualizar en la base de datos con hash seguro
                $nuevo_hash = password_hash($pass_nueva, PASSWORD_BCRYPT);
                $update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $update->execute([$nuevo_hash, $usuario_id]);
                
                $msg = "Contraseña institucional actualizada correctamente."; $tipo_alerta = "exito";
            }
        } else {
            $msg = "La contraseña actual ingresada es incorrecta."; $tipo_alerta = "error";
        }
    } else {
        $msg = "Todos los campos son obligatorios."; $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguridad UTPL - Actualizar Credenciales</title>
    <link rel="stylesheet" href="css_estilos.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .campo-password { position: relative; }
        .btn-ojo { position: absolute; right: 12px; top: 38px; cursor: pointer; color: #666; padding: 5px; z-index: 10; }
        .lista-requisitos { margin-top: 0.5rem; padding-left: 1.2rem; font-size: 0.82rem; list-style: none; }
        .lista-requisitos li { color: #dc3545; margin-bottom: 0.25rem; transition: color 0.3s; }
        .lista-requisitos li::before { content: "✕ "; margin-right: 5px; }
        .lista-requisitos li.cumplido { color: #28a745; }
        .lista-requisitos li.cumplido::before { content: "✓ "; }
    </style>
</head>
<body>
    <div class="contenedor" style="margin-top: 5vh;">
        <h2>Actualizar Contraseña</h2>
        <p class="subtitulo">Gestión de seguridad de su cuenta universitaria.</p>
        
        <?php if(!empty($msg)): ?> 
            <div class="alerta alerta-<?= $tipo_alerta ?>"><?= $msg ?></div> 
        <?php endif; ?>
        
        <form method="POST" action="" id="formPassword">
            <div class="grupo-input campo-password">
                <label>Contraseña Actual</label>
                <input type="password" id="password_actual" name="password_actual" required placeholder="••••••••">
                <i class="fa-solid fa-eye btn-ojo" id="toggleActual"></i>
            </div>
            
            <hr style="border: 0; border-top: 1px solid var(--gris-borde); margin: 1.5rem 0;">

            <div class="grupo-input campo-password">
                <label>Nueva Contraseña</label>
                <input type="password" id="password_nueva" name="password_nueva" required placeholder="••••••••">
                <i class="fa-solid fa-eye btn-ojo" id="toggleNueva"></i>
                <ul class="lista-requisitos">
                    <li id="reqLongitud">Debe tener de 8 a 16 caracteres</li>
                    <li id="reqMayuscula">Debe tener al menos una mayúscula</li>
                    <li id="reqNumero">Debe tener al menos un número</li>
                    <li id="reqEspecial">Debe tener al menos un carácter especial (@$!%*?&)</li>
                </ul>
            </div>
            
            <div class="grupo-input campo-password">
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" required placeholder="••••••••">
                <i class="fa-solid fa-eye btn-ojo" id="toggleConfirm"></i>
                <ul class="lista-requisitos">
                    <li id="reqCoincide">Las contraseñas deben coincidir</li>
                </ul>
            </div>
            
            <button type="submit">Actualizar Contraseña</button>
        </form>
        
        <p style="text-align:center; margin-top:1.5rem; font-size:0.9rem;">
            <a href="perfil.php" style="color:var(--azul-utpl); font-weight:bold; text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Portal Académico
            </a>
        </p>
    </div>

<script>
    // Elementos de los Inputs
    const txtActual = document.getElementById('password_actual');
    const txtNueva = document.getElementById('password_nueva');
    const txtConfirm = document.getElementById('password_confirm');
    
    // Elementos de los Botones Ojo
    const btnOjoActual = document.getElementById('toggleActual');
    const btnOjoNueva = document.getElementById('toggleNueva');
    const btnOjoConfirm = document.getElementById('toggleConfirm');
    
    // --- FUNCIÓN ULTRA ESTRICTA PARA MOSTRAR/OCULTAR AL PRESIONAR ---
    function vincularOjoEstricto(boton, input) {
        // Función para mostrar la contraseña
        const mostrar = (e) => {
            e.preventDefault(); // Evita comportamientos raros en móviles
            input.setAttribute('type', 'text');
            boton.classList.remove('fa-eye');
            boton.classList.add('fa-eye-slash');
        };

        // Función para ocultar la contraseña
        const ocultar = () => {
            input.setAttribute('type', 'password');
            boton.classList.remove('fa-eye-slash');
            boton.classList.add('fa-eye');
        };

        // Eventos para Computadora (Mouse)
        boton.addEventListener('mousedown', mostrar);
        boton.addEventListener('mouseup', ocultar);
        boton.addEventListener('mouseleave', ocultar); // Si sacas el mouse del ojo, se oculta

        // Eventos para Teléfonos/Tablets (Pantalla Táctil)
        boton.addEventListener('touchstart', mostrar, { passive: false });
        boton.addEventListener('touchend', ocultar);
        
        // Evento de seguridad global por si acaso
        boton.addEventListener('pointerleave', ocultar);
    }

    // Aplicar la lógica estricta a los tres ojos
    vincularOjoEstricto(btnOjoActual, txtActual);
    vincularOjoEstricto(btnOjoNueva, txtNueva);
    vincularOjoEstricto(btnOjoConfirm, txtConfirm);

    // --- VALIDADOR DE REQUISITOS EN TIEMPO REAL ---
    txtNueva.addEventListener('input', function() {
        const valor = txtNueva.value;
        
        // Longitud 8-16
        if(valor.length >= 8 && valor.length <= 16) document.getElementById('reqLongitud').classList.add('cumplido');
        else document.getElementById('reqLongitud').classList.remove('cumplido');
        
        // Mayúscula
        if(/[A-Z]/.test(valor)) document.getElementById('reqMayuscula').classList.add('cumplido');
        else document.getElementById('reqMayuscula').classList.remove('cumplido');
        
        // Número
        if(/\d/.test(valor)) document.getElementById('reqNumero').classList.add('cumplido');
        else document.getElementById('reqNumero').classList.remove('cumplido');
        
        // Carácter Especial
        if(/[@$!%*?&]/.test(valor)) document.getElementById('reqEspecial').classList.add('cumplido');
        else document.getElementById('reqEspecial').classList.remove('cumplido');
        
        verificarCoincidencia();
    });

    txtConfirm.addEventListener('input', verificarCoincidencia);

    function verificarCoincidencia() {
        if(txtNueva.value === txtConfirm.value && txtNueva.value !== '') {
            document.getElementById('reqCoincide').classList.add('cumplido');
            document.getElementById('reqCoincide').style.color = '#28a745';
        } else {
            document.getElementById('reqCoincide').classList.remove('cumplido');
            document.getElementById('reqCoincide').style.color = '#dc3545';
        }
    }
</script>
</body>
</html>