<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use Nette\DI\Container;

class TableCisr extends UI\Control
{    
 /** @var NS\User */
public $user;	
	 /**
     * @var \Nette\Database\Context
     */
  public  $db;	 //sporný public, dořešit	
/** @var Container */
    protected $container;
    public function __construct( \Nette\Database\Connection $db, Container $container, \Nette\Security\user $user) {		 		 		 		
		$this->user =$user;
		$this->db =$db;
		$this->container =$container;
	    }
    public function render() {
        $this->dataUpdate();        
    }
    public function dataUpdate() {
        $subject = new \App\Model\MyAuthenticator($this->db, $this->container);      //getParent               
        $this->getTemplate()->ciselnerady = $subject->getCiselneRady($this->user->getId());
        $this->getTemplate()->setFile(__DIR__ . '/templates/tablecisr.latte');
        $this->getTemplate()->render();

        
    }

}