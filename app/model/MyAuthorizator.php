<?php
 
namespace App\Model;
 
use Nette\SmartObject;
use Nette\Security as NS;
use Nette\Application\UI as NUI;
/**
 * Users Authorizator.
 */
class MyAuthorizator implements NS\IAuthorizator 
{
 
    /**
     * @var NS\Permission
     */
    private $acl;
    public function __construct() {
        $this->acl = new NS\Permission();
		 /*addRole(role, parent)*/
        $this->acl->addRole('guest'); //nepřihlášený návštěvník stránek		 
        $this->acl->addRole('registered'); // registrovaný uživatel nebo zákazník
        $this->acl->addRole('user', 'registered'); //přihlášený uživat TW
        $this->acl->addRole('participant', 'user'); //přihlášený pro užživatele s provizní smlouvou
		$this->acl->addRole('obchod', 'user'); //zakázky, nabídky
        $this->acl->addRole('fakturace', 'user'); //fakturace
        //$this->acl->addRole('crew', 'user'); //posádka vozu/technologie - zejména ve spojení s plánovačem termínů a termínů k dispozici
		$this->acl->addRole('pokladna', 'user' ); //práva pro práci s pokladnou pokladnou        
        $this->acl->addRole('admin', 'user'); // administrátor
        
		 
		 //zdroje - stránky       		 
        //$this->acl->addResource('customer');//prostor pro regitrované zákazníky		   
        $this->acl->addResource('user');//prostor pro regitrované uživ.tw		   
       //$this->acl->addResource('backend'); //pro přihlášené uživatele TW
		 //$this->acl->addResource('pokladna');//pokladna EET
        
        //ACL        
       
//		 $this->acl->allow('customer', array('customer')); //třetí atribut jako např ['view', 'ass', 'delete', 'list' ..] neuvádím, protože ho při startupu nefiltruji, prozatím
	     $this->acl->allow('user', array('user'));
		//$this->acl->allow('pokladna', array('pokladna'));
		  //$this->acl->allow('admin', array('user', 'customer'));
         $this->acl->allow('admin',  $this->acl::ALL);     		 
    }
 
    function isAllowed($role, $resource, $privilege) {		 
        return $this->acl->isAllowed($role, $resource, $privilege);		 
    }
	function afterLoginRedirect(NS\user $user,NUI\presenter $presenter) {
        	 $presenter->redirect("Homepage:"); //toto by po loginu nemělo nastat
        
	}
	function afterLogoutRedirect(NUI\presenter $presenter) {
	 $presenter->redirect("Homepage:");
	} 
}