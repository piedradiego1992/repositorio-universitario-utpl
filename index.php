<?php
session_start();
require_once 'config/conexion.php';
$msg = ""; $tipo_alerta = "";

if (isset($_SESSION['usuario_id'])) {
    header("Location: perfil.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    if (!empty($correo) && !empty($pass)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($pass, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            header("Location: perfil.php");
            exit();
        } else {
            $msg = "Credenciales incorrectas o usuario no registrado."; $tipo_alerta = "error";
        }
    } else {
        $msg = "Por favor ingrese todos los campos obligatorios."; $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Académico - UTPL</title>
    <link rel="stylesheet" href="css_estilos.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .campo-password { position: relative; }
        .btn-ojo { position: absolute; right: 12px; top: 38px; cursor: pointer; color: #666; padding: 5px; z-index: 10; }
    </style>
</head>
<body>
    <div class="contenedor" style="margin-top: 12vh;">
        <h2>Portal UTPL</h2>
        <p class="subtitulo">Ingrese sus credenciales de acceso institucional.</p>
        
        <?php if(!empty($msg)): ?>
            <div class="alerta alerta-<?= $tipo_alerta ?>"><?= $msg ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="grupo-input">
                <label>Correo Institucional</label>
                <input type="email" name="correo" required placeholder="usuario@utpl.edu.ec">
            </div>
            
            <div class="grupo-input campo-password">
                <label>Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                <i class="fa-solid fa-eye btn-ojo" id="toggleLogin"></i>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        <p style="text-align:center; margin-top:1.5rem; font-size:0.9rem;">¿No tienes cuenta? <a href="registro.php" style="color:var(--azul-utpl); font-weight:bold; text-decoration:none;">Regístrate aquí</a></p>
    </div>

    <script>
        const txtPass = document.getElementById('password');
        const btnOjo = document.getElementById('toggleLogin');

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

        vincularOjoEstricto(btnOjo, txtPass);
    </script>
</body>
</html>