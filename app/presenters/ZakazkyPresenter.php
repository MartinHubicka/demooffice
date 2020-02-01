<?php
namespace App\Presenters;
use App\Controls as AC;
use App\Model;
use Nette;
use Vendor\mpdf\mpdf;
use Latte\Engine;
final class ZakazkyPresenter extends BasePresenter
{
 //control v basepresenrteru modal okno ModalPrijemEET	
    public function startup() {
        parent::startup(); 
        self::renderShow(); 
    }
    

public function renderShow () {
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    //$dokument = new \App\Model\Zakazky($this->db, $this->container); //overi zda dokument existuje (je-li zadán druhý parametr a má-li uživatel/subjekt oprávnění/vlastnictví ) jinak $dokument= NULL
    /*
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

       // $this->template->nabidky = $nabidky;
    }  
    
public function renderEditor($ref_cislo = NULL)
{
    $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
    $dokument = new \App\Model\Zakazky($this->db, $this->container); //overi zda dokument existuje (je-li zadán druhý parametr a má-li uživatel/subjekt oprávnění/vlastnictví ) jinak $dokument= NULL
    $zakazka = $dokument->getDokument($this->user, $userm->getParent($this->user->getId())["subj_id"],$ref_cislo);
    
    if (!$zakazka) {
        $this->error();
    } else {        
        //nastavit parametry šabloně
        $template = $this->getTemplate();
        $template->zakazka=$zakazka;        
        $template->setFile(__DIR__ . '/templates/Zakazky/editor.latte');
        
    }
}        
}

    

    