<?php

namespace App\Presenters;
use App\Controls as AC;
use App\Model;
use Nette;
use Vendor\mpdf\mpdf;
use Latte\Engine;
final class NabidkyPresenter extends BasePresenter
{
 //control v basepresenrteru modal okno ModalPrijemEET	
    public function startup() {
        parent::startup(); 
        self::renderShow(); 
    }
    
public function handlesaveDokument(array $arrHlavicka=NULL, array $arrRows=NULL){

 if (!$this->isAjax()) {
		
	} else {
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Nabidky($this->db, $this->container); 
    $result = $dokument->saveData($this->user, $userm->getParent($this->user->getId())["subj_id"],$arrHlavicka, $arrRows); 
     $this->payload->result = $result;        
     $this->sendPayload();
     
 }
 
}    

public function renderShow () {
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Nabidky($this->db, $this->container); //overi zda dokument existuje (je-li zadán druhý parametr a má-li uživatel/subjekt oprávnění/vlastnictví ) jinak $dokument= NULL
    $nabidky = $dokument->getNabidky($this->user, $userm->getParent($this->user->getId())["subj_id"]);
    if ($nabidky===NULL) {        
     //   $this->error();
    } elseif (!$this->user->isInRole('admin') && !$this->user->isInRole('fakturace')) {
         $this->error('Chybí oprávnění', Nette\HTTP\IResponse::S403_FORBIDDEN);
    } else {        
        //nastavit parametry šabloně
     /*   $template = $this->getTemplate();
        $template->faktury=$faktury;        
        */

        $this->template->nabidky = $nabidky;
    }  
    }     
public function handletopdf($idecko= NULL) {
    
if (!$this->isAjax()) {
	 $this->redirect('this');
	} else {
        $this->redirect('Nabidky:topdf',$idecko);         
}  
}
public function rendertopdf($idecko = NULL)
{ //povinná fuknkce pro použitý redirect
    
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    
    $subject = new \App\Model\Subject($this->db, $this->container);
    $dokument = new \App\Model\Nabidky($this->db, $this->container); 
    
    $nabidka = $dokument->getDokumentById($this->user, $userm->getParent($this->user->getId())["subj_id"], $idecko);
    
    if ($nabidka->chyba == true) {
        $this->error();
        
    } else {               

        $infoSubject = $subject->getArrAttributeBySid($nabidka->subj_id);
        $userinfo = $userm->getUser($nabidka->uid);
        
            if(!$infoSubject){
                $infoSubject = NULL;
            }
        $latte = new Engine;
        //nastavit parametry šabloně
        $template = $this->getTemplate();
       // $template->nabidka=$nabidka;        
     //   $template->userinfo=$userinfo;   
        
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
        $platnost = date('d.m.Y', strtotime($nabidka->dvystaveni . ' + '. abs($nabidka->dplatnosti).' days'));
       
        			 $params = [
            'nabidka' => $nabidka,
            'subject' =>$infoSubject,
            'platnost' => $platnost,
            'userinfo'  =>$userinfo 
                        ];
		$PDF->WriteHTML($latte->renderToString(__DIR__ . '/templates/Nabidky/topdf.latte', $params));				
        

        $PDF->SetTitle($infoSubject["prefix"]."-".$nabidka->zkraceny_nazev."-".$nabidka->refcislo);
		$PDF->Output($infoSubject["prefix"]."-".$nabidka->zkraceny_nazev."-".$nabidka->refcislo.".pdf", "I");	 
			 
	 
		
    }
}    
    
public function renderEditor($ref_cislo = NULL)
{
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Nabidky($this->db, $this->container); //overi zda dokument existuje (je-li zadán druhý parametr a má-li uživatel/subjekt oprávnění/vlastnictví ) jinak $dokument= NULL
    $nabidka = $dokument->getDokument($this->user, $userm->getParent($this->user->getId())["subj_id"],$ref_cislo);
    
    if (!$nabidka) {
        $this->error();
    } else {        
        //nastavit parametry šabloně
        $template = $this->getTemplate();
        $template->nabidka=$nabidka;        
        $template->setFile(__DIR__ . '/templates/Nabidky/editor.latte');
        
    }
}    
    
}