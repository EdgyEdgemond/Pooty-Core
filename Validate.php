<?php
  Class Validatable {
  
  	protected function verify_type($string,$type,$target,$msg){
  		switch($type){
  			case 'email': if(preg_match('[a-z0-9A-Z]+[a-z0-9A-Z_\.\-]*@[a-z0-9A-Z_\-][\.[a-z0-9A-Z]]*',$string)) return true;
  			case 'date': if(strtotime($string)) return true;
  			case 'int': if ($string !== true && preg_replace("/[^0-9]/", "", $string) === (string) $string) return true;
  			case 'float': if(is_float($string) || $string == '') return true;
  		}
      add_error($target, $msg);
  	}
  
  	protected function verify_presence($string,$target,$msg){
  		if (is_null($string) || strlen($string) == 0) {
  		  add_error($target, $msg);
  		}
  	}
  	
  	protected function verify_numeric($string,$target,$msg){
  		if (strlen($string) != 0 && !is_numeric($string)) {
  		  add_error($target, $msg);
  		}
  	}
  	
  	protected function verify_length($string,$start,$end,$target,$msg){
  		if ($start>$end && $end!=0) {
        print(Error::GetBasicErrorString("FAIL!", "verify_length can't handle start val greater than end val. Try again."));				
    		die;
  		}

      if(strlen($string)==0) {
        //if string is empty ignore it. If a field is mandatory verify_presence should also be used.
        return true;
      } else if ($start==0) {
        if(strlen($string)<=$end) return true;
      } else if ($end==0) {
        if(strlen($string)>=$start) return true;
      } else {
        if(strlen($string)>=$start && strlen($string)<=$end) return true;
      }
      add_error($target, $msg);
  	}
  	
  	protected function verify_alphanumeric($string,$target,$msg) {
  		if(!preg_match('/^([a-zA-Z0-9_-]+)$/', $string)) {
  			add_error($target, $msg);
  		}
  	}
  }
?>