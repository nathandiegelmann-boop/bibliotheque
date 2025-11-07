<?php
session_start();
//detruire toutes les variable session
session_destroy();
//detruire une session necesite toujour une redirection 
header('Location:/bibliotheque/index.php?logout=success');
exit;