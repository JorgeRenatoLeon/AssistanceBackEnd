<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_disponibilidad
 * @property int $id_usuario
 * @property int $id_programa
 * @property string $fecha
 * @property string $hora_inicio
 * @property string $estado
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property string $tipo_disponibilidad
 * @property Usuario $usuario
 * @property Programa $programa
 * @property Citum[] $citas
 */
class Disponibilidad extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'disponibilidad';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_disponibilidad';

    /**
     * @var array
     */
    protected $fillable = ['id_usuario', 'id_programa', 'fecha', 'hora_inicio', 'estado', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'tipo_disponibilidad'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'id_usuario', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function programa()
    {
        return $this->belongsTo('App\Programa', 'id_programa', 'id_programa');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citas()
    {
        return $this->hasMany('App\Cita', 'id_disponibilidad', 'id_disponibilidad');
    }
}
