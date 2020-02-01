<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use Nette\DI\Container;

class TableAdresar extends UI\Control
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
        $adresar = new \App\Model\Adresar($this->db, $this->container);              
        $this->getTemplate()->adresy = $adresar->getAdresy($userm->getParent($this->user->getId())["subj_id"]);
        $this->getTemplate()->setFile(__DIR__ . '/templates/tableadresar.latte');
        $this->getTemplate()->render();

        
    }

}