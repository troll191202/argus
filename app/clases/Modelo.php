<?php

namespace Clase;

use Clase\Database;
use Clase\Validaciones;
use Clase\GeneraConsultas;
use Error\Base AS ErrorBase;
use Error\Esperado AS ErrorEsperado;

class Modelo 
{
    private Validaciones $valida;
    private GeneraConsultas $generaConsulta;
    private string $tabla;
    private Database $coneccion;
    private array $columnasUnicas;
    private array $columnasObligatorias;
    private array $columnasProtegidas;
    private array $relaciones;

    public function __construct( Database $coneccion ,string $tabla ,array $relaciones, array $columnas )
    {
        $this->valida  = new Validaciones();
        $this->generaConsulta = new GeneraConsultas();
        $this->coneccion = $coneccion;
        $this->tabla = $tabla;
        $this->relaciones = $relaciones;
        $this->columnasUnicas = $columnas['unicas'];
        $this->columnasObligatorias = $columnas['obligatorias'];
        $this->columnasProtegidas = $columnas['protegidas'];
    }

    public function registrarBd($datos)
    {
        try{
            $this->validaColumnasObligatorias( $this->columnasObligatorias , $datos );
        }catch(ErrorBase $e){
            throw new ErrorBase($e->getMessage(),$e);
        }

        try{
            $this->validaColunmasUnicas($datos);
        }catch(ErrorBase $e){
            throw new ErrorEsperado($e->getMessage(),$e);
        }

        try{
            $consulta = $this->generaConsulta->insert($this->tabla,$datos);
        }catch(ErrorBase $e){
            throw new ErrorBase($e->getMessage(),$e);
        }

        try{
            $resultado = $this->coneccion->ejecutaConsultaInsert($consulta,$datos);
        }catch(ErrorBase $e){
            throw new ErrorBase($e->getMessage(),$e);
        }
        
        return $resultado;
    }

    private function validaColunmasUnicas($datos, $registro_id = 0)
    {
        $columnas = [$this->tabla.'.id'];
        foreach ($this->columnasUnicas as $nombreColumnaunica => $columnaUnica)
        {
            $filtros = [
                ['campo' => $columnaUnica , 'valor' =>  $datos[$columnaUnica] , 'signoComparacion' => '='],
                ['campo' => 'id' , 'valor' =>  $registro_id , 'signoComparacion' => '<>']
            ];

            $datosGenerados = [
                $columnaUnica => $datos[$columnaUnica],
                'id' => $registro_id
            ];

            try{
                $consulta = $this->generaConsulta->select($this->tabla,$columnas,$filtros);
            }catch(ErrorBase $e){
                throw new ErrorBase($e->getMessage(),$e);
            }

            try{
                $resultado = $this->coneccion->ejecutaConsultaSelect($consulta,$datosGenerados);
            }catch(ErrorBase $e){
                throw new ErrorBase($e->getMessage(),$e);
            } 

            if ($resultado['n_registros'] != 0)
            {
                throw new ErrorEsperado($nombreColumnaunica.':'.$datos[$columnaUnica].' ya registrad@');
            }

        }
    }

    private function validaColumnasObligatorias($columnasObligatorias,$datos)
    {
        foreach($columnasObligatorias as $columnaObligatoria)
        {
            if (!array_key_exists($columnaObligatoria, $datos))
            {
                throw new ErrorBase("El campo $columnaObligatoria debe existir en el array de datos");
            }
            if ( is_null($datos[$columnaObligatoria]) ||  $datos[$columnaObligatoria] == '' )
            {
                throw new ErrorBase("El campo $columnaObligatoria no pude ser vacio o null");
            }
        }
    }

}