<?php
namespace App\Model;
use Nette\Security\IAuthenticator;
use Nette\Database\Connection;
use Nette\Security\Passwords;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
class MyAuthenticator extends BaseModel  implements IAuthenticator 
{

	 
	 const EXPIRYMIN = 60; // expiration pro temp heslo v minutách 
	 const TABLE_USER = 'users';	
	 const USERS_UID = 'uid'; //auto incr int(11) 
	 const USERS_USEREXP = 'userexp'; //NULL - platný uživatel nebo time expirace uzivatele 
	 const USERS_PARENT = 'parent'; //int(11)
	 const USERS_EMAIL = 'email'; //string
	 const USERS_HESLO = 'heslo'; // hash strinf
	 const USERS_HESLOEXP = 'hesloexp'; // time
	 const USERS_HESLOZMENA = 'heslozmena'; // int 0/1 true false musí změnit
	 const USERS_ROLE = 'role'; // string (array in string) ?
	 const USERS_LABELS = 'labels'; // sting/y oddělené čárkou
	 const USERS_REGISTRACE = 'registrace'; // datum registrace unix time
	 const USERS_SOUHLAS_GDPR = 'souhlas_gdpr'; // string  text verze gdpr


	/**
	* @param array //email password uid
	* @return array	
	*/
    public function authenticate(array $credentials)
    {
	
		 //bohužel $credentials dělalo problém s textovým klíčem proto indexováno integerem
		 // 0- uid 1 - password
		 //array(3) { ["email"]  ["password"] ["uid"] }
		if (!defined('AUTH_EXCEPTION')) define('AUTH_EXCEPTION', 0);  // chyba pro try krtere tady není ... já vím :)
		if (!defined('AUTH_OK')) define('AUTH_OK', 1); // správné údaje
		if (!defined('AUTH_OK_PASSCHANGE')) define('AUTH_OK_PASSCHANGE', 5); // správné údaje ale s požadavkem na změnu hesla
		if (!defined('AUTH_NOUSER')) define('AUTH_NOUSER', 2);  // neplatnáý úživatel resp. email 
		if (!defined('AUTH_BADPASS')) define('AUTH_BADPASS', 3);  // chybné heslo 
		if (!defined('AUTH_PASSEXPIRY')) define('AUTH_PASSEXPIRY', 4);  // expirované temp heslo
      
		 
		 $row = $this->db->fetch("SELECT *  FROM ".self::TABLE_USER." WHERE ("
										  .self::USERS_EMAIL. " = ? OR " .self::USERS_UID. " = ? ) AND (
										  ".self::USERS_EMAIL." <> '' AND UID > 0) AND (userexp is NULL OR userexp > ?)",
										 $credentials['email'], $credentials['uid'], time());
	
        if (!$row) {
           // throw new AuthenticationException('User not found.');
			
			  return [AUTH_NOUSER,NULL] ;
        }

        if (!Passwords::verify($credentials["password"], $row->heslo)) {
			
          //  throw new AuthenticationException('Invalid password.');
		
			  return [AUTH_BADPASS, NULL];
        }
		 if($row->hesloexp !== NULL && $row->hesloexp < time() ) {
			   //throw new AuthenticationException('Temp password expiry');
			 return [AUTH_PASSEXPIRY, NULL];
		 }	
	 
		 //zde nevracím AUTH_OK ale pole s názvem uživatele		 
		 //construct( mixed $id, mixed $roles = null, iterable $data = null )
		 
        $identita =  new Identity($row->uid,(array) explode(",",$row->role), ['username' => 'Uživatel ID:'.$row->uid, 
																								'zmenahesla' => $row->heslozmena, 		
																								'email' => $credentials["email"], 
																								'heslo' => $credentials["password"], 
																								'parentid' => $row->parent, 
																								'label' => (array) explode(",",$row->labels)]);			 		 
		$tempstatus = ( $row->heslozmena > 0)  ? AUTH_OK_PASSCHANGE  : AUTH_OK;
		return [$tempstatus, $identita];
    }
    
    public function checkUserRecord($email,$key) {
        		if (!defined('DUPLICITY_EXCEPTION')) define('DUPLICITY_EXCEPTION', 0);  // chyba
		if (!defined('DUPLICITY_NODUPLICITY')) define('DUPLICITY_NODUPLICITY', 1); // není duplicitní je to ok
		if (!defined('DUPLICITY_DUPLICITY')) define('DUPLICITY_DUPLICITY', 2);  // je duplicitní 
        try {			            
            $rec = $this->db->fetch("SELECT COUNT(*) AS CNT FROM users WHERE email = ? AND klic = ?",  $email, $key);            
			return ($rec->CNT > 0) ? DUPLICITY_DUPLICITY : DUPLICITY_NODUPLICITY;		
		} catch (Nette\Database\Exception $e) {
			throw new Exception;
			return DUPLICITY_EXCEPTION;
		}		
    }
    
    /**
	 *	kontrola, zda existuje záznam/y v dané tabulce dle daného fieldu a hodnoty
	 * param u $value je třeba typ hodnoty, např. string předat obalený v uvozovkách
	 * @param string
	 * @param string
	 * @param int
	 * @return boolean true je ok false problém
	 */
	public function checkRecordDupplicity($table, $checkField, $value)  {
		//pozor tuto funkci lze také chápat jako existuje neexistuje záznam
		if (!defined('DUPLICITY_EXCEPTION')) define('DUPLICITY_EXCEPTION', 0);  // chyba
		if (!defined('DUPLICITY_NODUPLICITY')) define('DUPLICITY_NODUPLICITY', 1); // není duplicitní je to ok
		if (!defined('DUPLICITY_DUPLICITY')) define('DUPLICITY_DUPLICITY', 2);  // je duplicitní 
		try {			
			$rec = $this->db->fetch("SELECT COUNT(*) AS CNT FROM ".$table." WHERE ".$checkField." = ?",  $value);
			return ($rec->CNT > 0) ? DUPLICITY_DUPLICITY : DUPLICITY_NODUPLICITY;		
		} catch (Nette\Database\Exception $e) {
			throw new Exception;
			return DUPLICITY_EXCEPTION;
		}		
		/* MYSLENKA
		try{
    $con = new PDO( DB_HOST, DB_USER, DB_PASS );
    $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    $sql = "SELECT * FROM candidates WHERE firstnaame = :firstnaem AND surname = :surname";
    $stmt = $con->prepare( $sql );
    $stmt->bindValue( "firstnaame", $this->firstnamee, PDO::PARAM_STR );
    $stmt->bindValue( "surname", $this->surname, PDO::PARAM_STR );
    $stmt->execute();
    $result = $stmt -> fetchAll();
    echo $this->convertToJson($result);
}catch (PDOException $e) {
    echo $e->getMessage();
    return false;
}*/
	}

    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @param  string
     * @return int
     * @throws DuplicateNameException
     */
    public function addUser($email, $password, $role = 'customer') {
		 if (!defined('ADDUSER_EXCEPTION')) define('ADDUSER_EXCEPTION', 0); //chyba
		 if (!defined('ADDUSER_SUCCESS')) define('ADDUSER_SUCCESS', 1);  //ok
		 if (!defined('ADDUSER_DUPLICITY')) define('ADDUSER_DUPLICITY', 2); // user již existuje v db
		 $duplicity = $this->checkRecordDupplicity(self::TABLE_USER, self::USERS_EMAIL, $email);
		 if( $duplicity == 1) { // pokud neníchyba nebo duplicita
		 try {
			  $expiry = strtotime("+".$this->konstanty["temppassmin"]." minutes", time());
			 $fields = [
				 			self::USERS_EMAIL => $email,
			 			   self::USERS_HESLO  =>  Passwords::hash($password), 
				  			self::USERS_HESLOEXP  => $expiry,
				 			self::USERS_HESLOZMENA  => 1,
                		self::USERS_ROLE  => $role,
				    		self::USERS_REGISTRACE  => time(),
				    		self::USERS_SOUHLAS_GDPR  => 'debug'
			 			   ]; 			 
			  $this->db->query("INSERT INTO ". self::TABLE_USER, $fields);
				  return ADDUSER_SUCCESS;				  
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
             throw new DuplicateNameException;
			 	 return ADDUSER_EXCEPTION;
        } 
		 } else {			 
			 return $duplicity;
		 }
    }
	
	public function changePassword($uid, $password){
		if (!defined('CHANGEPASS_EXCEPTION')) define('CHANGEPASS_EXCEPTION', 0); //chyba
		if (!defined('CHANGEPASS_SUCCESS')) define('CHANGEPASS_SUCCESS', 1);  //ok
		if (!defined('CHANGEPASS_NOUSER')) define('CHANGEPASS_NOUSER', 2);  // user není nastaven
		if(isset($uid)) {
			  $fields = [				 			
			 			   self::USERS_HESLO  =>  Passwords::hash($password), 
				  			self::USERS_HESLOEXP  => NULL,
				 			self::USERS_HESLOZMENA  => 0,                						    		
			 			   ];  
				try {
			$this->db->query("UPDATE ". self::TABLE_USER ." SET ", $fields, " WHERE " . self::USERS_UID . " = ?",  $uid);				  					   
			return CHANGEPASS_SUCCESS;
				} catch (Nette\Database\Exception $e) {
			return CHANGEPASS_EXCEPTION;
				} 		
	} 
				return CHANGEPASS_NOUSER;		
	}
	
	public function generateTemppass ($email, $key) {
		if (!defined('TEMPPASS_EXCEPTION')) define('TEMPPASS_EXCEPTION', 0); //chyba
		if (!defined('TEMPPASS_SUCCESS')) define('TEMPPASS_SUCCESS', 1);  //ok
		if (!defined('TEMPPASS_NOUSER')) define('TEMPPASS_NOUSER', 2);  //email neexistuje
		$duplicity = $this->checkUserRecord($email,$key);         
		if($duplicity==2) { //email existuje
		try {
             $password = $this->password_brutal();
			 $expiry = strtotime("+".$this->konstanty["temppassmin"]." minutes", time());
			 $fields = [				 			
			 			    self::USERS_HESLO  =>  Passwords::hash($password), 
				  			self::USERS_HESLOEXP  => $expiry,
				 			self::USERS_HESLOZMENA  => 1,                						    		
			 			   ]; 			 
			$this->db->query("UPDATE ". self::TABLE_USER ." SET ", $fields, " WHERE email = ? AND klic = ?" ,  $email, $key);				  
            $mail = new EmailModel($this->db, $this->container);
            $mail->sendTempPass($email, $password);
            
			 return TEMPPASS_SUCCESS;				  
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
             throw new DuplicateNameException;
			 	 return TEMPPASS_EXCEPTION;
        } 
		}	else {
			return TEMPPASS_NOUSER;			
		}
	}
	
	public function getUser($uid=NULL) {
		if($uid && $uid!==NULL) {
		$rec = $this->db->fetch("SELECT * FROM ".self::TABLE_USER." WHERE uid = ?",  $uid);
		}
		return $rec;
	}	
	public function getParent($uid=NULL){		
		if($uid && $uid!==NULL) {
		$rec = $this->db->fetch("SELECT * FROM subjects WHERE subj_id = (SELECT parent FROM users WHERE uid = ?)",  $uid);		
		return $rec;
		}
	}
	
	/**
	* @param mixed
	* @return boolean
	*/
	public function isTempPass ($userID) {
			try { 
				if($userID !== NULL) {
				$rec = $this->db->fetch("SELECT heslozmena  FROM users WHERE uid = ?",  $userID);
			return ($rec->heslozmena > 0) ? true : false;						
					} else {
				return false;						
				}
			}	catch (Nette\Database\UniqueConstraintViolationException $e) {
				return false;			
		   }
		
	}
	public function updateParent($userID=NULL, $parentID=NULL){
					try { 
				if($userID !== NULL || $parentID !== NULL) {
				$this->db->query("UPDATE ".self::TABLE_USER." SET ".self::USERS_PARENT." = ? WHERE uid = ?",  $parentID,$userID);
			   return  true;						
					} else {
				return false;						
				}
			}	catch (Nette\Database\UniqueConstraintViolationException $e) {
				return false;			
		   }
		
	}
public function getPersonalData($uid = NULL)	 {
	if($uid!==NULL) {
		$res = $this->db->fetch("SELECT * FROM ".self::TABLE_USER." WHERE uid = ?", $uid);
		return [$res];
	} 	
}

public function UpdateKontakt($uid=NULL, $html=""){
	if($uid!==NULL ) {		
		$res = $this->db->query("UPDATE ".self::TABLE_USER." SET kontakt = ? WHERE uid = ?", $html, $uid);
		
		return $res;
	} 	
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
class DuplicateNameException extends \Exception{}
