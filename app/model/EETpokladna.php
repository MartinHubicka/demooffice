<?php
namespace App\Model;
use Nette\Database\Connection;
use Nette\Mail\Message;
use Vendor\mpdf\mpdf;
use Nette\Mail\SendmailMailer;
use Latte\Engine;
class EETpokladna extends BaseModel {
    
public function getEETlist ($subjectid = NULL, $demoEETincludet = false){
//seznam eet certifikátů a atributu pokladen na základě subject id resp $subjectid
//$demoEETincludet, je -li true zahrne i certifikáty s parentID NULL - tedy demo certifikáty
//vrací array
$eetList = [];
    if($subjectid){
        $democond = ($demoEETincludet===true) ? " OR subj_id is NULL" : "";
        $res = $this->db->fetchAll("SELECT * FROM eetcertifs WHERE subj_id = ? ".$democond,    $subjectid);
        if($res) {
            $eetList = $res;
        }
    }
return $eetList;    
}
    
    
}