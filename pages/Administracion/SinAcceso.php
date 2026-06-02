<?php require_once __DIR__ . '/../../includes/auth.php'; requireAuth(); ?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Sin Acceso | DATPOS</title>
<style>body{font-family:Inter,Arial,sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;background:#f5f5f5;margin:0}
.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1)}
h2{color:#d32f2f}a{display:inline-block;margin-top:15px;padding:10px 30px;background:#046bb4;color:#fff;border-radius:25px;text-decoration:none}</style></head>
<body><div class="box"><h2><i class="fa fa-lock"></i> Sin Acceso</h2><p style="color:#666">No tiene permisos para acceder a este módulo.</p>
<a href="/pages/Interfaces/Home.php">Volver al Inicio</a></div></body></html>
