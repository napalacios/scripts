<?php

	
	//SCRIPT PHP DE MIGRACION DE UNA BASE DE DATOS DE UNA UNICA TABLA A UNA RELACIONAL, DE UN SISTEMA DE RECLAMOS ANTIGUO A UNA VERSION MEJORADA. 
	
	set_time_limit(0);

	require_once('conexion.php');
	
	$dbh = conexion::getInstance();
	
	$query = "SELECT * FROM sistema_anterior.reclamo";
	
	$sth = $dbh->prepare($query);
	$sth->execute();
	$reclamos = $sth->fetchAll();
	
	$estado = 0;
	
	foreach($reclamos as $reclamo){
		
		$query = "SELECT us.id FROM sistema_nuevo.usuarios us WHERE us.dni = :DNI";
			
		$sth = $dbh->prepare($query);
	
		$sth->bindParam(':DNI', $reclamo['dni'], PDO::PARAM_INT);
	
		$sth->execute();
		
		if($sth->rowCount()== 0){
			
			$query = "INSERT INTO sistema_nuevo.usuarios (nombre_apellido, dni, interno, email) VALUE (:NOMBRE,:DNI,:INTERNO,:EMAIL)";
			
			$sth = $dbh->prepare($query);
			
			$sth->bindParam(':NOMBRE', $reclamo['nombre'], PDO::PARAM_STR);
			$sth->bindParam(':DNI', $reclamo['dni'], PDO::PARAM_INT);
			$sth->bindParam(':INTERNO', $reclamo['interno'], PDO::PARAM_STR);
			$sth->bindParam(':EMAIL', $reclamo['email'], PDO::PARAM_STR);
			
			$sth->execute();
			
			$user_id = $dbh->lastInsertId();
					
		}else{
		
			$resultado = $sth->fetch();
			$user_id = $resultado['id'];
			
		}
		
		// OBTENGO EL ID DEL ESTADO DEL RECLAMO
		if($reclamo['codigo_estado'] == 'P'){
			
			$id_estado = 1;	
				
		}elseif($reclamo['codigo_estado'] == 'V'){
			
			$id_estado = 2;
			
		}elseif($reclamo['codigo_estado'] == 'N'){
			
			$id_estado = 3;
			
		}elseif($reclamo['codigo_estado'] == 'R'){
			
			$id_estado = 4;
			
		}
		
		$query = "INSERT INTO sistema_nuevo.reclamos (id_estado, id_usuario, fecha_reclamo, fecha_problema, hora_desde, hora_hasta, problema, detalle, puerta_descripcion, respuesta)
				  VALUE (:ID_ESTADO,:ID_USUARIO,:FECHA_RECLAMO,:FECHA_PROBLEMA,:HORA_DESDE,:HORA_HASTA,:PROBLEMA,:DETALLE,:PUERTA_DESCRIPCION,:RESPUESTA)";
			
		if ( $reclamo['respuesta'] == 'NULL' OR $reclamo['respuesta'] == '(NULL)' OR $reclamo['respuesta'] == '') {
			$reclamo_respuesta = NULL;
		} else {
			$reclamo_respuesta = $reclamo['respuesta'];
		}
		
		
		$sth = $dbh->prepare($query);
		
		$sth->bindParam(':ID_ESTADO', $id_estado, PDO::PARAM_INT);
		$sth->bindParam(':ID_USUARIO', $user_id, PDO::PARAM_INT);
		$sth->bindParam(':FECHA_RECLAMO', $reclamo['fecha'], PDO::PARAM_STR);
		$sth->bindParam(':FECHA_PROBLEMA', $reclamo['fecha_reclamo'], PDO::PARAM_STR);
		$sth->bindParam(':HORA_DESDE', $reclamo['hora_desde'], PDO::PARAM_STR);
		$sth->bindParam(':HORA_HASTA', $reclamo['hora_hasta'], PDO::PARAM_STR);
		$sth->bindParam(':PROBLEMA', $reclamo['detalle'], PDO::PARAM_STR);
		$sth->bindParam(':DETALLE', $reclamo['observaciones'], PDO::PARAM_STR);
		$sth->bindParam(':PUERTA_DESCRIPCION', $reclamo['puerta'], PDO::PARAM_STR);
		$sth->bindParam(':RESPUESTA', $reclamo_respuesta, PDO::PARAM_STR);
		
		$sth->execute();	
		
		$estado = $estado + 1;
		
		echo '<tt>' . $estado . ' rows migradas </tt><br>';
	}
	
	echo "<br><tt>FIN</tt>";
?>	