<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_sesion
 * @property string $resultado
 * @property string $estado
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property Citum $citum
 * @property MotivoConsultum[] $motivoConsultas
 */
class Sesion extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sesion';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_sesion';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['resultado', 'estado', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citum()
    {
        return $this->belongsTo('App\Citum', 'id_sesion', 'id_cita');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function motivoConsultas()
    {
        return $this->belongsToMany('App\MotivoConsulta', 'sesion_x_motivo_consulta', 'id_sesion', 'id_motivo_consulta');
    }
}
