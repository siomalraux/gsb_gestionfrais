<?php
	/*
	 * Programme d'actualisation des lignes des tables,  
	 * cette mise à jour peut prendre plusieurs minutes...
	 */
	require_once("include/fct.inc.php");
	require_once("include/class.pdogsb.inc.php");

	$pdo = PdoGsb::getPdoGsb();

	set_time_limit(0);
	$pdo->creeLesFichesFrais();
	$pdo->creeLesFraisForfait();
	$pdo->creeLesFraisHorsForfait();
	$pdo->majLaFicheFrais();
?>