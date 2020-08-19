<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_plan_accion
 * @property int $id_tutor
 * @property int $id_alumno
 * @property int $id_programa
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha_inicio
 * @property string $estado
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property Usuario $usuarioTutor
 * @property Usuario $usuarioAlumno
 * @property Compromiso[] $compromisos
 */
class PlanAccion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plan_accion';

    public $timestamps = false;
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_plan_accion';

    /**
     * @var array
     */
    protected $fillable = ['id_tutor', 'id_programa','fecha_inicio','id_alumno', 'nombre', 'descripcion', 'estado', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioTutor()
    {
        return $this->belongsTo('App\Usuario', 'id_tutor', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioAlumno()
    {
        return $this->belongsTo('App\Usuario', 'id_alumno', 'id_usuario');
    }

    public function programa()
    {
        return $this->belongsTo('App\Programa', 'id_programa', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function compromisos()
    {
        return $this->hasMany('App\Compromiso', 'id_plan_accion', 'id_plan_accion');
    }
}
