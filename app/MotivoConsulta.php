<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_motivo_consulta
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property string $nombre
 * @property string $estado
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property Sesion[] $sesions
 */
class MotivoConsulta extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'motivo_consulta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_motivo_consulta';

    /**
     * @var array
     */
    protected $fillable = ['fecha_creacion', 'fecha_actualizacion', 'nombre', 'estado', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sesions()
    {
        return $this->belongsToMany('App\Sesion', 'sesion_x_motivo_consulta', 'id_motivo_consulta', 'id_sesion');
    }
}
