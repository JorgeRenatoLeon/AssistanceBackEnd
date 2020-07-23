<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_tipo_tutoria
 * @property int $id_programa
 * @property string $nombre
 * @property string $descripcion
 * @property string $obligatorio
 * @property string $individual
 * @property string $planificado
 * @property string $tutor_asignado
 * @property string $tutor_fijo
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property string $estado
 * @property Programa $programa
 * @property Citum[] $citas
 * @property Usuario[] $usuarios
 */
class TipoTutoria extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tipo_tutoria';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_tipo_tutoria';

    /**
     * @var array
     */
    protected $fillable = ['id_programa', 'bajo_rendimiento','nombre', 'descripcion', 'obligatorio', 'individual', 'planificado', 'tutor_asignado', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'estado','tutor_fijo'];

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
        return $this->hasMany('App\Citum', 'id_tipo_tutoria', 'id_tipo_tutoria');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usuarios()
    {
        return $this->belongsToMany('App\Usuario', 'tipo_tutoria_x_usuario', 'id_tipo_tutoria', 'id_usuario');
    }
}
