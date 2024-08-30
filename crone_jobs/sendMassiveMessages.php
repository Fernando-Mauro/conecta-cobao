<?php
// cron_script.php

// Ejecutar el comando de Laravel
exec('php artisan queue:work database --queue=massiveMessages');