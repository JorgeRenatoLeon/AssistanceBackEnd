<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_modulo_permiso
 * @property string $nombre
 * @property string $estado
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property Permiso[] $permisos
 */
class ModuloPermiso extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'modulo_permiso';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_modulo_permiso';

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'estado', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permisos()
    {
        return $this->hasMany('App\Permiso', 'id_modulo_permiso', 'id_modulo_permiso');
    }
}
