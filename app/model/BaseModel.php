<?php
namespace App\Model;
use Nette\Database\Connection;

class BaseModel  {
	 /**
     * @var \Nette\Database\Connection
     */
   public $db;    

public function __construct(\Nette\Database\Connection $db)		 
    {				 		 
    
   
	 	 	$this->db = $db;
			
    }
}