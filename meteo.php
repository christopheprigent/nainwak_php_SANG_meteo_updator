<?php
/**
 * \file	               meteo.php
 * \author	             Christophe PRIGENT
 * \version	            0.42
 * \brief	              mode Client MAJ météo
 *
 * \details	            Fichier CLI only. taper --help pour avoir plus d'informations.
 *
 * \param -nw_login      votre login nainwak.
 * \param -nw_passwd     votre mote de passe nainwak.
 * \param -sang_login    votre login sang.
 * \param -sang_passwd   votre mot de passe sang.
 * \param -action        action pour la mise à jour : EVT|DETECT|ENCYCLO|ALL . défaut ALL.
 */
include_once('./includes/meteo_func.inc.php');

$opts  = array(   "nw_login:"
                , "nw_passwd:"       // Valeur requise
                , "sang_login:"      // Valeur requise
                , "sang_passwd:"     // Valeur requise
                , "action:"          // Valeur optionnelle. défaut ALL
                , "h"                // Valeur optionnelle
                , "help"             // Valeur optionnelle
              );
$options    = getopt(null, $opts);

if( isset($options['h'])           ||
    isset($options['help'])        ||
    empty($options['nw_login'])    ||
    empty($options['nw_passwd'])   ||
    empty($options['sang_login'])  ||
    empty($options['sang_passwd'])
  ){
	usage();
}

$nw_login    = $options['nw_login'];
$nw_passwd   = $options['nw_passwd'];
$sang_login  = $options['sang_login'];
$sang_passwd = $options['sang_passwd'];
$action      = $options['action'];

if (empty($action)) $action = 'ALL';
$phpsessid   = loginIntoSang($sang_login, $sang_passwd);
if ($action == 'ALL')
  foreach (array_keys($url_action) as $action)
      echo doAction($action, $phpsessid, $nw_login, $nw_passwd) ."\n";
else
    echo doAction($action, $phpsessid, $nw_login, $nw_passwd)."\n";
signoutSangDB($phpsessid);
