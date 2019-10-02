<?PHP
namespace App\Model;
use Nette\Database\Connection;
use Latte\Engine;
use Nette\Utils;
use Nette\Application;

class Nabidky extends BaseModel {
 /** @var NS\User */
public $user;	
    
public function getNabidky(\Nette\Security\user $user, $subjid = NULL, $arrayFilter = []) {
  $this->user = $user;
    $nabidky = NULL;
  if($subjid!==NULL) {                
    //todo zapracovat filter
  $res = $this->db->fetchAll("SELECT *, (SELECT SUM(mnozstvi*cena_mj) FROM nabidky_rows WHERE nabidky_rows.nid = nabidky.idecko GROUP BY nabidky_rows.nid ) AS celkem FROM nabidky WHERE subj_id = ? ORDER BY dvystaveni DESC,refcislo DESC  " ,  $subjid);
        if($res) {            
            $nabidky = (object)$res;
        }  
       
   } else {       
       // uživatel nemá oprávnění
       throw new \Nette\Application\ForbiddenRequestException;       
   }

    return $nabidky;         
}  
    
    
public function getDokument(\Nette\Security\user $user, $subjid = NULL, $ref = NULL, $rowsArray = false) {
    //$rowsArray = false umožnuje v případě true vrátit v $nabidka->_DOM array se řádky v případě pokud checme předat pouze data
   $this->user = $user;
    if($subjid) {
    //user je přořazen k subjektu a je předán druh faktury/dokumentu        
       $nabidka = new tridy\objnabidka($this->db,$ref);
        
        if(!$ref){
        // nový dokument            
            $nabidka->dvystaveni = date("Y-m-d");            
            $nabidka->subj_id = $subjid;            
            
            $nabidka->dplatnosti = $nabidka->changeDplatnosti($nabidka->dvystaveni, $this->konstanty["defplatnostnab"]);
            
            $nabidka->uid = $this->user->id;
            $nabidka->vystavil = $this->user->getIdentity()->username;
            //---
            $subject = new \App\Model\Subject($this->db, $this->container);        
            $subj_data = $subject->getArrAttributeBySid($subjid);
            if($subj_data) {            
                $nabidka->FIRMAD = $subj_data["name"];
                $nabidka->ICOD = $subj_data["ico"];
                $nabidka->DICD = $subj_data["dic"];
                $nabidka->MESTOD = $subj_data["mesto"];
                $nabidka->MISTOD = $subj_data["misto"];
                $nabidka->PODNAZEVD = $subj_data["podnazev"];
                $nabidka->PSCD = $subj_data["psc"];
                $nabidka->STATD = $subj_data["stat"];
                $nabidka->ULICECPD = $subj_data["ulicecp"];
            }
        } else {
            
            // požadován existující dokument, pokud je potvrzeno vlastnictví (a existuje-li) provede se load dokumentu                    
        $res = $this->db->fetchAll("SELECT * FROM nabidky WHERE subj_id = ? AND refcislo = ? " ,  $subjid, $ref);        
     
        if($res){
            $res= $res[0]; //první záznam - vžy bude jen jeden hack fetchAll
            $nabidka->idecko = $res->idecko;
            $nabidka->refcislo = $res->refcislo;
            $nabidka->subj_id = $res->subj_id;
            $nabidka->uid = $res->uid;
            $_date=date_create($res->dvystaveni);                         
            $nabidka->dvystaveni = date_format($_date,"d.m.Y");
            $_date=date_create($res->dvystaveni);                                     
            $nabidka->dplatnosti = $nabidka->changeDplatnosti($nabidka->dvystaveni, $res->dplatnosti);            
            $nabidka->vystavil =  $res->vystavil;
            $nabidka->termin =  $res->termin;
             $nabidka->priv_note =  $res->priv_note;
             $nabidka->public_note =  $res->public_note;
            $nabidka->poptavka =  $res->poptavka;
             $nabidka->ICOD = $res->ICOD;
             $nabidka->ICOO = $res->ICOO;
             $nabidka->ICOM = $res->ICOM;
             $nabidka->DICD = $res->DICD;
             $nabidka->DICO = $res->DICO;
             $nabidka->DICM = $res->DICM;
             $nabidka->FIRMAD = $res->FIRMAD;
             $nabidka->FIRMAO = $res->FIRMAO;
             $nabidka->FIRMAM = $res->FIRMAM;
             $nabidka->PODNAZEVD = $res->PODNAZEVD;
             $nabidka->PODNAZEVO = $res->PODNAZEVO;
             $nabidka->PODNAZEVM = $res->PODNAZEVM;
             $nabidka->ULICECPD = $res->ULICECPD;
             $nabidka->ULICECPO = $res->ULICECPO;
             $nabidka->ULICECPM = $res->ULICECPM;
             $nabidka->MISTOD = $res->MISTOD;
             $nabidka->MISTOO = $res->MISTOO;
             $nabidka->MISTOM = $res->MISTOM;
             $nabidka->PSCD = $res->PSCD;
             $nabidka->PSCO = $res->PSCO;
             $nabidka->PSCM = $res->PSCM;
             $nabidka->MESTOD = $res->MESTOD;
             $nabidka->MESTOO = $res->MESTOO;
             $nabidka->MESTOM = $res->MESTOM;
             $nabidka->STATD = $res->STATD;
             $nabidka->STATO = $res->STATO;
             $nabidka->STATM = $res->STATM;
             $nabidka->TEXTY =  $res->TEXTY;  
         $html = ($rowsArray==true) ? NULL : '';
             // sestavíme dom řádků
            $res = $this->db->fetchAll("SELECT * FROM nabidky_rows WHERE nid = ? ORDER BY row_index ASC " , $nabidka->idecko );                
        if($res) {
            if($rowsArray==true) {
               $html = $res; 
            } else {
             foreach($res as $row) {
             $html .= '
<tr class="noCSS_dokument_row" sid="'.$row->sid.'" nid="'.$row->nid.'" rid="'.$row->rid.'">
    <td><div class="btn-group" role="group" aria-label="move-row">
        <button class="btn btn-light btn-sm noCSS_move_up">&#8593;</button>
        <button class="btn btn-light btn-sm noCSS_move_down">&#8595;</button>
        </div>
    </td>
    <td contenteditable="true" class="noCSS_col_field text-right" field="skarta">'.$row->skarta.'</td>
    <td contenteditable="true" class="noCSS_col_field" field="popis">'.$row->popis.'</td>
    <td contenteditable="true" class="noCSS_col_field text-center noCSS_number" format="0.00" selectOnFocus field="mnozstvi">'.$row->mnozstvi.'</td>
    <td contenteditable="true" class="noCSS_col_field text-center" field="mj">'.$row->mj.'</td>
    <td contenteditable="true" class="noCSS_col_field text-right noCSS_number" selectOnFocus format="0.00" field="cena_mj">'.$row->cena_mj.'</td>
    <td class="noCSS_row_cena text-right noCSS_number" format="0.00"></td>
    <td><button class="btn btn-light btn-sm noCSS_row_remove">x</button></td>
</tr>
             ';                     
             }
           } 
        } 
              $nabidka->_DOM = $html;
          
            
            
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
    
    
      return (object)$nabidka;   
}
 
    
public function saveData(\Nette\Security\user $user, $subjid = NULL, $arrHlavicka, $arrRows) {
    if($subjid===NULL){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id. [Nabidka]->saveData';
    $this->result->data = null;            
} else {
    $arrHlavicka["subj_id"]= $subjid;    
    list($den, $mesic, $rok) = explode(".", $arrHlavicka["dvystaveni"]);
    $arrHlavicka["dvystaveni"] = $rok."-".$mesic."-".$den;        
    $arrHlavicka["uid"]= $user->id;
    $arrHlavicka["vystavil"]= $user->getIdentity()->username;        
    
    $subj = new \App\Model\Subject($this->db, $this->container);  
        
  if(!$arrHlavicka["refcislo"]) {
   //nový záznam      
      //získání referenčního čísla nového dokumentu
             
        
        //$arrHlavicka["refcislo"]= $subj->getFreeRefNumber($subjid,"faktury");
        
        $arrHlavicka["refcislo"]= $subj->getFreeRefNumber($subjid,"nabidky");
    $this->db->query('INSERT INTO nabidky', $arrHlavicka);
    $id = $this->db->getInsertId();
        if($id) {            
    //uložíme řádky        
        $rowindex = 0;
            if(count($arrRows)>0) {
        foreach($arrRows as $row) {
                        
                  $row["row_index"] = $rowindex;
                  $row["subj_id"] = $subjid; 
                  $row["nid"] = $id; 
            
            $this->db->query('INSERT INTO nabidky_rows', $row);
            
            $rowindex++;
        }  }  
            
              $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Dokument byl úspěšně uložen !';
    $this->result->data = ['idecko' => $id,'refnr' => $arrHlavicka["refcislo"]];  
            
            
        } else {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při uložení hlavičky nabídky';
    $this->result->data = null;        
        }
   
      
      
  } else {
   

      
    // update záznamu hlavičky faktury
      $recordsUpdated =  $this->db->query('UPDATE nabidky SET', 
        $arrHlavicka,
        'WHERE idecko = ?', $arrHlavicka["idecko"]);
      
    //uložíme řádky  
    // tři fáze,
    // má-li řádek idecko = update záznamu vč. row indexu ale ne idecka
    // nemá-li řádek idecko = insert záznamu
    // má-li faktura záznam v db a nebyl předán zánam = delete těchto záznamů 
        
      
      //získám idecka před uložením dokumentu
        $res = $this->db->fetchAll("SELECT rid FROM nabidky_rows WHERE nid = ? " , $arrHlavicka["idecko"]);                
         $puvodniIDS = [];
     
        if($res) {
         foreach($res as $row) {
            $puvodniIDS[] = $row["rid"]*1;//pozor na typ proměnné misí být integer     
         }
        }

        
        $rowindex = 0;
        
            if(count($arrRows)>0) {
        foreach($arrRows as $row) {
                $row["row_index"] = $rowindex;   
                $row["subj_id"] = $subjid;                          
            
            

                if($row["rid"]=="") { //nový záznam
                  unset($row["rid"]);  
                    
                    $row["nid"] =  $arrHlavicka["idecko"];
                    $this->db->query('INSERT INTO nabidky_rows', $row);  
                    
                } else {
                    $this->db->query('UPDATE nabidky_rows SET', 
                        $row,
                    'WHERE rid = ?', $row["rid"]*1);         
                  //ppokud je puvodni idecko putvrzeno updatem odebereme ze seznamu původních - tím nám zbydou jen ty k vymazání 
                 
                    $key = array_search($row["rid"]*1, $puvodniIDS);
                    if ($key!==false || $key!==NULL) { unset($puvodniIDS[$key]); }                


        }
                  $rowindex++;
                 } 
       
            } 
         //vymazání záznamů, který byly odebrány před uložením  - musí být až po smyčce rows     
        $this->db->query('DELETE FROM nabidky_rows WHERE ?or', ['rid' => $puvodniIDS]);    
         
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
    
public function getDokumentById(\Nette\Security\user $user, $subjid = NULL, $idecko = NULL) {

if($subjid===NULL|| $idecko ===NULL ){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id nebo id dokumentu. [model->Nabidky]->getDokumentById';
    $this->result->data = null;            
    return $this->result;
} else {       
         $res = $this->db->fetch("SELECT refcislo FROM nabidky WHERE idecko = ? AND subj_id = ?" , $idecko, $subjid );                
        
        if($res) {            
           $dokument =  $this->getDokument($user, $subjid, $res->refcislo, true);    
        } else {
           $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyly nalezeny záznamy podle id dokladu. [model->Nabidky]->getDokumentById';
    $this->result->data = null;                    
    return $this->result;
        }
    return $dokument; // máme kompletní fakturu včetně array řádku v parametru $dokument->_DOM;
}
}    
}
