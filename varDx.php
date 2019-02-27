<?php
namespace varDX;

/*
varDX - PHP flat-file storage
by @rahuldottech
v1.4
--
https://github.com/rahuldottech/
https://rahul.tech/
*/


class cDX {
	
	private $dataFile;	
	
	public function def($filename){
		$this->dataFile = $filename;
	}
	
	public function write($varName, $varVal){
		if(file_exists($this->dataFile)){
			$foundLine = $this->check($varName);
		} else {
			$foundLine = false;
		}
		
		if(!$foundLine){
			$writeData = $varName.'__=__'.urlencode(serialize($varVal)).PHP_EOL;
			file_put_contents($this->dataFile, $writeData, FILE_APPEND);	
		} else {
			return "ERR_DX_KEY_ALREADY_EXISTS";
		}
	}
	
	public function read($varName){
		if(file_exists($this->dataFile)){
			foreach(file($this->dataFile) as $line) {
				if(strpos($line, $varName) !== false) {
					list(, $new_str) = explode("__=__", $line);
					$foundLine = true;
				}
			}
			if($foundLine){
				$val = rtrim($new_str); 
				return unserialize(urldecode($val));
			} else {
				return "ERR_DX_KEY_NOT_FOUND";
			}
		} else {
			return "ERR_DX_FILE_DOES_NOT_EXIST";
		}
	}

	
	public function del($varName){
		if(file_exists($this->dataFile)){
		    $f = $this->dataFile;
			$term = $varName.'__=__';
			$arr = file($f);
			foreach ($arr as $key=> $line) {
				if(stristr($line,$term)!== false){unset($arr[$key]);break;}
			}
			//reindexing array
			$arr = array_values($arr);
			//writing to file
			file_put_contents($f, implode($arr));
		} else {
			return "ERR_DX_FILE_DOES_NOT_EXIST";
		}
	}
	
	public function modify($varName, $varVal){
		if(file_exists($this->dataFile)){	
			if($this->check($varName)){
				$this->del($varName);
			} 
		}
		$writeData = $varName.'__=__'.urlencode(serialize($varVal)).PHP_EOL;
		file_put_contents($this->dataFile, $writeData, FILE_APPEND);	
		
	}
	
	public function check($varName){
		if(file_exists($this->dataFile)){
			foreach(file($this->dataFile) as $line) {
				if(stripos($line, $varName.'__=__') === 0){
					return true;
				} 
			}
			return false;
		} else {
			return "ERR_DX_FILE_DOES_NOT_EXIST";
		}
	}
}
	
