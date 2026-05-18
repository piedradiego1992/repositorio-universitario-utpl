# Repositorio Digital UTPL - Sistema de Gestión Académica

Este es un sistema web institucional desarrollado en **PHP** y **MySQL** enfocado en la gestión académica que permite la autenticación segura de estudiantes, el control de sesiones, la actualización de perfiles en tiempo real y el intercambio de recursos digitales o guías didácticas, aplicando rigurosamente la metodología *CRUD*.

---

1. Requisitos del Sistema
Para replicar y ejecutar esta plataforma de forma local, se requiere un entorno de desarrollo que cuente con:
* **Servidor Web Local:** XAMPP, MAMP, WampServer o Laragon.
* **Versión de PHP:** 8.0 o superior.
* **Motor de Base de Datos:** MySQL / MariaDB.
* **Extensión Activa:** Librería gráfica **GD** habilitada en PHP (para el procesamiento del módulo CAPTCHA).

---

2. Estructura del Proyecto
El sistema ha sido estructurado bajo una arquitectura limpia y modular de la siguiente manera:

```text
repositorio-utpl/
│
├── config/
│   └── conexion.php         # Gestión y enlace seguro a la Base de Datos con PDO
│
├── uploads/                 # Carpeta física donde se almacenan las guías subidas (Se crea vacía)
│
├── index.php                # Formulario de Login
├── registro.php             # Registro estudiantil, control de cédula y políticas de claves fuertes
├── perfil.php               # Zona privada (actualización de datos y descarga de recursos)
├── cambiar_password.php     # Gestión de seguridad interna e historial de contraseñas
├── logout.php               # Destrucción absoluta de las variables de sesión del servidor
├── captcha.php              # Generador nativo de tokens visuales aleatorios para el Login
├── css_estilos.php          # Hoja de estilos
└── script.sql               # Código de creación del modelo relacional de la Base de Datos


3. Pasos para la Instalación y Despliegue Local
Siga minuciosamente estas instrucciones para poner en funcionamiento el repositorio en su computadora:

3.1. Ubicación del Proyecto
Descargue o clone el repositorio de código y coloque la carpeta principal (repositorio-utpl) estrictamente dentro de la ruta raíz de su servidor web local:

En Windows (XAMPP): C:/xampp/htdocs/repositorio-utpl/

En macOS (XAMPP): /Applications/XAMPP/xamppfiles/htdocs/repositorio-utpl/

En macOS (MAMP): /Applications/MAMP/htdocs/repositorio-utpl/

Nota: Asegúrese de que exista la carpeta vacía llamada uploads dentro del directorio del proyecto con los permisos de escritura correctos para permitir la carga de archivos.

3.2. Activación de Servicios
Abra el panel de control de su software de servidor (como XAMPP) y encienda dando clic en Start a los módulos de:

Apache (Servidor Web)

MySQL (Servidor de Base de Datos)

3.3. Configuración de la Base de Datos
Abra su navegador web e ingrese a la herramienta de administración: http://localhost/phpmyadmin/.

Cree una nueva base de datos vacía con el nombre exacto de: sistema_universitario.

Seleccione la base de datos recién creada, diríjase a la pestaña Importar en el menú superior, seleccione el archivo script.sql localizado en la raíz del proyecto y haga clic en Continuar o Ejecutar.

3.4. Ajustes de Credenciales (Opcional)
Por defecto, la plataforma viene configurada para conectarse a un servidor local estándar (Usuario: root, Contraseña: vacía). Si su entorno de MySQL maneja parámetros distintos, puede editarlos rápidamente abriendo el archivo modular config/conexion.php desde su editor de código.

5. Ejecución en el Navegador
Con los servicios en marcha y la base de datos importada, abra una pestaña en su navegador e ingrese a la siguiente dirección URL para interactuar con la plataforma:
👉 http://localhost/repositorio-utpl/