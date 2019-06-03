<?php

namespace App\Presenters;
use App\Controls as AC;
use Nette;


final class HomepagePresenter extends BasePresenter
{

/**
 * @return cardparentidentity
 */
protected function createComponentCardParentIdentity () {		
    return new AC\CardParentIdentity($this->db, $this->container,$this->user);
}	
  
}