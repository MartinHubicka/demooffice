<?php
namespace App\Model;
use Nette\Database\Connection;
use Latte\Engine;
use Nette\Utils;
class Skarty extends BaseModel {

public function getSkarty($subjid=NULL, $filter=NULL){
    
    //todo zapracovat filter
    $result = NULL;
   if($subjid!==NULL) {
  $res = $this->db->fetchAll("SELECT * FROM skarty WHERE subj_id = ? " ,  $subjid);
        if($res) {            
            $result = (object)$res;
        }  
    }   
    return $result;
}    

public function saveSkarta($subjid=null,$sid, $arrData){

if($subjid===NULL){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id.';
    $this->result->data = null;    
} else {
    //očista, nebo očistec  
      $arrData["subj_id"] = $subjid; //pridame subjId 
      $arrData["mj2"] = (trim($arrData["mj2"]) == "") ? NULL : $arrData["mj2"];
      $arrData["mj2mj"] = (trim($arrData["mj2mj"]) == "") ? NULL : str_replace(",", ".", $arrData["mj2mj"]) * 1;
    
    if($sid===NULL || $sid < 0 || !is_int($sid)) {
        //nová skl.karta

        
    $this->db->query('INSERT INTO skarty', $arrData);
    $id = $this->db->getInsertId();
        if($id) {
    $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Nový kontakt byl uložen';
    $this->result->data = $id;           
        } else {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při uložení nového kontaktu';
    $this->result->data = null;        
        }
        
    } else {
        //update kontaktu
    $recordsUpdated =  $this->db->query('UPDATE adresar SET', 
        $arrData,
        'WHERE aid = ?', $aid);
    if($recordsUpdated->getRowCount())     { 
        $this->result->chyba = false;
        $this->result->zprava = true;
        $this->result->zpravatext = 'Kontakt byl aktualizován';
        $this->result->data = $recordsUpdated->getRowCount();//pocet ovlivněných řádků           
    }
    }    
}
    return $this->result;
} 
    
 
    public function deleteSkartu($sids=[]) {
    //zde není nutná parent_id protože uživatel mohl označit pouze kontkty které se mu zobrazují
    try { 
    $cnt = $this->db->query('DELETE FROM skarty WHERE ?or', ['sid' => $sids]);    // zajímavý operátor ?or a parametr jako výčet možností u fieldu sid
    $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Skl. karta/y byla/y vymazány.';
    $this->result->data = $cnt;           
        } catch(Exception $e)  {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při mazání skl. karty/ů: Popis chyby'.$e;
    $this->result->data = null;        
        }
} 
   
    public function addSkartuAsProdukt($subjid=NULL, $sids=[]) {
    if($subjid!== NULL || count($sids) > 0){
    try { 
        $arrData = [];
        foreach ($sids as $sid => &$val) {
            $arrData[$val]=0;
        }
 
            
        $this->db->query('INSERT INTO kusovnik', ["arrSids" => http_build_query($arrData)]); // vloží array jako string  
        $kid =$this->db->getInsertId();
        $arrData = ["skarta"=>"Nový produkt", "kid" => $kid, "mj2" => "", "mj2mj" => "", "kodfakturace" => ""];
        $this->saveSkarta($subjid,-1, $arrData);
        /*
    $cnt = $this->db->query('DELETE FROM skarty WHERE ?or', ['sid' => $sids]);    // zajímavý operátor ?or a parametr jako výčet možností u fieldu sid
    $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Skl. karta/y byla/y vymazány.';
    $this->result->data = $cnt;           
    */
        } catch(Exception $e)  {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při tvorbě produktu ze skl. karty/et: Popis chyby'.$e;
    $this->result->data = null;        
        }
    }    
}   
  
}