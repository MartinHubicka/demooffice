<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use Nette\DI\Container;

class ModalPrijemEET extends UI\Control
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
        $user = new \App\Model\MyAuthenticator($this->db, $this->container);      //getParent
        $eet = new \App\Model\EETpokladna($this->db, $this->container);      
        $adresar = new \App\Model\Adresar($this->db, $this->container);      
        
        $this->getTemplate()->data = $eet->getEETlist($user->getParent($this->user->getId())["subj_id"], true); 
       // $this->getTemplate()->adresy = $adresar->getAdresyBysubjid();
        $this->getTemplate()->setFile(__DIR__ . '/templates/modalprijemeet.latte');
        $this->getTemplate()->render();
    }
 
}