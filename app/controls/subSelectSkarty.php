<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use Nette\DI\Container;

class SubSelectSkarty extends UI\Control
{

 /** @var NS\User */
public $user;	
	 /**
     * @var \Nette\Database\Context
     */
  public  $db;	 //sporný public, dořešit	
/** @var Container */
    protected $container;
private $skarty;    
    public function __construct( \Nette\Database\Connection $db, Container $container, \Nette\Security\user $user, $skarty=NULL) {		 		 		 		
		$this->user =$user;
		$this->db =$db;
        $this->skarty =$skarty;
		$this->container =$container;
	    }
    public function render() {
        $this->dataUpdate($this->skarty);        
    }
    public function dataUpdate($skarty) {        
        if($skarty===NULL){
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container);      //getParent       
        $skarty = new \App\Model\Skarty($this->db, $this->container);              
        $this->getTemplate()->skarty = $skarty->getSkarty($userm->getParentId($this->user->getId()));
        } else {
           $this->getTemplate()->skarty = $skarty;
        }    
        $this->getTemplate()->setFile(__DIR__ . '/templates/subselectskarty.latte');
        $this->getTemplate()->render();
    }
}