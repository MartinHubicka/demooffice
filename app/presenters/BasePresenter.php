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

	
}