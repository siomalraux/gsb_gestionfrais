1 - Créer et remplir la base de données et les tables :
mariadb --user root --password < schema.sql 

2 - Remplir les tables avec des valeurs aléatoires :
http://hostname/gestionfrais/install.php

3 - Configurer les informations de connexion au backend dans le fichier :
include/class.pdogsb.inc.php

3 - Tester l'application :
http://hostname/gestionfrais