<?php

	require_once('validations.php');
	require 'Slim/Slim.php';
	\Slim\Slim::registerAutoloader();
	
	$ws = new \Slim\Slim();
	
	$ws->response->headers->set("Content-type", "application/json;charset=ISO-8859-1");
	$ws->response->headers->set('Access-Control-Allow-Origin', '*');
	$ws->response->headers->set('Access-Control-Allow-Credentials', 'true');
	$ws->response->headers->set('Access-Control-Allow-Headers', 'Authorization, X-Requested-With');
	
	// Declaración de los métodos del Web Service y las funciones asociadas a ellos:
	$ws->get('/departments', 'getDepartments');
	$ws->get('/cities/id_department/:id_department', 'getCities');
	$ws->get('/type_document', 'getTypeDocument');
	$ws->get('/type_document_ws', 'getTypeDocumentWs');
	$ws->get('/cities_ws', 'getCitiesWs');
	$ws->get('/code_cell_phone', 'getCodeCellPhone');
	$ws->get('/status', 'getStatus');
	$ws->get('/queryCases/consecutive/:consecutive', 'getQueryCases');
	$ws->post('/setCases', 'postSetCases');

	$ws->run();
	
	function getConnection(){
		$dbhost = '10.1.xx.xx';
		$dbname = 'mexx_MLT_prod';
		$dbuser = 'mlxxx';
		$dbpass = 'suxxx';
		$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		return $dbh;
	}

	
	function codificaJson($cadena){
		return utf8_decode(preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', json_encode($cadena)));
	}
	

	function replace_unicode_escape_sequence($match) {
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}
	
	
	function generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	
	function purge_array($array) {
	    $result = array();
	    foreach($array as $key=>$value){
	    	if (is_array($value)){
	            $result = purge_array($value);
	        } else {
	        	if($value != 'true'){
	        		array_push($result, $value);
	        	}
	        }
	    }
	    return $result;
	}
	
	
	function purge_array2($array) {
		$result = array();
	    foreach($array as $key=>$value){
	    	if (is_array($value)){
	    		if(count($array[$key]) != 0){
	    			$result[$key] = array($array[$key]);
	    		}
	        }
	    }
	    return $result;
	}
	
	
	function purge_array3($array) {
	    foreach($array as $key=>$value){
	    	if($value == '' && $value == null){
				unset($array[$key]);
			}
	    }
	    return $array;
	}
	
	
	function get_data($url) {
		$contenido = file($url);
		$len = count($contenido)-2;
		$miarray = array();
		for ($i=3;$i<=$len;$i++){
			$inicioclave = strpos($contenido[$i],'[')+1;
			$finclave = strpos($contenido[$i],']')-5;
			$clave = substr($contenido[$i], $inicioclave, $finclave);	
			$clave = trim($clave);
			$iniciovalor = strpos($contenido[$i],'=')+3;			
			$valor = substr($contenido[$i], $iniciovalor);			
			$miarray[$clave] = trim($valor);
		}
		return $miarray;
	}
	
	//Funciones que contienen la lógica de negocio para los métodos del Web Services
	
	function getDepartments() {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Instancia de la conexión a la base de datos
			$db = getConnection();
			
			$sql = "SELECT id, name FROM ing_departamento WHERE deleted = 0 ORDER BY name;";
			$resultado = $db->query($sql);
			$departments = $resultado->fetchAll(PDO::FETCH_OBJ);
			echo '{"departments": ' . codificaJson($departments) . '}';
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}	
	
	
	function getCities($id_department) {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Instancia de la conexión a la base de datos
			$db = getConnection();

			$sql = "SELECT id, name FROM ing_municipio WHERE deleted = 0 AND ing_departamento_id_c = '{$id_department}' ORDER BY name;";
			$resultado = $db->query($sql);
			$cities = $resultado->fetchAll(PDO::FETCH_OBJ);
			echo '{"cities": ' . codificaJson($cities) . '}';
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function getTypeDocument() {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Obtener listas del language de SugarCRM		
			$data = get_data('http://10.1.xx.xx/xx/mexx/index.php?entryPoint=tipo_documento');
			echo '{"type_document": ' . codificaJson($data) . '}';
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function getTypeDocumentWs() {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Obtener listas del language de SugarCRM		
			$data = get_data('http://10.1.xx.xx/Mxx/mexx/index.php?entryPoint=tipo_documento_ws');
			return $data;
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function getCitiesWs() {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Obtener listas del language de SugarCRM		
			$data = get_data('http://10.1.x.x/Mxx/mexx/index.php?entryPoint=municipio_ws');
			return $data;
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function getCodeCellPhone() {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Obtener listas del language de SugarCRM		
			$data = get_data('http://10.1.x.x/Mxx/mexx/index.php?entryPoint=prefijo_movil');
			echo '{"code_cell_phone": ' . codificaJson($data) . '}';
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function getStatus() {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Obtener listas del language de SugarCRM		
			$data = get_data('http://10.1.x.x/Mxx/mexx/index.php?entryPoint=estado');
			echo '{"status": ' . codificaJson($data) . '}';
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function getQueryCases($consecutive) {
		try {
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			//Instancia de la conexión a la base de datos
			$db = getConnection();
						
			$sql = "
				SELECT
					case_number consecutive, ADDTIME(date_entered, '-05:00:00') date_created, status, resolution response
				FROM 
					cases
				WHERE 
					case_number = '{$consecutive}' 
					AND deleted = 0;
			";
			$resultado = $db->query($sql);
			$queryCases = $resultado->fetchAll(PDO::FETCH_OBJ);
			
			$status = array(
				'Aprobacion_respuesta_comunicaciones' => 'En progreso',
				'Cerrado_Cliente_no_satisfecho' => 'Cerrado',
				'Cerrado_cliente_satisfecho' => 'Cerrado',
				'Cierre_insatisfactorio_no_contactado' => 'Cerrado',
				'Escalado' => 'En progreso',
				'Pendiente_de_Respuesta_a_Usuario' => 'En progreso',
				'Pendiente_de_solucion' => 'Abierto',
				'Solucionado' => 'Cerrado'
			);
			if(array_key_exists($queryCases[0]->status, $status) ? $queryCases[0]->status = $status[$queryCases[0]->status] : $queryCases[0]->status = null);
			echo '{"queryCases": ' . codificaJson($queryCases) . '}';
		} catch(Exception $e) {
			echo '{"01": '.$e.'}';
			$ws->response->setStatus(500);
		}
	}
	
	
	function call_webservice($number_identification) {
		try {			
			$ws = \Slim\Slim::getInstance();			
			$client = new SoapClient('http://xx.xx.x8.xx:xx/RegistroPN.svc?singleWSDL', array(
				'location' => 'http://xx.xx.x8.xx:xx/RegistroPN.svc?singleWSDL',
				'trace'=>1,
				'exceptions'=>0,
				'style'    => SOAP_DOCUMENT,
				'use'      => SOAP_LITERAL,
				'soap_version' => SOAP_1_1,
			));
			 
			$consulta = array('numeroDocumento'=>$number_identification);
			$consultar=$client->ConsultarPersona($consulta);
			
			$keys = array('full_last_name', null, null, 'ing_barrio_id_c', 'city', 'emt_ocupacion_c', 'type_document', null, 'adress', 'email',
			    'emt_cuadrante_2_c', 'emt_cuadrante_1_c', null, 'emt_empresa_c', null, null, 'emt_estrato_c', 'emt_fecha_nacimiento_c', null,
			    null, null, 'emt_letra_1_c', 'emt_letra_2_c', 'cell_phone', null, 'full_name', 'number_identification', 'emt_numero_placa_c', 'emt_numero_via_gen_c', 
			    'emt_numero_via_ppal_c', 'description', null, null, 'code_cell_phone', 'emt_procedencia_c', 'emt_sexo_c', null, null, null, 'telephone', 'emt_tipo_via_c'
			);
			
			$array = array();
			if(isset($consultar->ConsultarPersonaResult->PersonaNatural)) {
				$i = 0;
				foreach ($consultar->ConsultarPersonaResult->PersonaNatural as $key=>$value){
					$array[$keys[$i]] = $value;
					$i++;
				}	
			}
			return $array;
			
		} catch (Exception $e) {
			$ws->response->setStatus(500);
			echo '{"01": '.$e.'}';
			die();
		}
	}
	
	
	function postSetCases() {		
		try{
			//Obtener la instacia actual del Slim para modificar el status
			$ws = \Slim\Slim::getInstance();
			$data = $ws->request()->post();
			
			$validations = array();
			if(isset($data['number_identification'])) $validations['number_identification'] = array(
				Validation::validate_maxlenght($data['number_identification'], 100),
				Validation::validate_digits($data['number_identification']),
			);
			if(isset($data['type_document']) ? $type_document = $data['type_document'] : $type_document = null);
			$validations['type_document'] = array(Validation::validate_required($type_document));
			if(isset($data['firt_name']) ? $firt_name = $data['firt_name'] : $firt_name = null);
			if(isset($data['second_name']) ? $second_name = $data['second_name'] : $second_name = null);
		/*	$validations['firt_name-second_name'] = array(
				Validation::validate_required($firt_name.$second_name),
				Validation::validate_maxlenght($firt_name.' '.$second_name, 150)
			);*/
			if(isset($data['last_name']) ? $last_name = $data['last_name'] : $last_name = null);
			if(isset($data['second_last_name']) ? $second_last_name = $data['second_last_name'] : $second_last_name = null);
			$validations['last_name-second_last_name'] = array(Validation::validate_maxlenght($last_name.' '.$second_last_name, 255));
			if(isset($data['adress'])) $validations['adress'] = array(Validation::validate_maxlenght($data['adress'], 255));
			if(isset($data['email'])) $validations['email'] = array(Validation::validate_email($data['email']));
			if(isset($data['telephone'])) $validations['telephone'] = array(
				Validation::validate_digits($data['telephone']),
				Validation::validate_maxlenght($data['telephone'], 7),
				Validation::validate_minlenght($data['telephone'], 7),
				Validation::validate_initial($data['telephone'])
			);
			if(isset($data['cell_phone'])) $validations['cell_phone'] = array(
				Validation::validate_digits($data['cell_phone']),
				Validation::validate_maxlenght($data['cell_phone'], 7),
				Validation::validate_minlenght($data['cell_phone'], 7)
			);
			if(isset($data['type_request']) ? $type_request = $data['type_request'] : $type_request = null);
			$validations['type_request'] = array(Validation::validate_required($type_request));
			if(isset($data['agree_terms']) ? $agree_terms = $data['agree_terms'] : $agree_terms = null);
			$validations['agree_terms'] = array(Validation::validate_required($agree_terms));
			if(isset($data['id_attached'])) {
				$validations['id_attached'] = array(
					Validation::validate_uuid($data['id_attached']),
					Validation::validate_attached($data['id_attached'])
				);
				if(isset($data['file_name']) ? $file_name = $data['file_name'] : $file_name = null);
				$validations['file_name'] = array(Validation::validate_required($file_name));
				if(isset($data['file_ext']) ? $file_ext = $data['file_ext'] : $file_ext = null);
				$validations['file_ext'] = array(
					Validation::validate_required($file_ext),
					Validation::validate_attached_ext($file_ext)
				);
				if(isset($data['file_mime_type']) ? $file_mime_type = $data['file_mime_type'] : $file_mime_type = null);
				$validations['file_mime_type'] = array(Validation::validate_required($file_mime_type));
			}
			
			$error = purge_array2(array_map("purge_array", $validations));
			
			if(count($error) > 0){
				echo '{"error": ' . codificaJson($error). '}';
				$ws->response->setStatus(400);	
			}else {
				//Instaciar conexión de base de datos.
				$db = getConnection();
					
				$sql = "SET autocommit=0;";
				$db->query($sql);
				
				// Consultar cuenta
				if(isset($data['type_document']) && isset($data['number_identification']) ? $account_id = findAccount($data['type_document'], $data['number_identification'], $db) : $account_id = null);
				if(empty($account_id)){
					if(isset($data['number_identification'])){
						$data_ws = call_webservice($data['number_identification']);
						if(!empty($data_ws)) {
							//Instanciar maestros ws de la cívica
							$TypeDocumentWs = getTypeDocumentWs();
							$CitiesWs = getCitiesWs();
							if(isset($data_ws['type_document'])) $data_ws['type_document'] = $TypeDocumentWs[$data_ws['type_document']];
							if(isset($data_ws['city'])) $data_ws['city'] = $CitiesWs[$data_ws['city']];
							$data_merge = array_merge($data, $data_ws);
							$data = purge_array3($data_merge);
						}
					}
						
					if(!isset($data['full_name'])){
						if(isset($data['firt_name'])) {
							$data['full_name'] = $data['firt_name'];
							if(isset($data['second_name'])) $data['full_name'] = $data['full_name'].' '.$data['second_name'];
						} else {
							if(isset($data['second_name'])) $data['full_name'] = $data['second_name'];	
						}	
					}
					
					if(!isset($data['full_last_name'])){
						if(isset($data['last_name'])) {
							$data['full_last_name'] = $data['last_name'];
							if(isset($data['second_last_name'])) $data['full_last_name'] = $data['full_last_name'].' '.$data['second_last_name'];
						} else {
							if(isset($data['second_last_name'])) $data['full_last_name'] = $data['second_last_name'];
						}	
					}
					
					//Insertar cuenta
					$account_id = generate_uuid();
					insertAccount($account_id, $data, $db);
				}					
				
				//Insertar caso
				$case_id = generate_uuid();
				insertCase($case_id, $account_id, $data, $db);
				
				
				//Insertar adjuntos
				$case_attached_id = generate_uuid();
				if(isset($data['id_attached'])){
					insertAttached($case_id, $case_attached_id, $data, $db);
				}
				
				//Cosultar información de la cita
				$case_data = findCase($case_id, $db);
				
				
				$sql = "COMMIT;";
				$db->query($sql);
				echo '{"consecutive": ' . codificaJson($case_data) . '}';
				
				$ws->response->setStatus(200);
			}
		} catch (Exception $e){
			echo '{"01": '.$e.'}';
			$sql = "ROLLBACK;";
			$db->query($sql);
			$ws->response->setStatus(500);
			die();
		}
	}
	
	
	function insertAccount($account_id, $data, $db) {
		try{
			$ws = \Slim\Slim::getInstance();
			
			$date_hour = date("Y-m-d H:i:s", strtotime('+5 hours'));
			$formulario_web = 'd5d80b95-044b-9cb0-eee2-57fb9c34e073';
			
			if(isset($data['telephone'])) {
				if(empty($data['telephone']) ? $emt_telefono_opc_c = 'NS' : $emt_telefono_opc_c = 'S');	
			}else {
				$emt_telefono_opc_c = 'NS';
			}
			if(isset($data['email'])) {
				if(empty($data['email']) ? $emt_correo_opc_c = 'NS' : $emt_correo_opc_c = 'S');	
			}else {
				$emt_correo_opc_c = 'NS';
			}
			if(isset($data['adress'])) {
				if(empty($data['adress']) ? $emt_direccion_opc_c = 'NS' : $emt_direccion_opc_c = 'S');	
			}else {
				$emt_direccion_opc_c = 'NS';
			}
			
			$sql = "
			INSERT INTO accounts 
				(id, name, date_entered, date_modified, modified_user_id, created_by, deleted)
			VALUES
				('{$account_id}','{$data['full_name']}', '{$date_hour}', '{$date_hour}', '{$formulario_web}', '{$formulario_web}', '0');
			";
			$db->query($sql);
						
			//Insertar cuenta custom
			$sql = "
			INSERT INTO accounts_cstm 
				(id_c, emt_documento_c, emt_apellido_c, emt_direccion_c, emp_tipo_doc_c, emt_correo_electronico_c, emt_telefono1_c, crm_depto_c, crm_mpio_c, emt_complemento_c, emt_correo_opc_c, emt_cuadrante_1_c, emt_cuadrante_2_c, emt_empresa_c, emt_estrato_c, emt_fecha_nacimiento_c, emt_letra_1_c, emt_letra_2_c, emt_movil_c, emt_movil_opc_c, emt_numero_placa_c, emt_numero_via_gen_c, emt_numero_via_ppal_c, emt_ocupacion_c, emt_prefijo_movil_c, emt_sexo_c, emt_telefono_opc_c, emt_tipo_via_c, emt_procedencia_c, ing_departamento_id_c, ing_municipio_id_c, ing_barrio_id_c, emt_direccion_opc_c, emt_tipo_solicitante_c, emt_acepta_terminos_c)
			VALUES
				('{$account_id}',
				 ".((isset($data['number_identification'])) ? "'".$data['number_identification']."'" : 'NULL').",
				 ".((isset($data['full_last_name'])) ? "'".$data['full_last_name']."'" : 'NULL').",
				 ".((isset($data['adress'])) ? "'".$data['adress']."'" : 'NULL').",
				 ".((isset($data['type_document'])) ? "'".$data['type_document']."'" : 'NULL').",
				 ".((isset($data['email'])) ? "'".$data['email']."'" : 'NULL').",
				 ".((isset($data['telephone'])) ? "'".$data['telephone']."'" : 'NULL').",
				 ".((isset($data['department'])) ? "'".$data['department']."'" : 'NULL').",
				 ".((isset($data['city'])) ? "'".$data['city']."'" : 'NULL').",
				 ".((isset($data['adress'])) ? "'".$data['adress']."'" : 'NULL').",
				 '{$emt_correo_opc_c}',
				 ".((isset($data['emt_cuadrante_1_c'])) ? "'".$data['emt_cuadrante_1_c']."'" : 'NULL').",
				 ".((isset($data['emt_cuadrante_2_c'])) ? "'".$data['emt_cuadrante_2_c']."'" : 'NULL').",
				 ".((isset($data['emt_empresa_c'])) ? "'".$data['emt_empresa_c']."'" : 'NULL').",
				 ".((isset($data['emt_estrato_c'])) ? "'".$data['emt_estrato_c']."'" : 'NULL').",
				 ".((isset($data['emt_fecha_nacimiento_c'])) ? "'".$data['emt_fecha_nacimiento_c']."'" : 'NULL').",
				 ".((isset($data['emt_letra_1_c'])) ? "'".$data['emt_letra_1_c']."'" : 'NULL').",
				 ".((isset($data['emt_letra_2_c'])) ? "'".$data['emt_letra_2_c']."'" : 'NULL').",
				 ".((isset($data['cell_phone'])) ? "'".$data['cell_phone']."'" : 'NULL').",
				 ".((isset($data['code_cell_phone'])) ? "'".$data['code_cell_phone']."'" : 'NULL').",
				 ".((isset($data['emt_numero_placa_c'])) ? "'".$data['emt_numero_placa_c']."'" : 'NULL').",
				 ".((isset($data['emt_numero_via_gen_c'])) ? "'".$data['emt_numero_via_gen_c']."'" : 'NULL').",
				 ".((isset($data['emt_numero_via_ppal_c'])) ? "'".$data['emt_numero_via_ppal_c']."'" : 'NULL').",
				 ".((isset($data['emt_ocupacion_c'])) ? "'".$data['emt_ocupacion_c']."'" : 'NULL').",
				 ".((isset($data['code_cell_phone'])) ? "'".$data['code_cell_phone']."'" : 'NULL').",
				 ".((isset($data['emt_sexo_c'])) ? "'".$data['emt_sexo_c']."'" : 'NULL').",
				 '{$emt_telefono_opc_c}',
				 ".((isset($data['emt_tipo_via_c'])) ? "'".$data['emt_tipo_via_c']."'" : 'NULL').",
				 ".((isset($data['emt_procedencia_c'])) ? "'".$data['emt_procedencia_c']."'" : 'NULL').",
				 ".((isset($data['department'])) ? "'".$data['department']."'" : 'NULL').",
				 ".((isset($data['city'])) ? "'".$data['city']."'" : 'NULL').",
				 ".((isset($data['ing_barrio_id_c'])) ? "'".$data['ing_barrio_id_c']."'" : 'NULL').",
				 '{$emt_direccion_opc_c}',
				 ".((isset($data['type_applicant'])) ? "'".$data['type_applicant']."'" : 'NULL').",
				 ".((isset($data['agree_terms'])) ? "'".$data['agree_terms']."'" : 'NULL').");
			";
			$db->query($sql);
				
		} catch(PDOException $e) {
			$ws->response->setStatus(500);
			echo '{"01": '.$e.'}';
			die();
		}
	}
	
	
	function insertCase($case_id, $account_id, $data, $db) {
		try{
			$ws = \Slim\Slim::getInstance();			
			
			$date_hour = date('Y-m-d H:i:s', strtotime('+5 hours'));
			$emt_fecha2_c = date('Y-m-d');
			$arbol_id = 'd0ee5cb7-adec-e5a6-f4fa-57fb9d09cf66'; //Formulario Web
			$formulario_web = 'd5d80b95-044b-9cb0-eee2-57fb9c34e073'; //Formulario Web
			$emt_canal_com_c = 'cfe00b94-a383-6594-61eb-580fb368c7ef';
			
			$sql = "
			SELECT
				CONCAT_WS('||', CONCAT_WS(' ', users.first_name, users.last_name), arb_arbol.name) arbol_nombre, CONCAT_WS('||', arb_arbol.id, arb_arbol.arb_arbol_id_c) arbol_ruta, arb_arbol.user_id_c user_id, arb_arbol.id
			FROM
				arb_arbol
				INNER JOIN users ON arb_arbol.user_id_c = users.id	
			WHERE
				users.deleted = '0'
				AND users.status = 'Active'
				AND arb_arbol.deleted = '0'
				AND arb_arbol.estado = 'ACTIVO'	
				AND arb_arbol.name = '{$data['type_request']}'
				AND arb_arbol.user_id_c = '{$formulario_web}'
			";
			$resultado = $db->query($sql);
			$arbol_data = $resultado->fetchAll(PDO::FETCH_OBJ);		
			
			$sql = "
			INSERT INTO cases 
				(id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, assigned_user_id, status, account_id)
			VALUES
				('{$case_id}',
				'Formulario web >> ".$data['type_request']."',
				'{$date_hour}',
				'{$date_hour}',
				'{$formulario_web}',
				'{$formulario_web}',
				".((isset($data['subject_request'])) ? "'".$data['subject_request']."'" : 'NULL').",
				'0',
				'{$arbol_data[0]->user_id}',
				'Pendiente_de_solucion',
				'{$account_id}');
			";
			$db->query($sql);
				
			//Insertar cuenta custom
			$sql = "
			INSERT INTO cases_cstm 
				(id_c, arb_arbol_id_c, arbol_ruta_nombres_c, arbol_ruta_id_c, documento_cliente_c, telefono_cliente_c, emt_canal_com_c, emt_fecha2_c, nombre_completo_c, emt_medio_respuesta_c)
			VALUES
				('{$case_id}',
				'{$arbol_data[0]->id}',
				'{$arbol_data[0]->arbol_nombre}',
				'{$arbol_data[0]->arbol_ruta}',
				".((isset($data['number_identification'])) ? "'".$data['number_identification']."'" : 'NULL').",
				".((isset($data['telephone'])) ? "'".$data['telephone']."'" : 'NULL').",
				'{$emt_canal_com_c}',
				'{$emt_fecha2_c}',
				".((isset($data['full_name'])) ? "'".$data['full_name']."'" : 'NULL').",
				".((isset($data['modo_response'])) ? "'".$data['modo_response']."'" : 'NULL').");
			";
			$db->query($sql);
			
		} catch(PDOException $e) {
			$ws->response->setStatus(500);
			echo '{"01": '.$e.'}';
			die();
		}
	}
	
	
	function insertAttached($case_id, $case_attached_id, $data, $db) {
		try{
			$ws = \Slim\Slim::getInstance();
			
			$date_hour = date('Y-m-d H:i:s', strtotime('+5 hours'));
			$active_date = date('Y-m-d');
			$formulario_web = 'd5d80b95-044b-9cb0-eee2-57fb9c34e073';
			
			$sql = "
			INSERT INTO emt_archivos_adjuntos 
				(id, date_entered, date_modified, modified_user_id, created_by, deleted, document_name, filename, file_ext, file_mime_type, active_date)
			VALUES
				('{$data['id_attached']}', '{$date_hour}', '{$date_hour}', '{$formulario_web}', '{$formulario_web}', '0', '{$data['file_name']}', '{$data['file_name']}', '{$data['file_ext']}', '{$data['file_mime_type']}', '{$active_date}');
			";
			$db->query($sql);
			
			$sql = "
			INSERT INTO cases_emt_archivos_adjuntos_1_c 
				(id, date_modified, deleted, cases_emt_archivos_adjuntos_1cases_ida, cases_emt_archivos_adjuntos_1emt_archivos_adjuntos_idb)
			VALUES
				('{$case_attached_id}', '{$date_hour}', '0', '{$case_id}', '{$data['id_attached']}');
			";
			$db->query($sql);
				
		} catch(PDOException $e) {
			$ws->response->setStatus(500);
			echo '{"01": '.$e.'}';
			die();
		}
	}
	
	
	function findAccount($type_document, $number_identification, $db){
		try {			
			$ws = \Slim\Slim::getInstance();
			
			$sql = "
			SELECT
				id
			FROM
				accounts INNER JOIN accounts_cstm ON id = id_c
			WHERE
				deleted = '0'
				AND emt_documento_c = '{$number_identification}'
				AND emp_tipo_doc_c = '{$type_document}';
			";
			$resultado = $db->query($sql);
			$existe = $resultado->fetchAll(PDO::FETCH_OBJ);			
			return (count($existe) > 0 ? $existe[0]->id : null);
		} catch(Exception $e) {
			$ws->response->setStatus(500);
			echo '{"01": '.$e.'}';
			die();
		}
	}
	
	
	function findCase($case_id, $db) {
		try {			
			$ws = \Slim\Slim::getInstance();
			
			$sql = "
			SELECT
				case_number
			FROM
				cases
				INNER JOIN cases_cstm ON id = id_c
			WHERE
				id = '{$case_id}';
			";
			$resultado = $db->query($sql);
			$existe = $resultado->fetchAll(PDO::FETCH_OBJ);
			return (count($existe) > 0 ? $existe[0]->case_number : null);
		} catch(Exception $e) {
			$ws->response->setStatus(500);
			echo '{"01": '.$e.'}';
			die();
		}
	}
	
?>