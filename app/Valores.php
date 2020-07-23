<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tabla
 * @property string $abreviatura
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 */
class Valores extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['tabla', 'abreviatura', 'nombre', 'descripcion', 'fecha_creacion', 'fecha_actualizacion'];

}
