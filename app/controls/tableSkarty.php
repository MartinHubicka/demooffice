<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use App\Controls as AC;
use Nette\DI\Container;

class TableSkarty extends UI\Control
{
  /** @var array */
    public $onChange;
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
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container);      //getParent       
        $skarty = new \App\Model\Skarty($this->db, $this->container);              
        $this->getTemplate()->skarty = $skarty->getSkarty($userm->getParentId($this->user->getId()));
        $this->getTemplate()->setFile(__DIR__ . '/templates/tableskarty.latte');
        $this->getTemplate()->render();

        
    }
/**
 * @return SubSelectSkarty
 */
protected function createComponentSubSelectSkarty() {		
       return new AC\SubSelectSkarty($this->db, $this->container,$this->user);    
}        
    
}