<?php
header("Content-type: text/css");
?>
:root {
    --azul-utpl: #002F6C;
    --amarillo-utpl: #F2C100;
    --gris-fondo: #F4F6F9;
    --gris-borde: #DCDCDC;
    --texto-oscuro: #1A1A1A;
    --texto-mutado: #555555;
    --blanco: #FFFFFF;
    --exito: #28a745;
    --error: #dc3545;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: var(--gris-fondo);
    color: var(--texto-oscuro);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.contenedor {
    background-color: var(--blanco);
    width: 100%;
    max-width: 450px;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 47, 108, 0.08);
    border-top: 6px solid var(--azul-utpl);
}

/* Contenedor extendido para la zona privada */
.contenedor-privado {
    background-color: var(--blanco);
    width: 100%;
    max-width: 850px;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 47, 108, 0.08);
    border-top: 6px solid var(--azul-utpl);
}

h2 {
    color: var(--azul-utpl);
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-align: center;
}

p.subtitulo {
    color: var(--texto-mutated);
    font-size: 0.95rem;
    text-align: center;
    margin-bottom: 2rem;
}

.grupo-input {
    margin-bottom: 1.25rem;
}

label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--azul-utpl);
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="date"],
input[type="file"] {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--gris-borde);
    border-radius: 6px;
    background-color: #FAFAFA;
    color: var(--texto-oscuro);
    font-size: 0.95rem;
    transition: all 0.3s;
}

input:focus {
    outline: none;
    border-color: var(--azul-utpl);
    box-shadow: 0 0 0 3px rgba(0, 47, 108, 0.1.5);
    background-color: var(--blanco);
}

button, input[type="submit"] {
    width: 100%;
    padding: 0.85rem;
    background-color: var(--azul-utpl);
    color: var(--blanco);
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 0.5rem;
}

button:hover, input[type="submit"]:hover {
    background-color: #001F47;
}

.alerta {
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
    font-weight: 500;
}

.alerta-exito {
    background-color: #E6F4EA;
    color: var(--exito);
    border: 1px solid #C3E6CB;
}

.alerta-error {
    background-color: #FCE8E6;
    color: var(--error);
    border: 1px solid #F5C6CB;
}

/* Estilos de la Foto de Perfil */
.avatar-contenedor {
    text-align: center;
    margin-bottom: 1.5rem;
}

.avatar-img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--amarillo-utpl);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* Diseño del Panel de Datos en el Perfil */
.grid-perfil {
    display: table;
    width: 100%;
    margin-top: 1.5rem;
}

.fila-perfil {
    display: table-row;
}

.celda-perfil {
    display: table-cell;
    padding: 10px;
    border-bottom: 1px solid #EEE;
    font-size: 0.95rem;
}

.celda-label {
    font-weight: bold;
    color: var(--azul-utpl);
    width: 35%;
}

.badge-edad {
    background-color: var(--amarillo-utpl);
    color: var(--azul-utpl);
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 0.85rem;
    margin-left: 8px;
}