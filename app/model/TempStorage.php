<?php
namespace App\Model;
use Nette\SmartObject;
use Vendor\mpdf\mpdf;
class TempStorage
{
    public $dir;
	 public $relpath;
    public function __construct()
    {
       $this->relpath = "/temp/";
		 $this->dir = __DIR__ . "/../../www". $this->relpath;
    }

    public function save($file, $contents)
    {
        file_put_contents($this->dir . '/' . $file, $contents);
    }
	 public function savePDF($filename,$mpdfobject) {
		 	

		 $mpdfobject->Output($this->dir . $filename, \Mpdf\Output\Destination::FILE);
		 
	 return	array("relpath" => $this->relpath . $filename, "fullpath" =>$this->dir . $filename);
	 }
	
}