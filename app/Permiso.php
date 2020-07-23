<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_permiso
 * @property int $id_modulo_permiso
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property string $estado
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property ModuloPermiso $moduloPermiso
 * @property TipoUsuario[] $tipoUsuarios
 */
class Permiso extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permiso';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_permiso';

    /**
     * @var array
     */
    protected $fillable = ['id_modulo_permiso', 'nombre', 'descripcion', 'fecha_creacion', 'fecha_actualizacion', 'estado', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moduloPermiso()
    {
        return $this->belongsTo('App\ModuloPermiso', 'id_modulo_permiso', 'id_modulo_permiso');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tipoUsuarios()
    {
        return $this->belongsToMany('App\TipoUsuario', 'permiso_x_tipo_usuario', 'id_permiso', 'id_tipo_usuario');
    }
}
