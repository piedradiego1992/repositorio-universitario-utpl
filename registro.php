<?php
session_start();
require_once 'config/conexion.php';
$msg = ""; $tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = trim($_POST['cedula']);
    $nombres = htmlspecialchars(trim($_POST['nombres']));
    $apellidos = htmlspecialchars(trim($_POST['apellidos']));
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];
    $pass_conf = $_POST['password_confirm'];
    $fecha_nac = $_POST['fecha_nacimiento'];

    if (!empty($cedula) && !empty($nombres) && !empty($apellidos) && !empty($correo) && !empty($pass) && !empty($fecha_nac)) {
        
        if (strlen($cedula) !== 10 || !ctype_digit($cedula)) {
            $msg = "La cédula debe contener exactamente 10 dígitos numéricos."; 
            $tipo_alerta = "error";
        } else if ($pass !== $pass_conf) {
            $msg = "Las contraseñas de validación no coinciden."; 
            $tipo_alerta = "error";
        } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,16}$/', $pass)) {
            $msg = "La contraseña no cumple con las políticas de seguridad."; 
            $tipo_alerta = "error";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $msg = "El formato de correo institucional no es válido."; 
            $tipo_alerta = "error";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? OR cedula = ?");
            $stmt->execute([$correo, $cedula]);

            if ($stmt->rowCount() == 0) {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $insert = $pdo->prepare("INSERT INTO usuarios (cedula, nombres, apellidos, correo, password, fecha_nacimiento) VALUES (?, ?, ?, ?, ?, ?)");
                $insert->execute([$cedula, $nombres, $apellidos, $correo, $hash, $fecha_nac]);
                $msg = "Registro exitoso al sistema UTPL. Ya puede iniciar sesión."; 
                $tipo_alerta = "exito";
            } else {
                $msg = "La cédula o el correo ya se encuentran registrados."; 
                $tipo_alerta = "error";
            }
        }
    } else { 
        $msg = "Todos los campos son obligatorios."; 
        $tipo_alerta = "error"; 
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Estudiantil - UTPL</title>
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
    <div class="contenedor">
        <h2>Crear Cuenta</h2>
        <p class="subtitulo">Regístrese en el sistema institucional.</p>
        
        <?php if(!empty($msg)): ?> 
            <div class="alerta alerta-<?= $tipo_alerta ?>"><?= $msg ?></div> 
        <?php endif; ?>
        
        <form method="POST" action="" id="formRegistro">
            <div class="grupo-input">
                <label>Cédula de Identidad</label>
                <input type="text" name="cedula" required minlength="10" maxlength="10" pattern="\d{10}" placeholder="Ej. 1104567890">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="grupo-input" style="flex: 1;">
                    <label>Nombres</label>
                    <input type="text" name="nombres" required placeholder="Ej. Carlos Alfredo">
                </div>
                <div class="grupo-input" style="flex: 1;">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" required placeholder="Ej. Mendoza Ruíz">
                </div>
            </div>

            <div class="grupo-input">
                <label>Correo Institucional</label>
                <input type="email" name="correo" required placeholder="usuario@utpl.edu.ec">
            </div>
            <div class="grupo-input">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" required>
            </div>
            
            <div class="grupo-input campo-password">
                <label>Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                <i class="fa-solid fa-eye btn-ojo" id="togglePassword"></i>
                <ul class="lista-requisitos">
                    <li id="reqLongitud">Debe tener de 8 a 16 caracteres</li>
                    <li id="reqMayuscula">Debe tener al menos una mayúscula</li>
                    <li id="reqNumero">Debe tener al menos un número</li>
                    <li id="reqEspecial">Debe tener al menos un carácter especial (@$!%*?&)</li>
                </ul>
            </div>
            
            <div class="grupo-input campo-password">
                <label>Confirmar Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" required placeholder="••••••••">
                <i class="fa-solid fa-eye btn-ojo" id="toggleConfirm"></i>
                <ul class="lista-requisitos">
                    <li id="reqCoincide">Las contraseñas deben coincidir</li>
                </ul>
            </div>
            
            <button type="submit">Finalizar Registro</button>
        </form>
        <p style="text-align:center; margin-top:1.5rem; font-size:0.9rem;">¿Ya tienes cuenta? <a href="index.php" style="color:var(--azul-utpl); font-weight:bold; text-decoration:none;">Inicia Sesión</a></p>
    </div>

    <script>
        const txtPass = document.getElementById('password');
        const txtConfirm = document.getElementById('password_confirm');
        const btnOjoPass = document.getElementById('togglePassword');
        const btnOjoConfirm = document.getElementById('toggleConfirm');
        
        // --- LOGICA REFORZADA CON POINTER EVENTS ---
        function vincularOjoEstricto(boton, input) {
            const mostrar = (e) => {
                e.preventDefault();
                input.setAttribute('type', 'text');
                boton.classList.remove('fa-eye');
                boton.classList.add('fa-eye-slash');
            };

            const ocultar = (e) => {
                e.preventDefault();
                input.setAttribute('type', 'password');
                boton.classList.remove('fa-eye-slash');
                boton.classList.add('fa-eye');
            };

            boton.addEventListener('pointerdown', mostrar);
            boton.addEventListener('pointerup', ocultar);
            boton.addEventListener('pointerleave', ocultar);
            boton.addEventListener('pointercancel', ocultar);
            boton.addEventListener('mouseup', ocultar);
            boton.addEventListener('mouseleave', ocultar);
        }

        vincularOjoEstricto(btnOjoPass, txtPass);
        vincularOjoEstricto(btnOjoConfirm, txtConfirm);

        // --- VALIDADOR EN TIEMPO REAL ---
        txtPass.addEventListener('input', function() {
            const valor = txtPass.value;
            if(valor.length >= 8 && valor.length <= 16) document.getElementById('reqLongitud').classList.add('cumplido');
            else document.getElementById('reqLongitud').classList.remove('cumplido');
            if(/[A-Z]/.test(valor)) document.getElementById('reqMayuscula').classList.add('cumplido');
            else document.getElementById('reqMayuscula').classList.remove('cumplido');
            if(/\d/.test(valor)) document.getElementById('reqNumero').classList.add('cumplido');
            else document.getElementById('reqNumero').classList.remove('cumplido');
            if(/[@$!%*?&]/.test(valor)) document.getElementById('reqEspecial').classList.add('cumplido');
            else document.getElementById('reqEspecial').classList.remove('cumplido');
            verificarCoincidencia();
        });

        txtConfirm.addEventListener('input', verificarCoincidencia);

        function verificarCoincidencia() {
            if(txtPass.value === txtConfirm.value && txtPass.value !== '') {
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