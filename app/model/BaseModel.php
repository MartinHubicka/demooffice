<?php
namespace App\Model;
use Nette\Database\Connection;
class BaseModel  {
	 /**
     * @var \Nette\Database\Connection
     */
   public $db;
	public $user;
public function __construct(\Nette\Database\Connection $db, \Nette\Security\user $user)		 
    {				 		 
	 	 	$this->db = $db;
			$this->user = $user;
    }
}