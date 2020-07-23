<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_tutor
 * @property int $id_alumno
 * @property int $id_programa
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property int $id_tipo_tutoria
 * @property string $estado
 * @property Usuario $tutor
 * @property Programa $programa
 * @property Usuario $alumno
 * @property TipoTutoria $tipoTutoria
 */
class RegistroAlumno extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;

    protected $table = 'registro_alumno';

    protected $primaryKey = 'id_alumno';
    /**
     * @var array
     */
    protected $fillable = ['fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'estado'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function alumno()
    {
        return $this->belongsTo('App\Usuario', 'id_alumno', 'id_usuario');
    }

    public function tutor()
    {
        return $this->belongsTo('App\Usuario', 'id_tutor', 'id_usuario');
    }

    public function programa(){
        return $this->belongsTo('App\Programa', 'id_programa', 'id_programa');
    }

    public function tipoTutoria()
    {
        return $this->belongsTo('App\TipoTutoria', 'id_tipo_tutoria', 'id_tipo_tutoria');
    }
}
