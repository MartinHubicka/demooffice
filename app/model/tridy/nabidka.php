<?php
namespace App\Model\tridy;
class objnabidka { 
const TABLE = "nabidky";
const ROWSTABLE = "nabidky_rows";    
//---
public $idecko;
public $subj_id;
public $zid;
public $uid;
public $participant;
public $dvystaveni;
public $dplatnosti;
public $vystavil;
public $termin = "dohodou";
public $refcislo;
public $poptavka;
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
//public $tagy;  //zde zatímnepoužívám protože tagy neprocházejí editorem
    

public $_DOM="";    //dom radku v dokumentu  
    //--------- nedatabázové property
public $zkraceny_nazev = "NABÍDKA";        
private $db;    
   public function __construct(\Nette\Database\Connection $db, $refcislo = NULL)
    {
       $this->db = $db;
       $this->refcislo = $refcislo;

    }
    public function changeDplatnosti($datumvystaveni, $platnostDny) {
//$datumvystaveni format YYYY-MM-DD
//$splatnostDny integer   
        
   
    return $platnostDny;//date('Y-m-d', strtotime($datumvystaveni. ' + '. abs($platnostDny).' days'));
    }  

    
    
}