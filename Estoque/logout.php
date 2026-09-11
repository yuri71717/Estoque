<?php
// ============================================================
// LOGOUT DO SISTEMA (Encerra a sessão e volta para o login)
// ============================================================
session_start();
session_destroy();
header("Location: login.php");
exit;
?>
