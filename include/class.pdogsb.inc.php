<?php
/*
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */
class PdoGsb{   		
	private static $server='mysql:host=192.168.1.109';
	private static $db='dbname=frais_db';   		
	private static $db_user='sqladmin';
	private static $user_pw='5ecuri+Y';
	private static $monPdo;
	private static $monPdoGsb=null;
	/*
	 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
	 * pour toutes les méthodes de la classe
	 */				
	private function __construct() {
    	PdoGsb::$monPdo = new PDO(PdoGsb::$server.';'.PdoGsb::$db, PdoGsb::$db_user, PdoGsb::$user_pw); 
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
	}
	public function __destruct() {
		PdoGsb::$monPdo = null;
	}
	/*
	 * Fonction statique qui crée l'unique instance de la classe
	 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
	 * @return l'unique objet de la classe PdoGsb
	 */
	public static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null) {
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
	/*
	 * Fonctions d'initialisation des lignes des tables
	 * avec des données aléatoires
	 */
	public function getLesVisiteurs() {
		$req = "select * from visiteur";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
	public function getLesFichesFrais() {
		$req = "select * from fichefrais";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
 	}
 	public function getLesIdFraisForfait() {
		$req = "select fraisforfait.id as id from fraisforfait order by fraisforfait.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
 	}
 	public function getLeDernierMois($idVisiteur) {
		$req = "select max(mois) as dernierMois from fichefrais where idVisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne['dernierMois'];
 	}
	public function getDesFraisHorsForfait() {
		$tab = array(
			1 => array(
			   "lib" => "repas avec praticien",
			   "min" => 30,
			   "max" => 50 ),
			2 => array(
			   "lib" => "achat de matériel de papèterie",
			   "min" => 10,
			   "max" => 50 ),
			3	=> array(
			   "lib" => "taxi",
			   "min" => 20,
			   "max" => 80 ),
			4 => array(
			   "lib" => "achat d'espace publicitaire",
			   "min" => 20,
			   "max" => 150 ),
			5 => array(
			   "lib" => "location salle conférence",
			   "min" => 120,
			   "max" => 650 ),
			6 => array(
			   "lib" => "Voyage SNCF",
			   "min" => 30,
			   "max" => 150 ),
			7 => array(
			   "lib" => "traiteur, alimentation, boisson",
			   "min" => 25,
			   "max" => 450 ),
			8 => array(
			   "lib" => "rémunération intervenant/spécialiste",
			   "min" => 250,
			   "max" => 1200 ),
			9 => array(
			   "lib" => "location équipement vidéo/sonore",
			   "min" => 100,
			   "max" => 850 ),
			10 => array(
			   "lib" => "location véhicule",
			   "min" => 25,
			   "max" => 450 ),
			11 => array(
			   "lib" => "frais vestimentaire/représentation",
			   "min" => 25,
			   "max" => 450 ) 
		);
		return $tab;
 	}
 	public function creeLesFichesFrais() {
		$lesVisiteurs = $this->getLesVisiteurs();
		$moisActuel = getMois(date("d/m/Y"));
		$moisDebut = "201001";
		$moisFin = getMoisPrecedent($moisActuel);
		foreach($lesVisiteurs as $unVisiteur) {
			$moisCourant = $moisFin;
			$idVisiteur = $unVisiteur['id'];
			$n = 1;
			while($moisCourant >= $moisDebut) {
				if($n == 1) {
					$etat = "CR";
					$moisModif = $moisCourant;
				} else {
					if($n == 2) {
						$etat = "VA";
						$moisModif = getMoisSuivant($moisCourant);
					} else {
						$etat = "RB";
						$moisModif = getMoisSuivant(getMoisSuivant($moisCourant));
					}
				}
				$numAnnee =substr( $moisModif,0,4);
				$numMois =substr( $moisModif,4,2);
				$dateModif = $numAnnee."-".$numMois."-".rand(1,8);
				$nbJustificatifs = rand(0,12);
				$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
				values ('$idVisiteur','$moisCourant',$nbJustificatifs,0,'$dateModif','$etat');";
				PdoGsb::$monPdo->query($req);
				$moisCourant = getMoisPrecedent($moisCourant);
				$n++;
			}
		}
 	}
	public function creeLesFraisHorsForfait() {
		$desFrais = $this->getDesFraisHorsForfait();
		$lesFichesFrais= $this->getLesFichesFrais();
		foreach($lesFichesFrais as $uneFicheFrais) {
			$idVisiteur = $uneFicheFrais['idVisiteur'];
			$mois =  $uneFicheFrais['mois'];
			$nbFrais = rand(0,5);
			for($i=0;$i<=$nbFrais;$i++) {
				$hasardNumfrais = rand(1,count($desFrais)); 
				$frais = $desFrais[$hasardNumfrais];
				$lib = $frais['lib'];
				$min= $frais['min'];
				$max = $frais['max'];
				$hasardMontant = rand($min,$max);
				$numAnnee =substr( $mois,0,4);
				$numMois =substr( $mois,4,2);
				$hasardJour = rand(1,28);
				if(strlen($hasardJour)==1) {
					$hasardJour="0".$hasardJour;
				}
				$hasardMois = $numAnnee."-".$numMois."-".$hasardJour;
				$req = "insert into lignefraishorsforfait(idVisiteur,mois,libelle,date,montant)
				values('$idVisiteur','$mois','$lib','$hasardMois',$hasardMontant);";
				PdoGsb::$monPdo->query($req);
			}
		}
	}
	public function creeLesFraisForfait() {
		$lesFichesFrais= $this->getLesFichesFrais();
		$lesIdFraisForfait = $this->getLesIdFraisForfait();
		foreach($lesFichesFrais as $uneFicheFrais) {
			$idVisiteur = $uneFicheFrais['idVisiteur'];
			$mois =  $uneFicheFrais['mois'];
			foreach($lesIdFraisForfait as $unIdFraisForfait) {
				$idFraisForfait = $unIdFraisForfait['id'];
				if(substr($idFraisForfait,0,1)=="K") {
					$quantite =rand(300,1000);
				} else {
					$quantite =rand(2,20);
				}
				$req = "insert into lignefraisforfait(idvisiteur,mois,idfraisforfait,quantite)
				values('$idVisiteur','$mois','$idFraisForfait',$quantite);";
				PdoGsb::$monPdo->query($req);	
			}
		}
	}
	public function majLeMdpVisiteur() {
		$req = "select * from visiteur";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		$lettres ="azertyuiopqsdfghjkmwxcvbn123456789";
		foreach($lesLignes as $unVisiteur)	{
			$mdp = "";
			$id = $unVisiteur['id'];
			for($i =1;$i<=5;$i++) {
				$uneLettrehasard = substr( $lettres,rand(33,1),1);
				$mdp = $mdp.$uneLettrehasard;
			}
			$req = "update visiteur set mdp ='$mdp' where visiteur.id ='$id' ";
			PdoGsb::$monPdo->query($req);
		}
	}
	public function majLaFicheFrais() {
		$lesFichesFrais= $this->getLesFichesFrais();
		foreach($lesFichesFrais as $uneFicheFrais) {
			$idVisiteur = $uneFicheFrais['idVisiteur'];
			$mois =  $uneFicheFrais['mois'];
			$dernierMois = $this->getDernierMois($idVisiteur);
			$req = "select sum(montant) as cumul from ligneFraisHorsForfait where ligneFraisHorsForfait.idVisiteur = '$idVisiteur' 
					and ligneFraisHorsForfait.mois = '$mois' ";
			$res = PdoGsb::$monPdo->query($req);
			$ligne = $res->fetch();
			$cumulMontantHorsForfait = $ligne['cumul'];
			$req = "select sum(ligneFraisForfait.quantite * fraisForfait.montant) as cumul from ligneFraisForfait, FraisForfait where
			ligneFraisForfait.idFraisForfait = fraisForfait.id   and   ligneFraisForfait.idVisiteur = '$idVisiteur' 
					and ligneFraisForfait.mois = '$mois' ";
			$res = PdoGsb::$monPdo->query($req);
			$ligne = $res->fetch();
			$cumulMontantForfait = $ligne['cumul'];
			$montantEngage = $cumulMontantHorsForfait + $cumulMontantForfait;
			$etat = $uneFicheFrais['idEtat'];
			if($etat == "CR" )
				$montantValide = 0;
			else
				$montantValide = $montantEngage*rand(80,100)/100;
			$req = "update fichefrais set montantValide =$montantValide where
			idVisiteur = '$idVisiteur' and mois='$mois'";
			PdoGsb::$monPdo->query($req);
		}
	}
	/*
	 * Retourne les informations d'un visiteur
	 * @param $login 
	 * @param $mdp
	 * @return l'id, le type,le nom et le prénom sous la forme d'un tableau associatif 
	 */
	public function getInfosVisiteur($login, $mdp) {
		$req = "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom from visiteur 
		where visiteur.login='$login' and visiteur.mdp='$mdp'";
		$rs = PdoGsb::$monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	}
	/*
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
	 * concernées par les deux arguments
	 * La boucle foreach ne peut être utilisée ici car on procède
	 * à une modification de la structure itérée - transformation du champ date-
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
	 */
	public function getLesFraisHorsForfait($idVisiteur,$mois) {
	    $req = "select * from lignefraishorsforfait where lignefraishorsforfait.idvisiteur ='$idVisiteur' 
		and lignefraishorsforfait.mois = '$mois' ";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++) {
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
	/*
	 * Retourne le nombre de justificatif d'un visiteur pour un mois donné
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return le nombre entier de justificatifs 
	 */
	public function getNbjustificatifs($idVisiteur, $mois) {
		$req = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne['nb'];
	}
	/*
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
	 * concernées par les deux arguments
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
	 */
	public function getLesFraisForfait($idVisiteur, $mois) {
		$req = "select fraisforfait.id as idfrais, fraisforfait.libelle as libelle, 
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur ='$idVisiteur' and lignefraisforfait.mois='$mois' 
		order by lignefraisforfait.idfraisforfait";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
	/*
	 * Retourne tous les id de la table FraisForfait
	 * @return un tableau associatif 
	 */
	public function getLesIdFrais() {
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
	/*
	 * Met à jour la table lignefraisforfait
	 * Met à jour la table lignefraisforfait pour un visiteur et
	 * un mois donné en enregistrant les nouveaux montants
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
	 * @return un tableau associatif 
	 */
	public function majFraisForfait($idVisiteur, $mois, $lesFrais) {
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais) {
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
			and lignefraisforfait.idfraisforfait = '$unIdFrais'";
			PdoGsb::$monPdo->exec($req);
		}
		
	}
	/*
	 * met à jour le nombre de justificatifs de la table fichefrais
	 * pour le mois et le visiteur concerné
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @param $nbJustificatifs
	 */
	public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs) {
		$req = "update fichefrais set nbjustificatifs = $nbJustificatifs 
		where fichefrais.idvisiteur = '$idVisiteur' and fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);	
	}
	/*
	 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return vrai ou faux 
	 */	
	public function estPremierFraisMois($idVisiteur,$mois) {
		$ok = false;
		$req = "select count(*) as nblignesfrais from fichefrais 
		where fichefrais.mois = '$mois' and fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0) {
			$ok = true;
		}
		return $ok;
	}
	/*
	 * Retourne le dernier mois en cours d'un visiteur
	 * @param $idVisiteur 
	 * @return le mois sous la forme aaaamm
	 */	
	public function dernierMoisSaisi($idVisiteur) {
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
	/*
	 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
	 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
	 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 */
	public function creeNouvellesLignesFrais($idVisiteur,$mois) {
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR') {
			$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');	
		}
		$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values('$idVisiteur','$mois',0,0,now(),'CR')";
                
		PdoGsb::$monPdo->exec($req);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais) {
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values('$idVisiteur','$mois','$unIdFrais',0)";
			PdoGsb::$monPdo->exec($req);
		 }
	}    
	/*
	 * Crée un nouveau frais hors forfait pour un visiteur et un mois donné
	 * à partir des informations fournies en paramètre
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @param $libelle : le libelle du frais
	 * @param $date : la date du frais au format français jj//mm/aaaa
	 * @param $montant : le montant
	 */
	public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant) {
		$dateFr = dateFrancaisVersAnglais($date);
		$req = "insert into lignefraishorsforfait 
		values(DEFAULT,'$idVisiteur','$mois','$libelle','$dateFr','$montant')";
		PdoGsb::$monPdo->exec($req);
	}
	/*
	 * Supprime le frais hors forfait dont l'id est passé en argument
	 * @param $idFrais 
	 */
	public function supprimerFraisHorsForfait($idFrais) {
		$req = "delete from lignefraishorsforfait where lignefraishorsforfait.id =$idFrais ";
		PdoGsb::$monPdo->exec($req);
	}
	/*
	 * Retourne les mois pour lesquels un visiteur a une fiche de frais
	 * @param $idVisiteur 
	 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
	 */
	public function getLesMoisDisponibles($idVisiteur) {
		$req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.idEtat in ('CR', 'VA')
		order by fichefrais.mois desc ";
		$res = PdoGsb::$monPdo->query($req);
		$lesMois =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$numAnnee =substr( $mois,0,4);
			$numMois =substr( $mois,4,2);
			$lesMois["$mois"]=array(
		    "mois"=>"$mois",
		    "numAnnee"  => "$numAnnee",
			"numMois"  => "$numMois"
             );
			$laLigne = $res->fetch(); 		
		}
		return $lesMois;
	}
	/*
	 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
	 */	
	public function getLesInfosFicheFrais($idVisiteur,$mois) {
		$req = "select fichefrais.idEtat as idEtat, fichefrais.dateModif as dateModif, fichefrais.nbJustificatifs as nbJustificatifs, 
			fichefrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join etat on fichefrais.idEtat = etat.id 
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
	/*
	 * Modifie l'état et la date de modification d'une fiche de frais
	 * Modifie le champ idEtat et met la date de modif à aujourd'hui
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 */ 
	public function majEtatFicheFrais($idVisiteur,$mois,$etat) {
		$req = "update fichefrais set idEtat = '$etat', dateModif = now() 
		where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}
}
?>