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

public function saveSkarta($subjid=null,int $sid, $arrData,$arrSids=NULL){

if($subjid===NULL){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id.';
    $this->result->data = null;    
} else {
    //očista, nebo očistec  

    
     if(!isset($arrData["subj_id"])) {
          $arrData["subj_id"] = $subjid; //pridame subjId 
      }
    if(isset($arrData["mj2"]) && isset($arrData["mj2mj"])) {
      $arrData["mj2"] = (trim($arrData["mj2"]) == "") ? NULL : $arrData["mj2"];
      $arrData["mj2mj"] = (trim($arrData["mj2mj"]) == "") ? NULL : str_replace(",", ".", $arrData["mj2mj"]) * 1;
    }
    if($sid===NULL || $sid < 0 || !is_int($sid)) {
        //nová skl.karta

        
    $this->db->query('INSERT INTO skarty', $arrData);
    $id = $this->db->getInsertId();
        if($id) {
    $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Nová skl.karta byla uložen';
    $this->result->data = $id;           
        } else {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při uložení nové skl.karty';
    $this->result->data = null;        
        }
        
    } else {
        //update kontaktu
    $recordsUpdated =  $this->db->query('UPDATE skarty SET', 
        $arrData,
        'WHERE sid = ?', $sid);
    if($recordsUpdated->getRowCount())     { 
        $this->result->chyba = false;
        $this->result->zprava = true;
        $this->result->zpravatext = 'Skladová karta byla aktualizována';
        $this->result->data = $recordsUpdated->getRowCount();//pocet ovlivněných řádků           
    }
    //případný update kusovníku
        if(isset($arrData["kid"])) {
        $recordsUpdated =  $this->db->query('UPDATE kusovnik SET', 
        ["arrSids"=>$arrSids],
        'WHERE kid = ?', $arrData["kid"]);
            
        }
        
    }    
}
    return $this->result;
} 
public function getKidSids($kid){

if($kid !== NULL){
    
   $sids = [];
   $cntmj = [];
    $temp =$this->db->fetch("SELECT arrSids FROM kusovnik WHERE kid = ?",$kid);   

    if(strlen($temp["arrSids"])>0){
    $sidy = explode(",",$temp["arrSids"]);
    
    foreach($sidy as $sid){
        $tmp = explode(":",$sid); //[0] sid, [1] početmj        
        $sids[] = $tmp[0];
        $cntmj[$tmp[0]] = $tmp[1]*1;
    }
    
    
    $res = $this->db->fetchAll("SELECT * FROM skarty WHERE ?or",['sid' =>$sids]);  
    $result= [];
    foreach($res as $child){   
        
        $result[]= [
            "skarta" => $child->skarta,
            "sid" => $child->sid,
            "mj" =>$child->mj,
            "pocetmj" =>  (isset($cntmj[$child->sid])) ? $cntmj[$child->sid] : NULL
        ];            
    }
    } else { $result = NULL; }
    $this->result->chyba = false;
    $this->result->zprava = false;
    $this->result->zpravatext = '';
    $this->result->data = $result;    
   
} else {
    $this->result->chyba = false;
    $this->result->zprava = false;
    $this->result->zpravatext = '';
    $this->result->data = NULL;    
}
    
    return $this->result;
}    
 public function getSkartuBySid($sid=NULL)  {
if($sid !== NULL ){
 $result = $this->db->fetch("SELECT * FROM skarty WHERE sid = ?",$sid);  
    $this->result->chyba = false;
    $this->result->zprava = false;
    $this->result->zpravatext = '';
    $this->result->data = $result;    
    
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
   
    public function addSkartuAsProdukt($subjid=NULL, $parentsid=NULL,$sids=[]) {
    if($parentsid !==NULL || $subjid!== NULL || count($sids) > 0){
    try { 
        $arrData ='';
        foreach ($sids as $sid => &$val) {
            if(strlen($arrData)>0) {
                $arrData .= ', '; 
            }
            $arrData .= $val . ":" . 0;
        }    
    
        $this->db->query('INSERT INTO kusovnik ', ['arrSids' => $arrData]); // vloží array jako string  
        $kid =$this->db->getInsertId();
        
        //$arrData = ["skarta"=>"Nový produkt", "kid" => $kid, "mj2" => "", "mj2mj" => "", "kodfakturace" => ""];
        $arrData = ["kid" => $kid];
        $this->saveSkarta($subjid, $parentsid, $arrData);
        
        //vymazat nepoužitzé kidy v kusovníku - očista
        
        
        
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