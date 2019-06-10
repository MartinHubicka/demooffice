<?php

namespace App\Presenters;
use App\Controls as AC;
use App\Model;
use Nette;


final class CiselnikyPresenter extends BasePresenter
{
//modální okna pokladny jsou v BP
//control v basepresenrteru modal okno ModalPrijemEET	

public function handlefindAdresByIco($ico=NULL, $icofirma=NULL){

    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
        $chyba = true;
        $adresar = new Model\Adresar($this->db, $this->container);
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
        $dataObj = $adresar->getFirmaByIco($userm->getParent($this->user->getId())["subj_id"],$ico, $icofirma); 
        
            $this->payload->adresy = $dataObj;
   	        $this->sendPayload();
    }
}    
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
    
/**
 * @return tableAdresar
 */
protected function createComponentTableAdresar() {		
       return new AC\TableAdresar($this->db, $this->container,$this->user);    
}    
    
}