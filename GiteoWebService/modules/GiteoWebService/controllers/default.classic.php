<?php

/**
 * @package   GiteoWebService
 * @subpackage GiteoWebService
 * @author    your name
 * @copyright 2011 your name
 * @link      http://www.yourwebsite.undefined
 * @license    All rights reserved
 */
class defaultCtrl extends jController {

    function index() {

//Format de la réponse
        $response = $this->getResponse('json');
//Récupération de la requête de l'url, découpage des éléments puis analyse
        $monURL = $_SERVER['REQUEST_URI'];
        $urlquery = parse_url($monURL, PHP_URL_QUERY);
        $multiplerequest = explode("||", $urlquery);

        $response->data = array();
//Si plusieurs requête
        for ($i = 0; $i < count($multiplerequest); $i++) {

            $request = explode("|", $multiplerequest[$i]);
            switch ($request[0]) {
                case "GET":

                    //Récupération des différents champs des tables à partir des documents xml des daos
                    $xmldao = new DomDocument;
                    $chemin = '../../GiteoWebService/modules/GiteoWebService/daos/' . $request[1] . '.dao.xml';
                    $xmldao->load($chemin);
                    $Listeprop = $xmldao->getElementsByTagName("property");
                    //Récupération du contenu de la table
                    $Factory = jDao::get($request[1]);

                    foreach (explode("&", $request[2]) as $chunk) {
                        //var_dump($request[2]);    
                        $param = explode("=", $chunk);
                        //var_dump($param);

                        if ($param[1] == "?") {

                            $listOfAll = $Factory->findAll();
                        } else {

                            $conditions = jDao::createConditions();
                            $conditions->addCondition($param[0], '=', $param[1]);
                            $listOfAll = $Factory->findBy($conditions);
                        }

                        foreach ($listOfAll as $val) {
                            $chaine = array();
                            foreach ($Listeprop as $Listename) {

                                $toEval = '$var = $val->' . $Listename->getAttribute("name") . ';';
                                eval($toEval);
                                $chaine[$Listename->getAttribute("name")] = $var;
                            }
                            $response->data[] = $chaine;
                        }
                    }


                    break;
                case "POST":


                    break;
                case "PUT":
                    $xmldao = new DomDocument;
                    $chemin = '../../GiteoWebService/modules/GiteoWebService/daos/' . $request[1] . '.dao.xml';
                    $xmldao->load($chemin);
                    $Listeprop = $xmldao->getElementsByTagName("property");
                    
                    $Factory = jDao::get($request[1]);
                    $record = jDao::createRecord($request[1]);
                    
// on remplit le record
                   foreach (explode("&", $request[2]) as $chunk) {
                            
                        $param = explode("=", $chunk);
                        $record-> $param[0] = $param[1];
                    }
// on le sauvegarde dans la base
                    $e = new Exception("BDD error");
                    try{
                    $Factory->insert($record);
                    $response->data[] = "PUT succeed";
                    }
                    catch(Exception $e)
                    {
                     $response->data[] = "PUT error";   
                    }

                    break;
                case "DELETE":

                    $Factory = jDao::get($request[1]);
                    foreach (explode("&", $request[2]) as $chunk) {

                        $param = explode("=", $chunk);

                    try{    
                    $Factory->delete($param[1]);
                    $response->data[] = "DELETE succeed";
                    }
                    catch(Exception $e)
                    {
                     $response->data[] = "DELETE error";   
                    }
                    }
                    break;
                default:
                    $response->data[] = "Method error";
                    break;
            }
        }

        return $response;
    }

}