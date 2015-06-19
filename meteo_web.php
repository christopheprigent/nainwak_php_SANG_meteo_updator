<html>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <head>
      <title>météo updateur</title>
  </head>
  <body>
  <style>
  .label{float: left;text-align: right;width: 200px;margin-right:5px}
  </style>
<?php
/**
 * \file	               meteo_web.php
 * \author	             Christophe PRIGENT
 * \version	            0.42
 * \brief	              mode web MAJ météo
 *
 * \details	            Fichier web : formulaire saisie + action.
 *
 */
include_once('./includes/meteo_func.inc.php');
error_reporting(E_ALL);
$footer = "</body>\n</html>";
if (empty($_REQUEST['formfilled'])){
  showForm();
  die($footer);
}

if( empty($_REQUEST['nw_login'])    ||
    empty($_REQUEST['nw_passwd'])   ||
    empty($_REQUEST['sang_login'])  ||
    empty($_REQUEST['sang_passwd'])
  ){
  echo "<p>va t'acheter des doigs</p>";
  showForm($_REQUEST);
  die($footer);
}

$nw_login    = $_REQUEST['nw_login'];
$nw_passwd   = $_REQUEST['nw_passwd'];
$sang_login  = $_REQUEST['sang_login'];
$sang_passwd = $_REQUEST['sang_passwd'];
$action      = $_REQUEST['action'];

if (empty($action)) $action = 'ALL';
$phpsessid   = loginIntoSang($sang_login, $sang_passwd);
if ($action == 'ALL')
  foreach (array_keys($url_action) as $action)
      echo doAction($action, $phpsessid, $nw_login, $nw_passwd) ."<br />";
else
    echo doAction($action, $phpsessid, $nw_login, $nw_passwd)."<br />";
signoutSangDB($phpsessid);


function showForm($data=array('nw_login'=>'', 'nw_passwd'=>'', 'sang_login'=>'', 'sang_passwd'=>'')){

echo "
  <form action='?'>
    <input type='hidden' name='formfilled' value='42' />
    <p><span class='label'>login nainwak :</span> <input type='text'     name='nw_login'   value='".$data['nw_login']."' /></p>
    <p><span class='label'>  mdp nainwak :</span> <input type='password' name='nw_passwd'  value='".$data['nw_passwd']."' /></p>
    <p><span class='label'>login sang :</span>    <input type='text'     name='sang_login' value='".$data['sang_login']."' /></p>
    <p><span class='label'>  mdp sang :</span>    <input type='password' name='sang_passwd'value='".$data['sang_passwd']."' /></p>
    <p><span class='label'>  action :</span>
       <select name=action>
           <option value='ALL' selected >ALL</option>
           <option value='DETECT'       >DETECT</option>
           <option value='EVT'          >EVT</option>
           <option value='ENCYCLO'      >ENCYCLO</option>
       </select>
    </p>
    <input type='submit' />
  </form>
  ";
}

?>
