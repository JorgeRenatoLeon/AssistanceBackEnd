<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_unidad_apoyo
 * @property string $nombre
 * @property string $nombre_contacto
 * @property string $correo_contacto
 * @property string $estado
 * @property string $telefono_contacto
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property Programa[] $programas
 */
class UnidadApoyo extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'unidad_apoyo';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_unidad_apoyo';

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'nombre_contacto', 'correo_contacto', 'telefono_contacto', 'estado', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function programas()
    {
        return $this->belongsToMany('App\Programa', 'unidad_apoyo_x_programa', 'id_unidad_apoyo', 'id_programa');
    }
}
