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
    
public function __construct( \Nette\Database\Connection $db, Container $container)		 
    {				 		 
	 	 	$this->db = $db;
            $this->container = $container;
            $this->konstanty = $container->getParameters()["konstanty"];
		//	var_dump($container->getParameters()["konstanty"]["temppassmin"]);
        //die();
    }

}