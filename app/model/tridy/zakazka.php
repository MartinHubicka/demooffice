<?php
/* todo předělat na továrnu */
namespace App\Model\tridy;
class objzakazka { 
const TABLE = "zakazky";
const ROWSTABLE = "objednavky_rows"; 
const ROWSTABLESKLAD = "sklad"; 
//---
public $idecko;
public $subj_id;
public $uid;
public $participant;
public $zamek; // true/false  1/0
public $dobjednani;
public $vystavil;
public $zpuhrady;
public $terminod;
public $termindo;	
public $nid;
public $refcislo;
public $objednavka;
public $ICOD;
public $ICOO;
public $ICOM;
public $DICD;
public $DICO;
public $DICM;
public $FIRMAD;
public $FIRMAO;
public $FIRMAM;
public $PODNAZEVD;
public $PODNAZEVO;
public $PODNAZEVM;
public $ULICECPD;
public $ULICECPO;
public $ULICECPM;
public $MISTOD;
public $MISTOO;
public $MISTOM;
public $PSCD;
public $PSCO;
public $PSCM;
public $MESTOD;
public $MESTOO;
public $MESTOM;
public $STATD;
public $STATO;
public $STATM;
public $TEXTY;
public $priv_note;
public $public_note;
public $tagy;

//public $tagy;  //zde zatímnepoužívám protože tagy neprocházejí editorem
public $_DOM_SKLAD="";    //dom radku v fakturovaných položek nebo položek k fakturaci 
public $_DOM_OBJEDNAVKA="";    //dom radku v zakazky  - kompatibilita s řádky nabídky
    //--------- nedatabázové property
public $zkraceny_nazev = "ZAKÁZKA - evid.obj.";            
private $db;    
    
    
   public function __construct(\Nette\Database\Connection $db, $refcislo = NULL)
    {
       $this->db = $db;
       $this->refcislo = $refcislo;

    }    
    
}