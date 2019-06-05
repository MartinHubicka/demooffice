<?php

namespace App\Presenters;
use App\Controls as AC;
use App\Model;
use Nette;


final class CiselnikyPresenter extends BasePresenter
{
//modální okna pokladny jsou v BP
//control v basepresenrteru modal okno ModalPrijemEET	

    
public function handlefindAdresByIco($ico=NULL){

    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
        $chyba = true;
        $adresar = new Model\Adresar($this->db, $this->container);
        $dataJson = $adresar->getFirmaByIco($ico);
        var_dump($dataJson);
        die();
            $this->payload->chyba = $chyba;
            $this->payload->chybatext = $chybatext;                            
   	        $this->sendPayload();
    }
}    

    
}