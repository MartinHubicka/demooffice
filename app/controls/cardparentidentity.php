<?php
namespace App\Controls;
 
use Nette\Application\UI;
use App\Model\Subject;
use Nette\SmartObject;
use Nette\Security AS NS;
use Nette\DI\Container;

class CardParentIdentity extends UI\Control
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
        $subject = new \App\Model\Subject($this->db, $this->container);        
        $this->getTemplate()->subject = $subject->getArrAttribute($this->user);
        $this->getTemplate()->setFile(__DIR__ . '/templates/cardParentIdentity.latte');
        $this->getTemplate()->render();
    }
 
}