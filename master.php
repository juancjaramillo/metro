<?php 

class Master {
	
	function get_data($url) {
		
		$contenido = $url;
		$len = count($contenido)-2;
		$miarray = array();
		
		for ($i=3;$i<=$len;$i++){
			$inicioclave = strpos($contenido[$i],'[')+1;
			$finclave = strpos($contenido[$i],']')-5;
			$clave = substr($contenido[$i], $inicioclave, $finclave);			
			$iniciovalor = strpos($contenido[$i],'=')+2;			
			$valor = substr($contenido[$i], $iniciovalor);			
			$miarray[$clave] = $valor; 
		}
		
		return $miarray;
	}
	
	function validate_master($value){
		return(array_key_exists(strtoupper($value), Master::type_document()) ? true : 'Información invalida');
	}
	
}

?>