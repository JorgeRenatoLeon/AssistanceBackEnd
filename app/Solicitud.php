<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_solicitante
 * @property int $id_remitente
 * @property int $id_programa
 * @property int $id_usuario_relacionado
 * @property int $id_cita
 * @property string $tipo_solicitud
 * @property string $descripcion
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property string $estado
 * @property string $motivo
 * @property Usuario $usuarioSol
 * @property Usuario $usuarioRem
 * @property Usuario $usuarioRel
 * @property Cita $cita
 * @property Programa $programa
 */
class Solicitud extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_solicitante';
    protected $table = 'solicitud';
    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = ['id_usuario_relacionado','id_cita', 'tipo_solicitud', 'descripcion', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'estado', 'motivo'];

    protected $casts = [
        'fecha_creacion'  => 'date:d/m/Y',
        'fecha_actualizacion' => 'date:d/m/Y',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioSol()
    {
        return $this->belongsTo('App\Usuario', 'id_solicitante', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioRem()
    {
        return $this->belongsTo('App\Usuario', 'id_remitente', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioRel()
    {
        return $this->belongsTo('App\Usuario', 'id_usuario_relacionado', 'id_usuario');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cita()
    {
        return $this->belongsTo('App\Cita', 'id_cita', 'id_cita');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function programa()
    {
        return $this->belongsTo('App\Programa', 'id_programa', 'id_programa');
    }
}
