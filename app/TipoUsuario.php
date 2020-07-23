<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_tipo_usuario
 * @property string $descripcion
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property int $id_programa
 * @property string $estado
 * @property string $nombre
 * @property Permiso[] $permisos
 * @property UsuarioXPrograma[] $usuarioXProgramas
 * @property Programa[] $programa
 */
class TipoUsuario extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tipo_usuario';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_tipo_usuario';

    /**
     * @var array
     */
    protected $fillable = ['descripcion', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'estado', 'nombre', 'id_programa'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permisos()
    {
        return $this->belongsToMany('App\Permiso', 'permiso_x_tipo_usuario', 'id_tipo_usuario', 'id_permiso');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarioXProgramas()
    {
        return $this->hasMany('App\UsuarioXPrograma', 'id_tipo_usuario', 'id_tipo_usuario');
    }

    public function usuarios() {
        return $this->belongsToMany('App\Usuario','usuario_x_programa','id_tipo_usuario'
            ,'id_usuario')->withPivot('id_programa');
    }

    public function programa() {
        return $this->belongsTo('App\Programa', 'id_programa', 'id_programa');
    }
}
