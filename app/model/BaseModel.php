<?php
namespace App\Model;
use Nette\Database\Connection;
use Nette\DI\Container;

class BaseModel  {
	 /**
     * @var \Nette\Database\Connection
     */
   public $db;    
   /** @var Container */
   protected $container;
  /** @var Array */
   protected $konstanty;
   public $result; //vlastní object výsledku volané funkce 
public function __construct( \Nette\Database\Connection $db, Container $container)		 
    {				 		 
	 	 	$this->db = $db;
            $this->container = $container;
            $this->konstanty = $container->getParameters()["konstanty"];
            $this->result =(object) [
                'chyba' => true,
                'zprava' => false,
                'zpravatext' => '',
                'data' => NULL
                ];		
    }

}