<?php
namespace App\Model;
use Nette\Database\Connection;
use Latte\Engine;
use Nette\Utils;
class Skarty extends BaseModel {

public function getSkarty($subjid=NULL, $filter=NULL){
    
    //todo zapracovat filter
    $result = NULL;
   if($subjid!==NULL) {
  $res = $this->db->fetchAll("SELECT * FROM skarty WHERE subj_id = ? " ,  $subjid);
        if($res) {            
            $result = (object)$res;
        }  
    }   
    return $result;
}    
    
}