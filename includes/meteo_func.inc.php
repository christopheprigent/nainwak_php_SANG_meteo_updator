<?php
/**
 * \file	               meteo_func.inc.php
 * \author	             Christophe PRIGENT
 * \version	            0.42
 * \brief	              mode Client MAJ météo
 *
 * \details	            Fichier contenant les fonctions coeurs.
 */

/**
 * Variables
 */
define('NW_BASE_URL'  , "http://www.nainwak.com/jeu/");
define('SANG_BASE_URL', "http://www.meteosang.fr/");
$url_action              = array();
$url_action['ENCYCLO']   = 'objvus.php';
$url_action['DETECT']    = 'detect.php';
$url_action['EVT']       = 'even.php';
// url_action['PERSO']     = 'perso.php' NOT YET IMPLEMENTED IN SANG

/**
 * launcher d'action
 * @return retour IHM météo sang
 */
function doAction($action, $phpsessid, $nw_login, $nw_passwd){
    $content 	= getInfoFromNW($action, $nw_login, $nw_passwd);
    if ($content == 42){// BAD LOGIN
        signoutSangDB($phpsessid); // CLEAN BEFORE EXIT
        exit(42);
    }
     $ret 		= updateSangDB($content, $phpsessid);

    return $ret;
}

/**
 * récupère les infos sur nainwak suivant l'action
 * @return data nainwak
 */
function getInfoFromNW($action, $nw_login, $nw_passwd){
    global $url_action;

    $url = NW_BASE_URL.$url_action[$action].'?duree=240&type=ALL'."&login=$nw_login&password=$nw_passwd";
    $ret = http_post($url);

    if (preg_match('/\<div class=\"titre\"\>\[Login\]\<\/div\>/',$ret['content'])){
        echo "Bad nainwak login or passwd : $nw_login, $nw_passwd\n";
        return 42;
      }

    return $ret['content'];
  }

/**
 * post les données sur notre météo
 * @return MSG météo
 */
function updateSangDB($content, $phpsessid){
    $params  =  array(   'detect'    => addslashes($content)
                       , 'btn_ajout' => 'Ok');

    $data = http_post(SANG_BASE_URL.'detection.php', $params, $phpsessid);

  if (preg_match('/\<TD class=\"tdcentre\"\>(.*)\<\/TH\>/', $data['content'], $m)){
    $html_reg = '/<+\s*\/*\s*([A-Z][A-Z0-9]*)\b[^>]*\/*\s*>+/i';
    $ret = html_entity_decode(preg_replace($html_reg,'', $m[1]));
  }
    else
      $ret = $data['content'];

    return $ret;
  }

/**
 * logout de l'IHM météo sang
 * @return retour logout
 */
function signoutSangDB($phpsessid){
    $params  =  array('sortie' => 'O');

    return http_post(SANG_BASE_URL.'index.php', $params, $phpsessid);
  }

/**
 * The help for the CLI usage of this file
 * @return Void echo String
 */
function usage(){
        global $argv;
        echo $argv[0]." --nw_login=login --nw_passwd=passwd --sang_login=login --sang_passwd=passwd [--action=EVT|DETECT|ENCYCLO|ALL] [--help]
--nw_login login\t: votre login nainwak.
--nw_passwd pass\t: votre mot de passe nainwak.
--sang_login login\t: votre login sang.
--sang_passwd pass\t: votre mot de passe sang.
--action act\t\t: action pour la mise à jour : EVT|DETECT|ENCYCLO|ALL . defaut ALL.
--help\t\t\t: give this help list.
";
        exit(0);
}

/**
 * se connect sur l'IHM web météo SANG
 * @return cookie de session
 */
function loginIntoSang($sang_login, $sang_passwd){
    $params  =  array( 'utilisateur'  => $sang_login
                    //  ,'mdp'          => urlencode($sang_passwd)
                      ,'mdp'          => $sang_passwd
                    );
    $ret = http_post(SANG_BASE_URL."index.php", $params);

    if (preg_match('/Erreur/',$ret['content']))
    {
      echo "Bad Sang login or passwd : $sang_login, $sang_passwd\n";
      exit(42);
    }

    $phpsessid = get_sessid($ret['headers']);

    return $phpsessid;
  }

/**
 * parse les header de la météo sang
 * @return cookie session id
 */
function get_sessid($ar_header)
{
  $sessid = FALSE;

  foreach ($ar_header as $k=>$v)
    if (preg_match('/Set-Cookie: (PHPSESSID=.*);/',$v, $m))
      {
        $sessid = $m[1];
        break;
      }

        return $sessid;
}

/**
 * helper post en php
 * @return array avec 'content':data retour et 'headers':header.
 */
function http_post($url, $data=FALSE,$cookie=FALSE)
{
  if ($data)
    $data_url = http_build_query ($data);
  else
    $data_url = '';
  $data_len = strlen ($data_url);
  $header = "Connection: close\r\nContent-Length: $data_len\r\n";

  if ($cookie){
      $header .="Cookie: $cookie;\r\n";
  }

  return array (
                    'content'  =>file_get_contents (  $url
                                                    , false
                                                    , stream_context_create (array (
                                                                               'http'=>array (   'method'  =>'POST'
                                                                                                , 'header'  =>$header
                                                                                                , 'content' =>$data_url
                                                                                              )
                                                                                    )
                                                                            )
                                                    )
                    , 'headers'=>$http_response_header
        );
}
