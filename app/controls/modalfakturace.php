<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use Nette\DI\Container;

class ModalFakturace extends UI\Control
{
 /** @var NS\User */
public $user;	
 /** @var int */    
public $fid; //faktura id or NULL
 /** @var string */    
public $fdruh; //faktura druh - DP dobropis, FP faktura převodem, FH faktura v hotovosti, ZP podklad pro zálohovou platbu   
	 /**
     * @var \Nette\Database\Context
     */
  public  $db;	 //sporný public, dořešit	
/** @var Container */
    protected $container;
    public function __construct( \Nette\Database\Connection $db, Container $container, \Nette\Security\user $user, $fid = NULL, $fdruh = 'FP') {		 		 		 		
		$this->user =$user;
		$this->db =$db;
        $this->fid =$fid; //nabídka id v systému
        $this->fdruh =$fdruh; //nabídka id v systému určuje atributy dokladu
		$this->container =$container;
	    }
    public function render() {
        $user = new \App\Model\MyAuthenticator($this->db, $this->container);      //getParent
    //    $eet = new \App\Model\EETpokladna($this->db, $this->container);      
        //$nabidka=
        $adresar = new \App\Model\Adresar($this->db, $this->container);      
        
        //$this->getTemplate()->data = $eet->getEETlist($user->getParent($this->user->getId())["subj_id"], true); 
       // $this->getTemplate()->adresy = $adresar->getAdresyBysubjid();
        $this->getTemplate()->setFile(__DIR__ . '/templates/modalfakturace.latte');
        $this->getTemplate()->render();
    }
 
}