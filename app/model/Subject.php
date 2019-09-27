<?php
namespace App\Model;
use Nette\Database\Connection;
use Nette\Utils\Strings;

class Subject extends BaseModel { 
private $user;    

public function getArrAttribute(\Nette\Security\user $user) {
    $this->user = $user; 
    
    $res = $this->db->fetch("SELECT * FROM subjects WHERE subj_id = (select parent from users where uid =  ?)",    $this->user->id);
    
	$result = NULL;
    if($res) {
        $result = array();
		foreach($res as $key => $value) {
       $result[Strings::normalize($key)] = $value;
      }	 	
        }    
    return $result;
}  
public function getArrAttributeBySid($subjid) {

    $res = $this->db->fetch("SELECT * FROM subjects WHERE subj_id = ?",  $subjid*1);
    

	$result = NULL;
    if($res) {
        $result = array();
		foreach($res as $key => $value) {
       $result[Strings::normalize($key)] = $value;
      }	 	
        }    
    
    return $result;
}
public function getFreeRefNumber($subjid,$field){
 //   $find = $this->raiseRefNumber($subjid, $field);
    //$find[$field];    
   $refnr = $this->getRefNumber($subjid,$field)[$field];
   
    $result = false;
    do {
    $refnr = $refnr+ 1;    
    $result =  $this->isFree($subjid, $field, $refnr);   
    } while (!$result);    
 	$this->db->query("UPDATE subjects SET ".$field." = ? WHERE subj_id = ?",  $refnr, $subjid);    
    return $refnr;


}   
private function getRefNumber($subjid, $field){
$res = $this->db->fetch("SELECT ".$field." FROM subjects WHERE subj_id = ?", $subjid);        
return $res;
} 
private function isFree($subjid, $field, $refnr) {
    $res = NULL;
    switch(strtolower($field)) {
        case "nabidky" :
            break;
        case "zakazky" :
            break;
        case "ppd":
            break;
        case "vpd":
            break;
        case "faktury":

            $res = $this->db->fetch("SELECT count(idecko) FROM faktury WHERE subj_id = ? AND refcislo = ? AND (druh_dokladu = 'FP' OR druh_dokladu = 'FH' OR druh_dokladu = 'DB')",    $subjid, $refnr);                        
       		foreach($res as $key => $value) {
            $res = $value;
            }             
            break;
        case "zlist":
            $res = $this->db->fetch("SELECT idecko FROM faktury WHERE subj_id = ? AND druh_dokladu = 'ZP' AND refcislo = ?",    $subjid, $refnr);
            foreach($res as $key => $value) {
            $res = $value;
            } 
            break;
        case "prijemky":               
        case "vydejky":
            $res = $this->db->fetch("SELECT count(idecko) FROM sklad WHERE subj_id = ? AND ".$field." = ? ",    $subjid, $refnr);                        
       		foreach($res as $key => $value) {
            $res = $value;
            }     
            break;
        case "prevodky":
            break;
        case "recyklist":
            break;
    }

    return ($res > 0 || $res === NULL) ? false : true;
}    
}