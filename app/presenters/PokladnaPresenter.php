<?php

namespace App\Presenters;
use App\Controls as AC;
use App\Model;
use Nette;


final class PokladnaPresenter extends BasePresenter
{
//modální okna pokladny jsou v BP
//control v basepresenrteru modal okno ModalPrijemEET	

    
public function handleeetPrijem(){

    if (!$this->isAjax()) {
					 $this->redirect('this');
	} else {
        $chyba = true;
        $userm = new \App\Model\MyAuthenticator($this->db, $this->container); 
        $eet = new Model\EETpokladna($this->db, $this->container);
        $eet->getEETlist($userm->getParent($this->user->getId())["subj_id"], true) ;
            $this->payload->chyba = $chyba;
            $this->payload->chybatext = $chybatext;                            
   	        $this->sendPayload();
    }
}    

    
}