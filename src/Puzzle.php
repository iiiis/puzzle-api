<?php

namespace Src;

class Puzzle {
    private $db;
    private $requestMethod;
    private $puzzleId;

    public function __construct($db, $requestMethod, $puzzleId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->puzzleId = $puzzleId;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ( $this->puzzleId ) {
                    $response = $this->getPuzzle($this->puzzleId);
                } else {
                    $response = $this->getAllPuzzles();
                }
                break;
            case 'POST':
                $response = $this->createPuzzle();
                break;
            case 'PUT':
                $response = $this->updatePuzzle( $this->puzzleId );
                break;
            case 'DELETE':
                $response = $this->deletePuzzle( $this->puzzleId );
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ( $response['body'] ) {
            echo $response['body'];
        }
    }

    private function getAllPuzzles()
    {
        $query = "SELECT id, title, added FROM puzzles";

        try {
            $statement = $this->db->query($query);
            $result    = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit( $e->getMessage() );
        }

        return array(
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode( $result )
        );
    }

    private function getPuzzle( $id )
    {
        $result = $this->find( $id );
        if ( ! $result ) {
            return $this->notFoundResponse();
        }

        return array(
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode( $result )
        );
    }

    private function createPuzzle()
    {
        $input = (array) json_decode( file_get_contents( 'php://input' ), TRUE );
        if ( ! $this->validatePuzzle( $input ) ) {
            return $this->unprocessableEntityResponse();
        }

        $query = 'INSERT INTO puzzles (title) VALUES (:title)';

        try {
            $statement = $this->db->prepare($query);
            $statement->execute( array( 'title' => $input['title'] ) );
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit( $e->getMessage() );
        }

        return array(
            'status_code_header' => 'HTTP/1.1 201 Created',
            'body' => json_encode( array( 'message' => 'Puzzle Created' ) )
        );
    }

    private function updatePuzzle( $id )
    {
        $result = $this->find( $id );
        if ( ! $result ) {
            return $this->notFoundResponse();
        }

        $input = (array) json_decode( file_get_contents( 'php://input' ), TRUE );
        if ( ! $this->validatePuzzle( $input ) ) {
            return $this->unprocessableEntityResponse();
        }

        $query = 'UPDATE puzzles SET title = :title WHERE id = :id';

        try {
            $statement = $this->db->prepare($query);
            $statement->execute( array( 'id' => $id, 'title' => $input['title'] ) );
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit( $e->getMessage() );
        }

        return array(
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode( array( 'message' => 'Puzzle Updated!' ) )
        );
    }

    private function deletePuzzle( $id )
    {
        $result = $this->find( $id );
        if ( ! $result ) {
            return $this->notFoundResponse();
        }

        $query = "DELETE FROM puzzles WHERE id = :id";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute( array( 'id' => $id ) );
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit( $e->getMessage() );
        }

        return array(
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode( array( 'message' => 'Puzzle Deleted!' ) )
        );
    }

    private function find( $id )
    {
        $query = "SELECT id, title, added FROM puzzles WHERE id = :id";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute( array( 'id' => $id ) );
            $result    = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit( $e->getMessage() );
        }
    }

    private function validatePuzzle( $input )
    {
        if ( ! isset( $input['title'] ) ) {
            return false;
        }

        return true;
    }

    private function unprocessableEntityResponse()
    {
        return array(
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode( array( 'error' => 'Invalid input' ) )
        );
    }

    private function notFoundResponse()
    {
        return array(
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => null
        );
    }
}