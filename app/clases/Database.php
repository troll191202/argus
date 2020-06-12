<?php

namespace Clase;

use PDO;
use PDOException;
use Clase\Error;

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $password = DB_PASSWORD;
    private $name = DB_NAME;

    private $dbh;
    private $stmt;

    public function __construct($usuario = DB_USER,$password = DB_PASSWORD,$name = DB_NAME, $host = DB_HOST)
    {
        $this->host = $host;
        $this->user = $usuario;
        $this->password = $password;
        $this->name = $name;
        
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->name;
        $opciones = array(PDO::ATTR_PERSISTENT=>true, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION);

        try
        {
            $this->dbh = new PDO($dsn,$this->user,$this->password,$opciones);
            $this->dbh->exec('set names utf8');
        } 
        catch (PDOException $e) 
        {
            throw new Error($e->getMessage(), null ,$e->getCode());
        }
    }

    public function ejecutaQuery(string $query_string = '' ,array $datos = array())
    {
        if( $query_string === ''){
            throw new Error('La query no puede estar vacia');
        }

        $explode_query = explode(' ',$query_string);

        if( count($explode_query) < 2 )
        {
            throw new Error('Query no valida');
        }

        $tipo_consulta = $explode_query[0];

        $this->stmt = $this->dbh->prepare($query_string);

        if (count($datos) != 0){
            foreach ( $datos as $campo => $valor){
                $campo_explode = explode('.',$campo);
                $numero = count($campo_explode);
                if ($numero > 1){
                    $this->stmt->bindValue(':'.$campo_explode[($numero-1)],$valor);
                }else{
                    $this->stmt->bindValue(':'.$campo,$valor);
                }

            }
        }

        try 
        {
            
            if ($tipo_consulta === 'SELECT')
            {
                $this->stmt->execute();
                $n_registros = $this->stmt->rowCount();
                $resultado = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                return array('registros' => $resultado,'n_registros'=>$n_registros);
            }

            if ($tipo_consulta === 'UPDATE')
            {
                $this->stmt->execute();
                return array('mensaje' => 'registro modificado');
            }

            if ($tipo_consulta === 'INSERT')
            {
                $this->stmt->execute();
                $registro_id = (int) $this->dbh->lastInsertId();
                return array(
                    'mensaje' => 'registro insertado',
                    'registro_id' =>$registro_id
                );
            }

            if ($tipo_consulta === 'DELETE')
            {
                $this->stmt->execute();
                return array('mensaje' => 'registro eliminado');
            }
            throw new Error('Consulta no valida: '.$query_string);
        } 
        catch (PDOException $e)
        {
            throw new Error($e->getMessage(), null ,$e->getCode());
        }

    }

}