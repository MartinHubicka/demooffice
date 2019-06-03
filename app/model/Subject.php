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
    
}