<?php
namespace App\Model;
use Nette\Database\Connection;
use Latte\Engine;
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
        $this->result->data = $recordsUpdated->getRowCount();//pocet ovlivněných řádků           
    }
    }    
}
    return $this->result;
}   
    
public function deleteKontakt($aids=[]) {
    //zde není nutná parent_id protože uživatel mohl označit pouze kontkty které se mu zobrazují
    try { 
    $cnt = $this->db->query('DELETE FROM adresar WHERE ?or', ['aid' => $aids]);    // zajímavý operátor ?or a parametr jako výčet možností u fieldu aid
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

     $xmldom = new \DOMDocument(); //lomítko je tam proto, yb to nehledal v modelu ale v stanartně v php třídě
    if($xmldom) {
        if($xmldom->load($this->konstanty['aresurl']."&ico=".$ico)) {
       
        $returnCount =  $xmldom->getElementsByTagName("Ares_odpovedi")[0]->getAttribute('odpoved_pocet');


if($returnCount == 1 ) {

if ($xmldom->getElementsByTagName("ET")->length > 0) { //rejstrik vratil chybu 
/*
$chyba=  "Neočekávaná chyba, zkontrolujte správnost zadaného IČ.";
$json = '{ "CHYBA" : "' .$chyba . '" }' ;
*/
} else {
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
// vrací object
//    object(stdClass)#54 (8) { ["FIRMA"]=> string(26) "ThermoWhite Moravia s.r.o." ["DIC"]=> string(10) "CZ07496516" ["ICO"]=> string(8) "07496516" ["ULICECP"]=> string(17) "Havlíčkova 5633" ["MESTO"]=> string(7) "Jihlava" ["MISTO"]=> string(0) "" ["PSC"]=> string(5) "58601" ["STAT"]=> string(17) "Česká republika" }
}}     
            
            
    }}    
}
return $result;
}    
}