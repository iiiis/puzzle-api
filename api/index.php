<?php

require "../start.php";

use Src\Puzzle;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$uri = explode( '/', $uri );

// endpoints start with '/puzzle' or '/puzzles' for GET shows all posts
// everything else results in a 404 not found
if ( $uri[1] !== 'puzzle' ) {
    if ( $uri[1] !== 'puzzles' ) {
        header( 'HTTP/1.1 404 Not Found' );
        exit();
    }
}

// endpoints starting with '/puzzles' for POST/PUT/DELETE results in a 404 Not Found
if ( $uri[1] == 'puzzles' && isset( $uri[2] ) ) {
    header( 'HTTP/1.1 404 Not Found' );
    exit();
}

// the puzzle id is, of course, optional and must be a number
$puzzleId = null;

if ( isset( $uri[2] ) ) {
    $puzzleId = (int) $uri[2];
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

$controller = new Puzzle( $dbConnection, $requestMethod, $puzzleId );
$controller->processRequest();