<?php
namespace App\Model\tridy;
class objfaktura { 
const TABLE = "faktury";
const ROWSTABLE = "sklad";    
//---
public $idecko;
public $subj_id;
public $zid;
public $uid;
public $participant;
public $druh_dokladu;
public $duzp;
public $dvystaveni;
public $dsplatnosti;
public $fakturoval;
public $ispdp;
public $refcislo;
public $banka="RB (Raiffeisenbank)";    //zatím natvrdo
public $ucet = "962717002/5500";    //zatím natvrdo
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
    
public $_DOM="";    //dom radku v dokumentu  
    //--------- nedatabázové property
public $info_druh_dokladu;    
public function changeDokType($doktyp) { 
     $doktyp = strtoupper(trim($doktyp));
     $druhy_dokumentu = array("FP", "FH", "ZP", "DB"); //možné druhy dokumentu faktury
    if(in_array($doktyp, $druhy_dokumentu)) {
            $this->druh_dokladu = strtoupper(trim($doktyp));
        switch ($this->druh_dokladu) {
            case "FP":                
                $this->info_druh_dokladu = "Daňový doklad - převodem";
            break;    
            case "FH":
                $this->info_druh_dokladu = "Daňový doklad - v hotovosti (EET)";
            break;    
            case "ZP":
                $this->info_druh_dokladu = "Podklad pro zálohovou platbu";
            break;    
            case "DB":
                $this->info_druh_dokladu = "Opravný daňový doklad - dobropis";
            break;                
            default:
                $this->info_druh_dokladu = "chyba-neznámý typ dokumentu";
            break;    
      }  
        return $this->druh_dokladu;
    } else { return false; }
        
    }
    
public function changeDsplatnosti($datumvystaveni, $splatnostDny) {
//$datumvystaveni format YYYY-MM-DD
//$splatnostDny integer    
    return $splatnostDny;// date('Y-m-d', strtotime($datumvystaveni. ' + '. abs($splatnostDny).' days'));
}  
}