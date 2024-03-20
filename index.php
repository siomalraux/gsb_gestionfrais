<?php
require_once("include/fct.inc.php");
require_once("include/class.pdogsb.inc.php");
session_start();
$pdo = PdoGsb::getPdoGsb();
$pdo->creeLesTables('schema.sql');
if(count($pdo->getLesFichesFrais())==0){
	set_time_limit(0);
	$pdo->creeLesFichesFrais();
	$pdo->creeLesFraisForfait();
	$pdo->creeLesFraisHorsForfait();
	$pdo->majLaFicheFrais();
}
$estConnecte = estConnecte();
if(!isset($_REQUEST['uc']) || !$estConnecte){
   	$_REQUEST['uc'] = 'connexion';
}	 
$uc = $_REQUEST['uc'];
switch($uc){
	case 'connexion':{
		include("controleurs/c_connexion.php");break;
	}
	case 'gererFrais' :{
		include("controleurs/c_gererFrais.php");break;
	}
	case 'etatFrais' :{
		include("controleurs/c_etatFrais.php");break; 
	}
}
?>