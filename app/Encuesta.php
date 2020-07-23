<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_encuesta
 * @property int $orden
 * @property string $pregunta
 * @property string $tipo
 * @property string $estado
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property PreguntasXAlumno[] $preguntasXAlumnos
 */
class Encuesta extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'encuesta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_encuesta';

    /**
     * @var array
     */
    protected $fillable = ['orden', 'pregunta', 'tipo', 'estado', 'fecha_creacion', 'fecha_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function preguntasXAlumnos()
    {
        return $this->hasMany('App\PreguntasXAlumno', 'id_encuesta', 'id_encuesta');
    }


}
