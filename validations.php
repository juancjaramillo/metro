<?php

require_once('master.php');

class Validation {
	
	function validate_digits($value){
		return(preg_match("/^\d*$/", $value) ? true : 'El campo debe ser númerico');		
	}
	
	function validate_maxlenght($value, $lenght){
		return((strlen($value) <= $lenght) ? true : 'El campo no puede ser mayor a '.$lenght.' caracteres');
	}
	
	function validate_minlenght($value, $lenght){
		return((strlen($value) >= $lenght) ? true : 'El campo no puede ser menor a '.$lenght.' caracteres');
	}
	
	function validate_initial($value){
		return($value[0] == '0' ? 'El valor del campo no puede iniciar por cero' : true);
	}
	
	function validate_email($value){
		return(preg_match("/^([\\w-]+(?:\\.[\\w-]+)*)@((?:[\\w-]+\\.)*\\w[\\w-]{0,66})\\.([a-z]{2,6}(?:\\.[a-z]{2})?)$/", $value) ? true : 'Dirección de correo electrónico invalida');
	}
	
	function validate_required($value){
		return($value != '' ? true : 'Este campo es requerido');
	}
	
	
	function validate_uuid($value){
		return(preg_match("/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/", $value) ? true : 'uuid de registro invalido');
	}
	
	function validate_attached($value){
		$file = '/var/www/html/MLT/mexxx/upload/'.$value;
		if(file_exists($file)){
			if(filesize($file) <= '1000000') {
				return true;
			} else{
				unlink($file);
				return 'Tamaño de archivo invalido';
			}	
		} else{
			return 'Referencia de archivo no valida';
		}
	}
	
	function validate_attached_ext($value){
		$ext = array(
			'doc','docx','xls','xlsx','xlm','xlt','ppt','pptx','jpg','png','ico','zip','rar','msg','pdf','mp4','mkv','avi','mov','wmv','flv','mpeg','3gp','odt'
		);
		return (in_array($value, $ext) ? true : 'Extensión de archivo invalida');
	}
	
}

?>