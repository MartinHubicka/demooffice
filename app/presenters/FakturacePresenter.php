<?php

namespace App\Presenters;
use App\Controls as AC;
use App\Model;
use Nette;
use Vendor\mpdf\mpdf;
use Latte\Engine;
final class FakturacePresenter extends BasePresenter
{
//modální okna pokladny jsou v BP
//control v basepresenrteru modal okno ModalPrijemEET	
    public function startup() {
        parent::startup(); 
        self::renderShow(); 
    }
public function handleloadDokument($druh_dokumentu = "FP", $ref_cislo = NULL) {
    //$this->user->isinRole("EMIL")
    // $ref_cislo = NULL; // nový dokument  
if (!$this->isAjax()) {
	 $this->redirect('this');
	} else {
    $druh_dokumentu = strtoupper($druh_dokumentu);
    $druhy_dokumentu = array("FP", "FH", "ZP", "DB"); //možné druhy dokumentu faktury - jde o duplicitní věc, nemusí být, protože to řeší objekt tridy/faktura
    if(in_array($druh_dokumentu, $druhy_dokumentu)) {

        
        $this->redirect('Fakturace:editor',$druh_dokumentu, $ref_cislo);         
    }
    
}  
}
    
public function handletopdf($idecko= NULL) {
    
if (!$this->isAjax()) {
	 $this->redirect('this');
	} else {
        $this->redirect('Fakturace:topdf',$idecko);         
}  
}    
    
public function renderShow () {
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Fakturace($this->db, $this->container); //overi zda dokument existuje (je-li zadán druhý parametr a má-li uživatel/subjekt oprávnění/vlastnictví ) jinak $dokument= NULL
    $faktury = $dokument->getFaktury($this->user, $userm->getParent($this->user->getId())["subj_id"]);
    if ($faktury===NULL) {        
     //   $this->error();
    } elseif (!$this->user->isInRole('admin') && !$this->user->isInRole('fakturace')) {
         $this->error('Chybí oprávnění', Nette\HTTP\IResponse::S403_FORBIDDEN);
    } else {        
        //nastavit parametry šabloně
     /*   $template = $this->getTemplate();
        $template->faktury=$faktury;        
        */

        $this->template->faktury = $faktury;
    }  
    } 
  
public function handlesaveDokument(array $arrHlavicka=NULL, array $arrRows=NULL){

 if (!$this->isAjax()) {
		
	} else {
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Fakturace($this->db, $this->container); //overi zda dokumen
    $result = $dokument->saveData($this->user, $userm->getParent($this->user->getId())["subj_id"],$arrHlavicka, $arrRows); 
     $this->payload->result = $result;        
     $this->sendPayload();
     
 }
 
}
public function rendertopdf($idecko = NULL)
{ //povinná fuknkce pro použitý redirect
    
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $subject = new \App\Model\Subject($this->db, $this->container);
    $dokument = new \App\Model\Fakturace($this->db, $this->container); 
    
    $faktura = $dokument->getDokumentById($this->user, $userm->getParent($this->user->getId())["subj_id"], $idecko);
    if (!$faktura) {
       // $this->error();
    } else {               
 
        $infoSubject = $subject->getArrAttributeBySid($faktura->subj_id);

            if(!$infoSubject){
                $infoSubject = NULL;
            }
        $latte = new Engine;
        //nastavit parametry šabloně
        $template = $this->getTemplate();
        $template->faktura=$faktura;        
        //$template->setFile(__DIR__ . '/templates/Fakturace/topdf.latte');  
        $PDF = new \Mpdf\Mpdf([
			'default_font' => 'Lohit-Kannada'		
		]);
		$PDF->allow_charset_conversion=true;
		$PDF->charset_in='UTF-8';
		$PDF->ignore_invalid_utf8 = true;
		$stylesheet = file_get_contents(__DIR__ . "/../../www/CSS/style.css"); 
		$PDF->WriteHTML($stylesheet,1); //druhý parametr deklaruje zápis stylů
        
        $stylesheet = file_get_contents(__DIR__ . "/../../www/CSS/bootstrap.min.css"); 
		$PDF->WriteHTML($stylesheet,1); //druhý parametr deklaruje zápis stylů
        
        $stylesheet = file_get_contents(__DIR__ . "/../../www/CSS/bootstrap-grid.min.css"); 
		$PDF->WriteHTML($stylesheet,1); //druhý parametr deklaruje zápis stylů
        /*
        $stylesheet = file_get_contents(__DIR__ . "/../../www/bootstrap-reboot.min.cs"); 
		$PDF->WriteHTML($stylesheet,1); //druhý parametr deklaruje zápis stylů
*/
        $splatnost = date('d.m.Y', strtotime($faktura->dvystaveni . ' + '. abs($faktura->dsplatnosti).' days'));
       
        			 $params = [
            'faktura' => $faktura,
            'subject' =>$infoSubject,
            'splatnost' => $splatnost             
                        ];
		$PDF->WriteHTML($latte->renderToString(__DIR__ . '/templates/Fakturace/topdf.latte', $params));				
        

        $PDF->SetTitle($infoSubject["prefix"]."-".$faktura->druh_dokladu."-".$faktura->refcislo);
		$PDF->Output($infoSubject["prefix"]."-".$faktura->druh_dokladu."-".$faktura->refcislo.".pdf", "I");	 
			 
	 
		
    }
} 
public function renderEditor($druh_dokumentu = NULL, $ref_cislo = NULL)
{
 //povinná fuknkce pro použitý redirect
    
            $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Fakturace($this->db, $this->container); //overi zda dokument existuje (je-li zadán druhý parametr a má-li uživatel/subjekt oprávnění/vlastnictví ) jinak $dokument= NULL
    $faktura = $dokument->getDokument($this->user, $userm->getParent($this->user->getId())["subj_id"],$druh_dokumentu, $ref_cislo);
    if (!$faktura) {
        $this->error();
    } else {        
        //nastavit parametry šabloně
        $template = $this->getTemplate();
        $template->faktura=$faktura;        
        $template->setFile(__DIR__ . '/templates/Fakturace/editor.latte');
        
    }
}

//------------------
// následující funkce jsou duplicitní s ciselnikyPresenter, možná přesunout do BasePresenteru, nebo vymazat -     
public function handleupdateAdress($aid=-1, $arrdata=[]){
   // COMPONENTA tableAdresar
   //$aid = id kontaktu v adresáři, je li null nebo -1 jde o nový kontakt
   //$arrData = key = název fieldu value = hodnota fieldu
if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
   
        $adresar = new Model\Adresar($this->db, $this->container);
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
        $resultobj = $adresar->saveKontakt($userm->getParent($this->user->getId())["subj_id"],$aid, $arrdata); 
        $this->payload->result = $resultobj;        
        $this->redrawControl('redrawtableadresar');    
   	//    $this->sendPayload();
       
}
}
    
 public function handleupdateSkarta($sid=-1, $arrdata=[], $arrSids){
   // COMPONENTA tableAdresar
   //$aid = id kontaktu v adresáři, je li null nebo -1 jde o nový kontakt
   //$arrData = key = název fieldu value = hodnota fieldu
if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {   
        $skarty = new Model\Skarty($this->db, $this->container);
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
        $resultobj = $skarty->saveSkarta($userm->getParentId($this->user->getId()),$sid, $arrdata,  $arrSids); 
        $this->payload->result = $resultobj;        
        $this->redrawControl('redrawtableskarty');              
   	//    $this->sendPayload();            
}
}   

public function handledeleteAdresses($aids=[]){
    // COMPONENTA tableAdresar
    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
    if(count($aids) > 0){
         $adresar = new Model\Adresar($this->db, $this->container);
         $resultobj = $adresar->deleteKontakt($aids);    

        $this->redrawControl('redrawtableadresar');
    $this->payload->result = $resultobj;        
//   	     $this->sendPayload();
          
    }
}    
}
    
public function handledeleteSkartu($sids=[]){
    // COMPONENTA tableSkarty
    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
    if(count($sids) > 0){
         $skarty = new Model\Skarty($this->db, $this->container);
         $resultobj = $skarty->deleteSkartu($sids);    

        $this->redrawControl('redrawtableskarty');
    //    $this->redrawControl('redrawsubtableskarty');
    $this->payload->result = $resultobj;        
//   	     $this->sendPayload();
          
    }
}    
}    
public function handleaddSkartuAsProdukt($parentsid, $sids=[]){
    // COMPONENTA tableSkarty
    if (!$this->isAjax()) {
        $this->redirect('this');
	} else {
    if(count($sids) > 0){
        $skarty = new Model\Skarty($this->db, $this->container);
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container);         
        $resultobj = $skarty->addSkartuAsProdukt($userm->getParentId($this->user->getId()),$parentsid, $sids);    
        $this->redrawControl('redrawtableskarty');
        //$this->redrawControl('redrawsubtableskarty');
        $this->payload->result = $resultobj;        
//   	     $this->sendPayload();          
    }
}    
}     
    
public function handleupdateCisr ($field, $value) {
    $value = (int)$value; //force converto to int
    if(($field || $value) && is_int($value)){
        $this->MyAuthenticator->updateCisr($this->user->getId(), $field, $value);        
    }
    
        $this->redrawControl('redrawtablecisr'); //i když proces neproběhne, není na škodu aktualizovat data -> proto nepodmiňuji
    }   
    
/**
 * @return tableAdresar
 */
protected function createComponentTableAdresar() {		
       return new AC\TableAdresar($this->db, $this->container,$this->user);    
}    
/**
 * @return tableCisr
 */
protected function createComponentTableCisr() {		
       return new AC\TableCisr($this->db, $this->container,$this->user);    
}  
/**
 * @return tableSkarty
 */
protected function createComponentTableSkarty() {		
       return new AC\TableSkarty($this->db, $this->container,$this->user);    
} 


}