<?PHP
namespace App\Model;
use Nette\Database\Connection;
use Latte\Engine;
use Nette\Utils;
use Nette\Application;

class Fakturace extends BaseModel {
 /** @var NS\User */
public $user;	
    
public function getFaktury(\Nette\Security\user $user, $subjid = NULL, $arrayFilter = []) {
  $this->user = $user;
    $faktury = NULL;
  if($subjid!==NULL) {                
    //todo zapracovat filter
  $res = $this->db->fetchAll("SELECT *, (SELECT SUM(vydej_mj*cena_mj*(1+dph/100)) FROM sklad WHERE sklad.refcislo = CONCAT(faktury.druh_dokladu,faktury.refcislo) GROUP BY sklad.refcislo ) AS celkem FROM faktury WHERE subj_id = ? ORDER BY dvystaveni DESC,refcislo DESC  " ,  $subjid);
        if($res) {            
            $faktury = (object)$res;
        }  
       
   } else {       
       // uživatel nemá oprávnění
       throw new \Nette\Application\ForbiddenRequestException;       
   }
    return $faktury;         
}  
    
public function getDokument(\Nette\Security\user $user, $subjid = NULL, $druh = NULL, $ref = NULL, $rowsArray = false) {
    //$rowsArray = false umožnuje v případě true vrátit v $faktura->_DOM array se řádky v případě pokud checme předat pouze data
   $this->user = $user;
    if($subjid && $druh) {
    //user je přořazen k subjektu a je předán druh faktury/dokumentu
       $faktura = new tridy\objfaktura;
        
        if(!$ref){
        // nový dokument
            $faktura->duzp = date("Y-m-d");
            $faktura->dvystaveni = $faktura->duzp;
            $faktura->druh_dokladu = $faktura->changeDokType($druh);
            $faktura->subj_id = $subjid;            
            $faktura->dsplatnosti = $faktura->changeDsplatnosti($faktura->dvystaveni, $this->konstanty["defsplatnostdny"]);
            $faktura->uid = $this->user->id;
            $faktura->fakturoval = $this->user->getIdentity()->username;
            //---
            $subject = new \App\Model\Subject($this->db, $this->container);        
            $subj_data = $subject->getArrAttributeBySid($subjid);
            if($subj_data) {            
                $faktura->FIRMAD = $subj_data["name"];
                $faktura->ICOD = $subj_data["ico"];
                $faktura->DICD = $subj_data["dic"];
                $faktura->MESTOD = $subj_data["mesto"];
                $faktura->MISTOD = $subj_data["misto"];
                $faktura->PODNAZEVD = $subj_data["podnazev"];
                $faktura->PSCD = $subj_data["psc"];
                $faktura->STATD = $subj_data["stat"];
                $faktura->ULICECPD = $subj_data["ulicecp"];
            }
        } else {        
        // požadován existující dokument, pokud je potvrzeno vlastnictví (a existuje-li) provede se load dokumentu                    
          $res = $this->db->fetchAll("SELECT * FROM faktury WHERE subj_id = ? AND refcislo = ? AND druh_dokladu = ? " ,  $subjid, $ref, $druh);        
     
        if($res){
            $res= $res[0]; //první záznam - vžy bude jen jeden hack fetchAll
            $faktura->idecko = $res->idecko;
            $faktura->refcislo = $res->refcislo;
            $faktura->subj_id = $res->subj_id;
            $faktura->uid = $res->uid;
            $faktura->druh_dokladu = $res->druh_dokladu;      
            $faktura->changeDokType($res->druh_dokladu);
                $_date=date_create($res->duzp);                         
            $faktura->duzp = date_format($_date,"d.m.Y");
                $_date=date_create($res->dvystaveni);                         
            $faktura->dvystaveni = date_format($_date,"d.m.Y");                                    
            $faktura->dsplatnosti = $faktura->changeDsplatnosti($faktura->dvystaveni, $res->dsplatnosti);            
            $faktura->fakturoval =  $res->fakturoval;
            $faktura->banka =  $res->banka;
            $faktura->ucet =  $res->ucet;
            $faktura->ispdp =  ($res->ispdp<>0) ? true : false;
            $faktura->objednavka =  $res->objednavka;
             $faktura->ICOD = $res->ICOD;
             $faktura->ICOO = $res->ICOO;
             $faktura->ICOM = $res->ICOM;
             $faktura->DICD = $res->DICD;
             $faktura->DICO = $res->DICO;
             $faktura->DICM = $res->DICM;
             $faktura->FIRMAD = $res->FIRMAD;
             $faktura->FIRMAO = $res->FIRMAO;
             $faktura->FIRMAM = $res->FIRMAM;
             $faktura->PODNAZEVD = $res->PODNAZEVD;
             $faktura->PODNAZEVO = $res->PODNAZEVO;
             $faktura->PODNAZEVM = $res->PODNAZEVM;
             $faktura->ULICECPD = $res->ULICECPD;
             $faktura->ULICECPO = $res->ULICECPO;
             $faktura->ULICECPM = $res->ULICECPM;
             $faktura->MISTOD = $res->MISTOD;
             $faktura->MISTOO = $res->MISTOO;
             $faktura->MISTOM = $res->MISTOM;
             $faktura->PSCD = $res->PSCD;
             $faktura->PSCO = $res->PSCO;
             $faktura->PSCM = $res->PSCM;
             $faktura->MESTOD = $res->MESTOD;
             $faktura->MESTOO = $res->MESTOO;
             $faktura->MESTOM = $res->MESTOM;
             $faktura->STATD = $res->STATD;
             $faktura->STATO = $res->STATO;
             $faktura->STATM = $res->STATM;
             $faktura->TEXTY =  $res->TEXTY;  
         $html = ($rowsArray==true) ? NULL : '';
             // sestavíme dom řádků
            $res = $this->db->fetchAll("SELECT * FROM sklad WHERE refcislo = ? ORDER BY row_index ASC " , $druh.$ref );                
        if($res) {
            if($rowsArray==true) {
               $html = $res; 
            } else {
             foreach($res as $row) {
             $html .= '
<tr class="noCSS_dokument_row" vydejka="'.$row->vydejka.'" idecko="'.$row->idecko.'" sid="'.$row->sid.'" druh="'.$row->druh.'">
<td>
<div class="btn-group" role="group" aria-label="move-row"><button type="button" class="btn btn-light btn-sm noCSS_move_up">&#8593;</button><button type="button" class="btn btn-light btn-sm noCSS_move_down">&#8595;</button></div>
</td>
<td contenteditable="true" class="noCSS_col_field text-right" field="skarta">'.$row->skarta.'</td>
<td contenteditable="true" class="noCSS_col_field" field="popis">'.$row->popis.'</td>
<td contenteditable="true" class="noCSS_col_field text-center noCSS_number" format="0.00" selectOnFocus field="vydej_mj">'.number_format($row->vydej_mj,2, '.', '').'</td>
<td contenteditable="true" class="noCSS_col_field text-center" field="mj">'.$row->mj.'</td>
<td contenteditable="true" class="noCSS_col_field text-right noCSS_number" selectOnFocus format="0.00" field="cena_mj">'.number_format($row->cena_mj,2 , '.', '').'</td>
<td contenteditable="true" class="noCSS_col_field text-center noCSS_number" selectOnFocus format="0" field="dph">'.number_format($row->dph,0, '.', '').'</td>
<td class="noCSS_row_cena text-right noCSS_number" format="0.00"></td>
<td><button type="button" class="btn btn-light btn-sm noCSS_row_remove">x</button></td>
</tr>             
             ';                     
             }
           } 
        } 
              $faktura->_DOM = $html;
          
            
            
        } else {
       // uživatel nemá oprávnění nebo neexistuje dokument 
       throw new \Nette\Application\ForbiddenRequestException; //není oprávnění
     //   throw new \Nette\Application\BadRequestException; // neexistuje dokument
        }
        }
       
   } else {       
       // uživatel nemá oprávnění
       throw new \Nette\Application\ForbiddenRequestException; 
   }
    if($faktura) {
        $faktura->changeDokType($faktura->druh_dokladu);        
    }
    

    return (object)$faktura;
}
public function getDokumentById(\Nette\Security\user $user, $subjid = NULL, $idecko = NULL) {

if($subjid===NULL|| $idecko ===NULL ){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id nebo id dokumentu. [model->Faktura]->getDokumentById';
    $this->result->data = null;            
    return $this->result;
} else {       
         $res = $this->db->fetch("SELECT druh_dokladu, refcislo FROM faktury WHERE idecko = ? AND subj_id = ?" , $idecko, $subjid );                
        
        if($res) {            
           $dokument =  $this->getDokument($user, $subjid, $res->druh_dokladu, $res->refcislo, true);    
        } else {
           $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyly nalezeny záznamy podle id dokladu. [model->Faktura]->getDokumentById';
    $this->result->data = null;                    
    return $this->result;
        }
    return $dokument; // máme kompletní fakturu včetně array řádku v parametru $dokument->_DOM;
}
}
public function saveData(\Nette\Security\user $user, $subjid = NULL, $arrHlavicka, $arrRows) {
    if($subjid===NULL){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id. [Faktura]->saveData';
    $this->result->data = null;            
} else {
    $arrHlavicka["subj_id"]= $subjid;    
    list($den, $mesic, $rok) = explode(".", $arrHlavicka["duzp"]);
    $arrHlavicka["duzp"] = $rok."-".$mesic."-".$den;    
    list($den, $mesic, $rok) = explode(".", $arrHlavicka["dvystaveni"]);
    $arrHlavicka["dvystaveni"] = $rok."-".$mesic."-".$den;        
    $arrHlavicka["uid"]= $user->id;
    $arrHlavicka["fakturoval"]= $user->getIdentity()->username;    
    $arrHlavicka["ispdp"] = ($arrHlavicka["ispdp"]===true || $arrHlavicka["ispdp"]== "true" || $arrHlavicka["ispdp"]== 1 || $arrHlavicka["ispdp"]== "1") ? 1 : 0;
    
         $subj = new \App\Model\Subject($this->db, $this->container);  
        
  if(!$arrHlavicka["refcislo"]) {
   //nový záznam      
      //získání referenčního čísla nového dokumentu
             
        
        //$arrHlavicka["refcislo"]= $subj->getFreeRefNumber($subjid,"faktury");
        
        $arrHlavicka["refcislo"]= $subj->getFreeRefNumber($subjid,"faktury");
    $this->db->query('INSERT INTO faktury', $arrHlavicka);
    $id = $this->db->getInsertId();
        if($id) {
            $groupvydejka = $subj->getFreeRefNumber($subjid,"vydejky");
    //uložíme řádky        
        $rowindex = 0;
            if(count($arrRows)>0) {
        foreach($arrRows as $row) {
                        
                  $row["row_index"] = $rowindex;
            
                  $row["vydejka"] =   $groupvydejka ;
                    
                $row["subj_id"] = $subjid; 
                    $row["refcislo"] = $arrHlavicka["druh_dokladu"] . $arrHlavicka["refcislo"]; 
                    $row["datum"] =  $arrHlavicka["duzp"];
                    //$row["kodfakturace"] = $arrHlavicka["kodfakturace"]
         
         /*foreach($row as $field=>$value) {
                //var_dump($field . "=>" . $value);
         }*/
            
            $this->db->query('INSERT INTO sklad', $row);
            
            $rowindex++;
        }  }  
            
              $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Dokument byl úspěšně uložen !';
    $this->result->data = ['idecko' => $id,'refnr' => $arrHlavicka["refcislo"]];  
            
            
        } else {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při uložení hlavičky faktury';
    $this->result->data = null;        
        }
   
      
      
  } else {
   

      
    // update záznamu hlavičky faktury
      $recordsUpdated =  $this->db->query('UPDATE faktury SET', 
        $arrHlavicka,
        'WHERE idecko = ?', $arrHlavicka["idecko"]);
      
    //uložíme řádky  
    // tři fáze,
    // má-li řádek idecko = update záznamu vč. row indexu ale ne idecka
    // nemá-li řádek idecko = insert záznamu
    // má-li faktura záznam v db a nebyl předán zánam = delete těchto záznamů 
        
      
      //získám idecka před uložením dokumentu
        $res = $this->db->fetchAll("SELECT idecko, vydejka FROM sklad WHERE refcislo = ? " , $arrHlavicka["druh_dokladu"] . $arrHlavicka["refcislo"]);                
         $puvodniIDS = [];
      $groupvydejka = NULL;
        if($res) {
         foreach($res as $row) {
            $puvodniIDS[] = $row["idecko"]*1;//pozor na typ proměnné misí být integer
            $groupvydejka =  $row["vydejka"];
         }
        }

        
        $rowindex = 0;
        
            if(count($arrRows)>0) {
        foreach($arrRows as $row) {
                $row["row_index"] = $rowindex;   
                $row["subj_id"] = $subjid;      
                $row["refcislo"] = $arrHlavicka["druh_dokladu"] . $arrHlavicka["refcislo"]; 
                $row["datum"] =  $arrHlavicka["duzp"];
                // todo výdejka  $row["výdejka"]
        
                if( $row["vydejka"] ===  NULL || $row["vydejka"] =="" ) {
                    if($groupvydejka === NULL) {
                    $groupvydejka = $subj->getFreeRefNumber($subjid,"vydejky");    
                    }
                    
                    $row["vydejka"] = $groupvydejka;
                    } 
                if($row["idecko"]=="") { //nový záznam
                  unset($row["idecko"]);  
                    
                    $this->db->query('INSERT INTO sklad', $row);  
                    
                } else {
                    $this->db->query('UPDATE sklad SET', 
                        $row,
                    'WHERE idecko = ?', $row["idecko"]*1);         
                  //ppokud je puvodni idecko putvrzeno updatem odebereme ze seznamu původních - tím nám zbydou jen ty k vymazání 
                 
                    $key = array_search($row["idecko"]*1, $puvodniIDS);
                    if ($key!==false || $key!==NULL) { unset($puvodniIDS[$key]); }                


        }
                  $rowindex++;
                 } 
    
            }
         //vymazání záznamů, který byly odebrány před uložením - musí být až po smyčce rows     
        $this->db->query('DELETE FROM sklad WHERE ?or', ['idecko' => $puvodniIDS]);    
            
      /*var_dump($arrRows);
      die();
      */
                    $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Dokument byl úspěšně uložen !';
    $this->result->data = ['idecko' =>  $arrHlavicka["idecko"],'refnr' => $arrHlavicka["refcislo"]];  
      
  }
   
        
        
}
    return $this->result;
    
}
}
