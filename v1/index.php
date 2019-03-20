<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

date_default_timezone_set("America/Santiago");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once dirname( __FILE__ ).'/../include/Config.php';
require '../libs/vendor/autoload.php';

//use \Slim\Middleware\JwtAuthentication;

$container = new \Slim\Container;
$app = new \Slim\App($container);

//Dependencia BBDD
$container = $app->getContainer();
$container['db'] = function ($container) {
	//global $db;
	$db = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if (!$db) {
    	//return mysqli_connect_error();
		return false;
	}
    return $db;
};
// autentificacion
/*
$container["jwt"] = function ($container) {
    return new StdClass;
};

$app->add(new \Slim\Middleware\JwtAuthentication([
    "secret" => API_KEY,
	"secure" => false,
	"algorithm" => ["HS256"],
	"callback" => function ($request, $response, $arguments) use ($container) {
        $container["jwt"] = $arguments["decoded"];
    },
	"error" => function ($request, $response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));
*/
//inicio
$app->get('/appointment', function (Request $request,  Response $response, $args = []) {
	$db=$this->get('db');
	if($db === false){
		return mysqli_connect_error();
	}
	$message = '';
	$values = array();
	$query = "SELECT email,fecha FROM libros Limit 1";
	//--
	$error=true;
	if ($result = mysqli_query($db, $query)) {
		$book = mysqli_fetch_object($result);
		//$book->idlibros;
		$error = false;
		$message = $book->email;
		$values["date"] = strtotime($book->fecha);
		mysqli_free_result($result);			
	}
	//respons
	$values["error"] = $error;
	$values["return"] = $message;
	//--
	return echoResponse(200, $values, $response);
});
$app->get('/appointments', function (Request $request,  Response $response, $args = []) {
	$db=$this->get('db');
	if($db === false){
		return mysqli_connect_error();
	}
	$message = 'No booking for this email';
	$error=true;
	$values = array();
	$rt=verifyRequiredParams(array('email'),$response,$request);
	if($rt !== true){
		return $rt;
	}
	//$data = $request->getParsedBody();
	$param['email'] = filter_var($_GET['email'], FILTER_SANITIZE_STRING);	
	$query = "SELECT * FROM libros WHERE email='{$param['email']}'";
	//--
	//$values["query"] = $query;	
	$error=true;
	$booking=array();
	if ($result = mysqli_query($db, $query)) {
		while($book = mysqli_fetch_object($result)){
			array_push($booking,array(
				'id'=>$book->idlibros,
				'date'=>strtotime($book->fecha)
			));
			$error = false;
			$message = 'booking for this email';
		}
		$values["booking"] = $booking;
		mysqli_free_result($result);			
	}
	//respons
	$values["error"] = $error;
	$values["return"] = $message;
	//--
	return echoResponse(200, $values, $response);
});
$app->delete('/appointment', function (Request $request,  Response $response, $args = []) {
	$db=$this->get('db');
	if($db === false){
		return mysqli_connect_error();
	}
	$message = 'Error Deleting';
	$error=true;
	$values = array();
	$rt=verifyRequiredParams(array('id'),$response,$request);
	if($rt !== true){
		return $rt;
	}
	$data = $request->getParsedBody();
	$param['id'] = filter_var($data['id'], FILTER_SANITIZE_STRING);
	
	//$query = "SELECT idlibros FROM libros WHERE fecha < DATE_SUB(NOW(), INTERVAL 1 HOUR) AND email='{$param['email']}' LIMIT 1";
	$query = "DELETE FROM libros WHERE id={$param['id']}";
	
	if ($result = mysqli_query($db, $query)) {
		$error=false;
		$message="appointment delete";
	}
	mysqli_free_result($result);
	//respons
	$values["error"] = $error;
	$values["return"] = $message;
	//--
	return echoResponse(200, $values, $response);
});
function addLibro($email,$date){
	global $container;
	$db=$container->get('db');
	if($db === false){
		return mysqli_connect_error();
	}
	$fecha = date('Y-m-d H:i:s',strtotime($date));
	$query = "INSERT INTO libros (email,fecha) VALUES ('{$email}','{$fecha}')";
	if ($result = mysqli_query($db, $query)) {
		return true;
	}else{
		return mysqli_error($db);
	}
}
/*

   _____                      _               
  / ____|                    (_)              
 | |  __  ___ _ __   ___ _ __ _  ___ __ _ ___ 
 | | |_ |/ _ \ '_ \ / _ \ '__| |/ __/ _` / __|
 | |__| |  __/ | | |  __/ |  | | (_| (_| \__ \
  \_____|\___|_| |_|\___|_|  |_|\___\__,_|___/
                                              
                                              

*/
function echoResponse($status_code, $values, $response) {	
	// Http response code
	$nresp = $response->withStatus($status_code)
					  ->withJson($values);
	return $nresp;
}
function verifyRequiredParams($required_fields, $response, $request='') {
	$error = false;
	$error_fields = "";
	$request_params = array();
	$request_params = $_REQUEST;
	// Handling PUT request params
	if ( $_SERVER[ 'REQUEST_METHOD' ] == 'PUT' ) {
		$request_params=$request->getParsedBody();
	}
	foreach ( $required_fields as $field ) {
		if ( !isset( $request_params[ $field ] ) || strlen( trim( $request_params[ $field ] ) ) <= 0 ) {
			$error = true;
			$error_fields .= $field . ', ';
		}
	}

	if ( $error ) {
		// Required field(s) are missing or empty
		// echo error json and stop the app
		$values = array();
		$values[ "error" ] = true;
		$values[ "message" ] = 'Required field(s) ' . substr( $error_fields, 0, -2 ) . ' is missing or empty';
		$values[ "params" ] = $request_params;
		$values[ "response" ] = $response;
		$values[ "request" ] = $request;
		return echoResponse( 400, $values, $response );
		//$res->stop();
	}
	return true;
}
function checkDiaSemana($fecha){
	$dia=date('N',strtotime($fecha));
	if($dia<6){
		$hora=date('G',strtotime($fecha));
		if( $hora >= 9 && $hora <=18 ){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
/*
function authenticate(\Slim\Route $route) {
	// Getting request headers
	$headers = apache_request_headers();
	$response = array();
	$app = \Slim\Slim::getInstance();

	// Verifying Authorization Header
	if ( isset( $headers[ 'Authorization' ] ) ) {
		//$db = new DbHandler(); //utilizar para manejar autenticacion contra base de datos

		// get the api key
		$token = $headers[ 'Authorization' ];

		// validating api key
		if ( !( $token == API_KEY ) ) { //API_KEY declarada en Config.php

			// api key is not present in users table
			$response[ "error" ] = true;
			$response[ "message" ] = "Acceso denegado. Token inválido";
			return echoResponse( 401, $response );

		} else {
			//procede utilizar el recurso o metodo del llamado
		}
	} else {
		// api key is missing in header
		$response[ "error" ] = true;
		$response[ "message" ] = "Falta token de autorización";
		return echoResponse( 400, $response );
	}
}*/
$app->run();
//echo 'Hola Mundo';