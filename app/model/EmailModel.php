<?php
namespace App\Model;
use Nette\Database\Connection;
use Nette\Mail\Message;
use Vendor\mpdf\mpdf;
use Nette\Mail\SendmailMailer;
use Latte\Engine;
class EmailModel extends BaseModel {
    
public function sendTempPass($email, $temppass){
    $mail = new Message;
    $latte = new Engine;	
     $params = [
    'temppass' => $temppass,
    'temppassmin' => $this->konstanty["temppassmin"]     
];
   
    
	//$mail->setFrom($this->parameters["systemmail"])
    $mail->setFrom('noreply@twoffice.app')
		->addTo($email)		
		->setSubject('Přístupové údaje')
		->setHtmlBody($latte->renderToString(__DIR__.'/emailtemplates/resetpass.latte', $params)); 
        $mailer =  new SendmailMailer();				
		$mailer->send($mail);
}
}