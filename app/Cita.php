<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_cita
 * @property int $id_tipo_tutoria
 * @property int $id_disponibilidad
 * @property string $nota
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property string $estado
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property TipoTutorium $tipoTutorium
 * @property Disponibilidad $disponibilidad
 * @property Usuario[] $citaXUsuarios
 * @property Sesion $sesion
 */
class Cita extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cita';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_cita';

    /**
     * @var array
     */
    protected $fillable = ['id_tipo_tutoria', 'id_disponibilidad', 'nota', 'fecha_creacion', 'fecha_actualizacion', 'estado', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoTutoria()
    {
        return $this->belongsTo('App\TipoTutoria', 'id_tipo_tutoria', 'id_tipo_tutoria');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disponibilidad()
    {
        return $this->belongsTo('App\Disponibilidad', 'id_disponibilidad', 'id_disponibilidad');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function citaXUsuarios()
    {
        return $this->belongsToMany('App\Usuario', 'cita_x_usuario', 'id_cita', 'id_usuario')
            ->withPivot('asistencia');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sesion()
    {
        return $this->hasOne('App\Sesion', 'id_sesion', 'id_cita');
    }
}
