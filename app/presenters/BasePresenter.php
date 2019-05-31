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
    protected $parameters;	
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
      //  $this->parameters = $container->getParameters()["konstanty"];
		 parent::__construct();
    $this->container = $container;
	}
    public function startup() {
        parent::startup(); //V každém presenteru je NUTNÉ volat startup předka, pokud je startup v presenteru použitý.
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
            $chyba = false;
            
        } else {
            $chybatext = "Email nebo klíč byly zadány v nepřípustném formátu.";            
        }
    $this->payload->chyba = $chyba;
    $this->payload->chybatext = $chybatext;        
    $this->payload->result = $result;            
   	$this->sendPayload();			   
    }    
}

	
}