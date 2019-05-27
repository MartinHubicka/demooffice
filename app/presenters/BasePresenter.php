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
		$this->user = $user;		
		$this->db = $db;
		$this->url = new Url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");// \Nette\Http\Url;
		$this->tempStorage = $tempStorage;
		$this->username = ''; //identifikace jména uživatele				
		 parent::__construct();
    $this->container = $container;
	}
    public function startup() {
        parent::startup(); //V každém presenteru je NUTNÉ volat startup předka, pokud je startup v presenteru použitý.
    }
	/**
 * @return EuCookiesControl
 */
protected function createComponentEuCookiesControl() {
	
    return new AC\EuCookiesControl($this->container);
}
/**
 * @return subjecttable
 */
protected function createComponentSubjectTable($useUid=NULL) {
		
    return new AC\SubjectTable($this->db, $this->user, $useUid);
}	

	
	
	 public function createComponentLoginForm()
    {
        $form = new NUI\Form;
		 // $form->getElementPrototype()->class('ajax');
        $form->addText('email', 'Email:');		  
        $form->addPassword('password', 'Heslo:');		  
		  $form->addHidden('uid', $this->user->id);       
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
	  	 $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        return $form;
    }
    public function createComponentLogoutForm()
    {
        $form = new NUI\Form;
		  //nemá pole protože mi stačí volat submit při potvrzení odhlášení
		  $form->onSuccess[] = [$this, 'logoutFormSucceeded'];
        //$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        return $form;
    }
    public function createComponentRegistrationForm()
    {
        $form = new NUI\Form;
        $form->addHidden('email2');
		  $form->addHidden('heslo')->setValue(self::password_brutal());	 		   
		  //$form->setAction('/submit.php');
        $form->onSuccess[] = [$this, 'registrationFormSucceeded']; 
        return $form;
    }
	 public function createComponentTemppassForm (){
		  $form = new NUI\Form;
        $form->addHidden('email3');		  
		  $form->addHidden('heslo2')->setValue(self::password_brutal());
		 
		  //$form->setAction('/submit.php');
        $form->onSuccess[] = [$this, 'temppassFormSucceeded']; 
        return $form;
		 
	 }
	public function createComponentChangepasswordForm ()
	{
		$form = new NUI\Form;
		 $form->addPassword('newpassword', 'Nové heslo:');			 
		 $form->addHidden('email');	
		 $form->addHidden('uid', $this->user->id);		
		  $form->onSuccess[] = [$this, 'changepasswordFormSucceeded']; 
		return $form;
	}
	
	public function changepasswordFormSucceeded(NUI\Form $form, \stdClass $values) {
		
		
		$result = $this->MyAuthenticator->changePassword($this->user->id, $values["newpassword"]);
		if($result == 1)	{	   							
				$this->flashMessage('Heslo bylo změněno.');
				$this->MyAuthorizator->afterLoginRedirect($this->user, $this);
				
		} else {
			$this->flashMessage('Heslo nebylo změněno.');			 	 
			
		}		  
		
	}	
	  
    // volá se po úspěšném odeslání formuláře
    public function registrationFormSucceeded(NUI\Form $form, \stdClass $values)
    {
		
		 /*
		 $result 0 - chyba (exeption), 1 - user byl pridan, 2 - duplicita; email existuje
		*/
		 $result = ($this->MyAuthenticator->addUser($values["email2"], $values["heslo"], $role = 'customer'));
		 if($result == 1 ) {
		 	$this->sendRegistrationEmail($form);	 			
		 } elseif ($result == 2) {                 
			 $this->flashMessage('Email '.$values["email2"].' již evidujeme, prosím přihlašte se heslem, nebo si vyžádejte do emailu heslo nové.');			 
   		 	
		 } else {
         $this->flashMessage('Nastaly potíže s ověřením v databázi. Prosím opakujte pokus za chvilku.');			 	 
			
		 }
    }
	public function temppassFormSucceeded(NUI\Form $form, \stdClass $values) {		
		 $result = ($this->MyAuthenticator->generateTemppass($values["email3"], $values["heslo2"]));		
		if($result == 1 ) {		 	
			 $this->sendTemppassEmail($form);	 	
		 } elseif ($result == 2) {               
		   $this->flashMessage('Uvedený email neevidujeme.');			 	 					
		 } else {
         $this->flashMessage('Nastaly potíže s odeslání nového hesla. Prosím opakujte požadavek na obnovu hesla za chvilku.');			 	 			
		 } 			 
	}
	 
	    public function loginFormSucceeded(NUI\Form $form, $values)
     {
				 
		$credentials = array(); 
		foreach($values as $key => $value) {
       $credentials[Strings::normalize($key)] = $value;
      }	 	
	
		$result = $this->MyAuthenticator->authenticate($credentials);	 	
       //$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');			
		 list($status, $identita) = $result;
		
			 switch($status) {
			case 2:
			case 3:
				$this->flashMessage('Neevidovaný email nebo chybné heslo.');	
				break;
			case 4:
				$this->flashMessage('Platnost generovaného hesla vypršela, vygenerujte si do emailu nové.');				
				break;
			default:
		 if($identita !==NULL) {
				//!!!!!!!!!!!!!!!!!!!!!!!!!
		 $this->user->	setExpiration('14 days');						 
		 if($this->user->isLoggedIn()	) {			 
		  $this->user->logout(true); //parametr vymaze identitu ze storage
		 }

		 $this->user->login($identita,$values['password']);  		
			 
			 $this->MyAuthorizator->afterLoginRedirect($this->user, $this);//přesměruje po loginu uřiv na správné místo
		 $this->flashMessage('Vítejte, byli jste úspěšně přihlášeni.');	
		
		 }		
       }
//		 $this->HandleResultStatus($status) ;	  //odešle signál do  ajaxu s indexem statusu
    }
	/*
	public function HandleResultStatus( $status) {
		 $this->payload->value = $status;			
		        if (!$this->isAjax()) {
                $this->redirect('this');
        }
	}
	*/
	
	public function handleFakesignal() {
		//toto musí existovat-hack pro generalnišablonu
	}
	public function handleZmenahesla () {	
		
		
		if (!$this->isAjax()) {
                $this->redirect('this');
        } else {
			$this->payload->value = $this->MyAuthenticator->isTempPass( $this->user->id );			
			$this->sendPayload();				
		}
	}
	
	public function handleUpdateKontakt($html=NULL){		
		if (!$this->isAjax()) {
                $this->redirect('this');
        } else {
		if( $html!==NULL){
			$this->MyAuthenticator->UpdateKontakt($this->user->id, $html);
			$this->payload->result=true;
		} else {
			$this->payload->result=false;			
		}
		$this->sendPayload();								
		}					
	}	
		
	
	public function logoutFormSucceeded (NUI\Form $form) {		
		$this->user->logout(true); //parametr vymaze identitu ze storage
		$this->MyAuthorizator->afterLogoutRedirect($this);
		$this->flashMessage('Jste odhlášeni. Děkujeme za Váš čas.');
	}
	static function password_smart($length = 9) { 
    $vowels = 'aeiou'; 
    $consonants = 'bdghjlmnpqrstvwx'; 
    $password = ''; 
    mt_srand((double)microtime() * 1000000); 
    $alt = mt_rand() % 2; 
    $number = mt_rand() % $length; 
    for ($i = 0; $i < $length; $i++) { 
        if ($number == $i) { 
            $password .= mt_rand() % 9; 
        } else if ($alt == 1) { 
            $password .= $consonants[(mt_rand() % strlen($consonants))]; 
            $alt = 0; 
        } else { 
            $password .= $vowels[(mt_rand() % strlen($vowels))]; 
            $alt = 1; 
        } 
    } 
    return $password; 
} 

static function password_brutal($length = 8, $upper = 2, $digit = 1, $spec = 1) { 
    mt_srand((double)microtime() * 1000000); 
    $count = $length; 
    $sp = '!"#$%&' . "'" . '()*+,-./:;<=>?@[\]^_`{|}~'; 
    $up = !$upper; 
    $password = str_repeat(' ', $length); 
    // spec 
    while ($count && $spec) { 
        $i = mt_rand() % $length; 
        if ($password[$i] == ' ') { 
            $password[$i] = $sp[mt_rand() % strlen($sp)]; 
            $spec--; 
            $count--; 
        } 
    } 
    // digit 
    while ($count && $digit) { 
        $i = mt_rand() % $length; 
        if ($password[$i] == ' ') { 
            $password[$i] = chr(mt_rand(ord('0'), ord('9'))); 
            $digit--; 
            $count--; 
        } 
    } 
    // upper 
    while ($count && $upper) { 
        $i = mt_rand() % $length; 
        if ($password[$i] == ' ') { 
            $password[$i] = chr(mt_rand(ord('A'), ord('Z'))); 
            $upper--; 
            $count--; 
        } 
    } 
    // other 
    for ($i = 0; $i < $length; $i++) { 
        if ($password[$i] == ' ') { 
            $a = ord($up && mt_rand(0, 1) ? 'A' : 'a'); 
            $password[$i] = chr(mt_rand($a, $a + 25)); 
        } 
    } 
    return $password; 
}
	/*
	*/
	/**
		$form - formulář který nastavil data 
		$contextValues - array dotačné informace k zaslání např. emailem
	*/
	private function sendRegistrationEmail (NUI\Form $form, $contextValues = array()){
		$EmailTitle = 'Registrace na ThermoWhite.cz';
		$values = $form->getValues(true) + $contextValues;
		$email  = new Mail\RegistrationMail();
		$message = new  Nette\Mail\Message;	
		$message->addTo($values['email2'])
        		->setFrom($email::FROM);		
					
		$template = $this->createTemplate();	   
    	$template->setFile(__DIR__ . '/templates/mail/RegistrationMail.latte');
		$template->title = $EmailTitle;
    	$template->values = $values;
	
	/*	var_dump($this->rootDir->getwwwDir().'\IM\demo-man.jpg');
		die();*/
		//$message->addAttachment($this->rootDir->getwwwDir().'\IM\demo-man.jpg');		
		//$message->addEmbeddedFile($this->rootDir->getwwwDir().'\IM\demo-man.jpg');
		
		$message->setSubject($EmailTitle)			
				->setHtmlBody($template);
			
			//->setHtmlBody('<h1>Registrace na  ThermoWhite.cz</h1> <img src="../../IM/demo-man.jpg">');
		
		if($email->sendEmail($message)) {
		$this->flashMessage('Na uvedený email, Vám byly zaslány regitrační údaje.');			 	 
			
		}
	}
	
	private function sendTemppassEmail (NUI\Form $form, $contextValues = array()) {
		$EmailTitle = 'Obnova hesla na ThermoWhite.cz';
		$values = $form->getValues(true) + $contextValues;		
		$email  = new Mail\TempPassMail();
		$message = new  Nette\Mail\Message;	
		$message->addTo($values['email3'])
        		->setFrom($email::FROM);		
					
		$template = $this->createTemplate();	   
    	$template->setFile(__DIR__ . '/templates/mail/TemppassMail.latte');
		$template->title = $EmailTitle;
    	$template->values = $values;
		
				$message->setSubject($EmailTitle)			
				->setHtmlBody($template);
		
		if($email->sendEmail($message)) {
		$this->flashMessage('Na uvedený email, Vám bylo zasláno dočasné heslo. Pozor, platnost hesla je 30 minunt.');			 	 			
		}		
		
	}
	/**
     * Sign-up form factory.
     * @return Nette\Application\UI\Form
     */
	/*
    protected function createComponentSignUpForm() {
        return $this->signUpFactory->create(function () {
            $token = Random::generate(32);
            $template = $this->templateFactory->createTemplate();
            //$template->setFile('App/Emails/Messages/activation.latte');
            $template->verifyLink = $this->linkGenerator->link('Global:activationAccount', ['token' => $token]);
            $mail = new Message();
            $mail->addTo('veronika.hubickova@centrum.cz');
            $mail->setSubject('Registrace na ThermoWhite.cz');
            $mail->setHtmlBody((string) $template, __DIR__ . '/../../assets/global/img/');
            $this->sendMailFactory->sendEmail($mail, $template);
            $this->redirect('this');
        });
    }*/
	
}