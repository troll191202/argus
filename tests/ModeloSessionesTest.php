<?php

use Modelo\Sessiones;
use Error\Base AS ErrorBase;
use PHPUnit\Framework\TestCase;

class ModeloSessionesTest extends TestCase
{
    /**
     * @test
     */
    public function creaModelo()
    {
        $this->assertSame(1,1);
        $claseDatabase = 'Clase\\'.DB_TIPO.'\\Database';
        $coneccion = new $claseDatabase();

        $claseGeneraConsultas = 'Clase\\'.DB_TIPO.'\\GeneraConsultas';
        $generaConsultas = new $claseGeneraConsultas($coneccion);
        
        $coneccion->ejecutaConsultaDelete('DELETE FROM sessiones');
        return new Sessiones($coneccion,$generaConsultas);
    }

    /**
     * @test
     * @depends creaModelo
     */
    public function buscarTodo($modelo)
    {
        $resultado = $modelo->buscarTodo();
        $this->assertIsArray($resultado);
        $this->assertSame(0,$resultado['n_registros']);
        $this->assertCount(0,$resultado['registros']);
    }
}