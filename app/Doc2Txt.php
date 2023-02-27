<?php

namespace App;

class Doc2Txt {
	private $filename;
	
	public function __construct($filePath) {
		$this->filename = $filePath;
	}
	
    private function read_doc()	
    {
		$fileHandle = fopen($this->filename, "r");
		$line = @fread($fileHandle, filesize($this->filename));   
		$lines = explode(chr(0x0D),$line);
        $outtext = "";  
        $i = 0;      

        $primerchar = 0;
        $segundochar = 0;
		$lineafinal = 9999999;

		foreach($lines as $thisline)
		{
			$i++;
            $pos = strpos($thisline, chr(0x00));
            $posfinal = strpos($thisline, chr(0x03));
            $posfinal2 = strpos($thisline, chr(0x04));

            if($posfinal !== FALSE){
            	$primerchar = $i;
            	
			}
			if (($pos !== FALSE)||(strlen($thisline)==0))
		  	{
                     
          	} 
          	else  if ($lineafinal > $i) {
          		if($posfinal2 !== FALSE){
	            	$segundochar = $i;            	
	            	
	            	if ($primerchar !=0 && $segundochar == $primerchar+2) {
						$lineafinal = $primerchar;
					} else{
						$primerchar = 0;
						$segundochar = 0;
					}
				}
          	
				$outtext .= $thisline;

			} 
        }

        //^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$
        //$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);


        //$outtext = preg_replace("/^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/","", $outtext);

        $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)\xC0-\xDC\xE0-\xFC]/","",$outtext);
        $outtext = preg_replace('/[\x00-\x1F\xFC-\xFF]/', '', $outtext);
        
        if ($outtext == '') {
        	$j = 0;
			foreach($lines as $thisline)
			{
	            $pos = strpos($thisline, chr(0x00));
	        
				if (($pos !== FALSE)||(strlen($thisline)==0))
			  	{
	                     
	          	} 
	          	else {
					$outtext .= $thisline;
				} 
	        }        	
        }
        return utf8_encode($outtext);
	}


	private function read_docx(){

		$striped_content = '';
		$content = '';

		$zip = zip_open($this->filename);

		if (!$zip || is_numeric($zip)) return false;

		while ($zip_entry = zip_read($zip)) {

			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

			if (zip_entry_name($zip_entry) != "word/document.xml") continue;

			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

			zip_entry_close($zip_entry);
		}// end while

		zip_close($zip);

		$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		$content = str_replace('</w:r></w:p>', "\r\n", $content);
		$striped_content = strip_tags($content);

		return $striped_content;
	}
	
	public function convertToText() {
	
		if(isset($this->filename) && !file_exists($this->filename)) {
			return "File Not exists";
		}
		
		$fileArray = pathinfo($this->filename);
		$file_ext  = $fileArray['extension'];
		if($file_ext == "doc" || $file_ext == "docx")
		{
			if($file_ext == "doc") {
				return $this->read_doc();
			} else {
				return $this->read_docx();
			}
		} else {
			return "Invalid File Type";
		}
	}
}
