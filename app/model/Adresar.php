<?php
namespace App\Model;
use Nette\Database\Connection;
use Nette\Utils;
class Adresar extends BaseModel {

public function getAdresy($subjid=NULL, $filter=NULL){    
    //todo zapracovat filter
    $result = NULL;
   if($subjid!==NULL) {
  $res = $this->db->fetchAll("SELECT * FROM adresar WHERE subject_id = ? " ,  $subjid);
        if($res) {            
            $result = (object)$res;
        }  
    }   
    return $result;
}    

public function getAdresyByStr($subjid=NULL, $str=NULL, $autocompleteformat=false){    
    //todo zapracovat filter
    $result = NULL;
   if($subjid!==NULL && $str !== NULL) {
  $res = $this->db->fetchAll("SELECT * FROM adresar WHERE subject_id = ? AND firma LIKE ?" ,  $subjid, $this->db::literal("'%".$str."%'"));
        if($res) {           
            
            if(!$autocompleteformat) {                            
            $result = (object)$res;
            } else { 
            //vrátí zkrácený formát:: data = [{label: "Pepa",aid: "15"},{label: "Martin",aid: "75"}];
            $adresy = array();
           foreach ($res as $value) {                
                $adresy[] = array("value" =>$value->FIRMA, "aid"=>$value->aid);
            }                
            $result = $adresy;
            }
        }  
    }   
    return $result;
}        
    
public function saveKontakt($subjid=NULL, $aid, $arrData){
//využití obj z BaseModelu $this->result
if($subjid===NULL){
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba toku programu. Nebyl předán subj_id.';
    $this->result->data = null;    
} else {
    if($aid===NULL || $aid < 0 || !is_int($aid)) {
        //nový kontakt
 
        $arrData["subject_id"] = $subjid; //pridame subjId 

    $this->db->query('INSERT INTO adresar', $arrData);
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
        $this->result->data = $recordsUpdated->getRowCount();
    }
    }    
}
    return $this->result;
}   
    
public function deleteKontakt($aids=[]) {
    //zde není nutná parent_id protože uživatel mohl označit pouze kontakty které se mu zobrazují
    try { 
    $cnt = $this->db->query('DELETE FROM adresar WHERE ?or', ['aid' => $aids]);   
    $this->result->chyba = false;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Kontakt/y byly vymazány.';
    $this->result->data = $cnt;           
        } catch(Exception $e)  {
    $this->result->chyba = true;
    $this->result->zprava = true;
    $this->result->zpravatext = 'Chyba při mazání kontaktu/ů: Popis chyby'.$e;
    $this->result->data = null;        
        }
}    
    
public function getAdresaByAid($aid=NULL)  {
if($aid !== NULL ){
 $result = $this->db->fetch("SELECT * FROM adresar WHERE aid = ?",$aid);  
    $this->result->chyba = false;
    $this->result->zprava = false;
    $this->result->zpravatext = '';
    $this->result->data = $result;    
    
} 
    return $this->result;
}  
/**
 * Vyhledá firmu v aresu dle ic
 * @param string
 * return stdClass|null
*/    
public function getFirmaByIcoAres($ico) {
   
       $xmldom = new \DOMDocument(); //lomítko => aby to nehledal v modelu ale v php třídě
    if($xmldom) {
        if($xmldom->load($this->konstanty['aresurl']."&ico=".$ico)) {
       
        $returnCount =  $xmldom->getElementsByTagName("Ares_odpovedi")[0]->getAttribute('odpoved_pocet');


if($returnCount == 1 ) {

if ($xmldom->getElementsByTagName("ET")->length > 0) { // == 0 => rejstrik vratil chybu 
} else {
    //todo: předělat na smyčku a konverzi do formátu json
$json = '{';
$json .= ($xmldom->getElementsByTagName("OF")->length > 0)  ?  '"FIRMA" : "'. $xmldom->getElementsByTagName("OF")[0]->nodeValue .'"' : "";
$json .= ", ";
$json .= ($xmldom->getElementsByTagName("DIC")->length > 0)  ?  '"DIC" : "'. $xmldom->getElementsByTagName("DIC")[0]->nodeValue .'"' : "";
$json .= ", ";
$json .= ($xmldom->getElementsByTagName("ICO")->length > 0)  ?  '"ICO" : "'. $xmldom->getElementsByTagName("ICO")[0]->nodeValue .'"' : "";
$json .= ", ";
$json .= ($xmldom->getElementsByTagName("UC")->length > 0)  ?  '"ULICECP" : "'. $xmldom->getElementsByTagName("UC")[0]->nodeValue .'"' : "";
$json .= ", ";
$mesto =  ($xmldom->getElementsByTagName("N")->length > 0) ?   $xmldom->getElementsByTagName("N")[0]->nodeValue  : "";
$json .=  '"MESTO" : "'. $mesto .'"';
$json .= ", ";                                                                                                                       
$misto = ($xmldom->getElementsByTagName("NMC")->length > 0)  ?  $xmldom->getElementsByTagName("NMC")[0]->nodeValue : "";
$misto .= ($xmldom->getElementsByTagName("NCO")->length > 0)  ? ($misto!="") ? "-".$xmldom->getElementsByTagName("NCO")[0]->nodeValue : $xmldom->getElementsByTagName("NCO")[0]->nodeValue : "";
if($mesto==$misto) $misto="";
$json .=  '"MISTO" : "'. $misto.'"';
$json .= ", ";
$json .= ($xmldom->getElementsByTagName("PSC")->length > 0)  ?  '"PSC" : "'. $xmldom->getElementsByTagName("PSC")[0]->nodeValue .'"' : "";
$json .= ", ";
$json .= ($xmldom->getElementsByTagName("NS")->length > 0)  ?  '"STAT" : "'. $xmldom->getElementsByTagName("NS")[0]->nodeValue .'"' : "";
$json .= '}';

$result =  \Nette\Utils\Json::Decode($json);   
}}     
            
            
    }}    
 return (isset($result)) ? $result : NULL;   
}  
    
/**
 * Vyhledání firmy dle IC, adresar nebo ares
 * @param int|null
 * @param int|null
 * @param string|null
 * return stdClass|null
*/    
public function getFirmaByIco ($subjid=NULL,$ico=NULL, $icofirma=NULL){
$result = NULL;    
if($icofirma !== NULL && strlen($icofirma) >=3   && $subjid!==NULL) { //ico nebo část názvu firmy a řetězec má alespoň 3 znaky
//------
//krok 1-vyhledani  v adresari    
    
  $res = $this->db->fetchAll("SELECT * FROM adresar WHERE subject_id = ? AND (ico = ? OR firma LIKE ?) " ,  $subjid, $icofirma, $this->db::literal("'%".$icofirma."%'"));
        if($res) {    
            $result = (object)$res;
        }
    
}    
    

if($ico!==NULL && $result===NULL) { 
//------
//pokud nebylo nalezeno v adresari následuje krok 2 - vyhledání v aresu dle ič
 $result = $this->getFirmaByIcoAres($ico);
  
}
return (isset($result)) ? $result : NULL;   
}    
}