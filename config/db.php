<?php
    date_default_timezone_set('America/Mexico_City');
    function conectar(): PDO {
        return new PDO (
            'pgsql:host=localhost;port=5432;dbname=cafeteria',
            'cafeteria_app',
            'Prueba', // Contraseña del usuario de la BDD.
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
?>