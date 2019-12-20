<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI as NUI;
use App\Mail;
use App\Model\MyAuthenticator;
use App\Model;
use Nette\Mail\Message;
use App\Presenters as AP;
use App\Services;
use App\Model\TempStorage;
use App\Presenters\stdClass;
use App\Controls as AC;
use Nette\Security AS NS;
use Nette\Utils\Strings;
use Nette\Http\Url;
use Nette\DI\Container;

class BasePresenter extends NUI\Presenter
{    
CONST NOTALLOW_SOURCE_MESSAGE = "Pro zobrazení požadované stránky nemáte příslušné oprávnění.";
	
/** @var Container */
    protected $container;
    /** @var tempStorage */
    public $tempStorage;
    /** @var Array */
    protected $konstanty;	
/**
 * @var \App\Model\MyAuthenticator
 * @inject
 */
public $MyAuthenticator;
	
	public $url;
/**
 * @var \App\Model\MyAuthorizator
 * @inject
 */
public $MyAuthorizator;
 /** @var NS\User */
public $user;	
	 /**
     * @var \Nette\Database\Context
     */
  public  $db;	 //sporný public, dořešit	
public $username;
	public function __construct(NS\User $user,TempStorage $tempStorage, \Nette\Database\Connection $db, Container $container ) {
        		parent::__construct();	
		$this->user = $user;		
		$this->db = $db;
		$this->url = new Url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");// \Nette\Http\Url;
		$this->tempStorage = $tempStorage;
		$this->username = ''; //identifikace jména uživatele				
      //  $this->parameters = $container->getParameters()["konstanty"];
		 parent::__construct();
        $this->container = $container;
        $this->konstanty = $container->getParameters()["konstanty"];
       
        //var_dump($container->getParameters()["konstanty"]);
        
	}
    public function startup() {
        parent::startup(); //V každém presenteru je NUTNÉ volat startup předka, pokud je startup v presenteru použitý. 
    }
protected function beforeRender() {
    //vlastní filtry do šablony     
        $this->template->addFilter('filterdsplatnosti', function ($dvystaveni, $splatnost) {             
            return date('Y-m-d', strtotime($dvystaveni. ' + '. abs($splatnost).' days'));
        });
    }
    

    
public function renderShow (){
    //funkce která je modifikována v konkrétních musí existovat i v basePresenteru
    
}

    
    public function handleupdateTags($table =NULL, $idecko=NULL, $value="") {        
        if($table!==NULL && $idecko !==NULL){
            $userm = new \App\Model\MyAuthenticator($this->db, $this->container);
            $userm->updateTags($userm->getParent($this->user->getId())["subj_id"], $table, $idecko, $value );
        }
    }
    
public function handleresetPass ($email=NULL, $key=NULL){
    $chyba = true;
    $chybatext = "";
    $result = false;
	if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
        if($email !== NULL && substr_count($email, '@') == 1 && substr_count($email, '.') > 0 && $key !== NULL && $key !== '') {
            $result = ($this->MyAuthenticator->generateTemppass($email, $key));		
            if($result==1){
            $chyba = false;
            $chybatext = "<p>Vygenerované heslo bylo zasláno na váš email.</p><p><strong>Platnost vygenerovaného hesla je ".$this->konstanty["temppassmin"]." minut.</strong></p><p>Pokud se přihlásíte po této době, nebude záložní heslo již platné a bude třeba ho vygenerovat znovu.</p>";
            } else {
            $chyba = true;
            $chybatext = "<p>Neregistrovaný uživatel, nebo klíč.</p><p>Nemáte oprávnění k přístupu</p>";        
            }
        } else {
            $chybatext = "Email nebo klíč byly zadány v nepřípustném formátu.";            
        }
    $this->payload->chyba = $chyba;
    $this->payload->chybatext = $chybatext;        
    $this->payload->result = $result;            
   	$this->sendPayload();			   
    }    
}
    
/**
 * @return ModalPrijemEET
 */
protected function createComponentModalPrijemEET () {		
    return new AC\ModalPrijemEET($this->db, $this->container,$this->user);    
}
 
/**
 * @return ModalNabidka
 */
protected function createComponentModalNabidka () {		
    return new AC\ModalNabidka($this->db, $this->container,$this->user);    
}    

/**
 * @return ModalFakturace
 */
protected function createComponentModalFakturace () {		
    return new AC\ModalFakturace($this->db, $this->container,$this->user);    
}  

public function handlefindAdresByIcoAres($ico=NULL){

    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {        
        $adresar = new Model\Adresar($this->db, $this->container);
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
        $dataObj = $adresar->getFirmaByIcoAres($ico); 
        
            $this->payload->adresy = $dataObj;
   	        $this->sendPayload();
    }
}      
    
public function handlefindAdresByIco($ico=NULL, $icofirma=NULL){

    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {        
        $adresar = new Model\Adresar($this->db, $this->container);
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
        $dataObj = $adresar->getFirmaByIco($userm->getParent($this->user->getId())["subj_id"],$ico, $icofirma); 
        
            $this->payload->adresy = $dataObj;
   	        $this->sendPayload();
    }
}    
    
public function handlegetAdresuByAid($aid){
    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {     
        $adresar = new Model\Adresar($this->db, $this->container);        
        $dataObj = $adresar->getAdresaByAid($aid); 
            $this->payload->dataObj = $dataObj;
   	        $this->sendPayload();
    }
}  
    
public function handlegetSkartuBySid($sid){
    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {     
         $skarty = new Model\Skarty($this->db, $this->container);       
        $dataObj = $skarty->getSkartuBySid($sid); 
            $this->payload->dataObj = $dataObj;
   	        $this->sendPayload();
    }
}        
public function handlegetKidSids($kid){
     if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {     
         $skarty = new Model\Skarty($this->db, $this->container);       
        $dataObj = $skarty->getKidSids((integer)$kid);
            $this->payload->dataObj = $dataObj;
   	        $this->sendPayload();
    }
}    
    
    
    
public function handlepassChange($newPass){

    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
        $chyba = true;
        $result = ($this->MyAuthenticator->changePassword($this->user->id, $newPass));
        switch($result) {
            case 0: 
                $chybatext = "Neočekávaná chyba. Chyba byla zaznamenána a bude odstraněna.";
            break;
            case 2:
                $chybatext = "Chyba v identitě uživatele. Heslo nebylo změněno.";
            break;
            default:
                $chybatext = "Heslo bylo úspěšně změněno.";
                $this->user->getIdentity()->zmenahesla = 0;
                
        }    
            
            $this->payload->chyba = $chyba;
            $this->payload->chybatext = $chybatext;                            
   	        $this->sendPayload();
    }
}
public function handleLogin($email, $key, $pass)
     {	
        if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
		$credentials = array(); 
	   
       $credentials["email"] = $email;
       $credentials["key"] = $key;
       $credentials["password"] = $pass;
       $credentials["uid"] = null;
      	 	
	    $chyba = true;
        $chybatext = "";
    
		$result = $this->MyAuthenticator->authenticate($credentials);	 	
       //$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');			
		 list($status, $identita) = $result;
		
			 switch($status) {
			case 2:
			case 3:
				$chybatext = 'Neevidovaný email nebo chybný klíč, či heslo.';	
				break;
			case 4:
				$chybatext ='Platnost generovaného hesla vypršela, vygenerujte si do emailu nové.';				
				break;
			default:
		 if($identita !==NULL) {
				//!!!!!!!!!!!!!!!!!!!!!!!!!
             
		 $this->user->setExpiration($this->konstanty["loginexpdays"].' days');						 
		 if($this->user->isLoggedIn()	) {			 
		  $this->user->logout(true); //parametr vymaze identitu ze storage
		 }
		 $this->user->login($identita,$pass);  		
			 
		$this->MyAuthorizator->afterLoginRedirect($this->user, $this);//přesměruje po loginu uřiv na správné místo
             $this->payload->chyba = $chyba;
            $this->payload->chybatext = $chybatext;        
            $this->payload->status = $status;            
   	        $this->sendPayload();	
		 //$this->flashMessage('Vítejte, byli jste úspěšně přihlášeni.');	
		  $chyba = false;
		 }	
   	
       }    
            $this->payload->chyba = $chyba;
            $this->payload->chybatext = $chybatext;        
            $this->payload->status = $status;            
   	        $this->sendPayload();	
}}
public function handleLogout()    {
        $this->user->logout(true); //parametr vymaze identitu ze storage
		$this->MyAuthorizator->afterLogoutRedirect($this);
		//$this->flashMessage('Jste odhlášeni. Děkujeme za Váš čas.');
}
    
}