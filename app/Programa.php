<?php

namespace App;

use App\Facultad;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_programa
 * @property int $id_facultad
 * @property string $codigo
 * @property string $nombre
 * @property string $descripcion
 * @property string $correo
 * @property string $estado
 * @property float $hora_bloque
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property Facultad $facultad
 * @property UsuarioXPrograma[] $usuarioXProgramas
 * @property TipoTutorium[] $tipoTutorias
 * @property UnidadApoyo[] $unidadApoyos
 */
class Programa extends Model
{
    public $timestamps=false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'programa';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_programa';

    /**
     * @var array
     */
    protected $fillable = ['id_facultad', 'nombre', 'descripcion', 'correo', 'estado', 'hora_bloque', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function facultad()
    {
        return $this->belongsTo('App\Facultad', 'id_facultad', 'id_facultad');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tipoTutorias()
    {
        return $this->hasMany('App\TipoTutoria', 'id_programa', 'id_programa');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function unidadApoyos()
    {
        return $this->belongsToMany('App\UnidadApoyo', 'unidad_apoyo_x_programa', 'id_programa', 'id_unidad_apoyo');
    }

    public function usuarios() {
        return $this->belongsToMany('App\Usuario','usuario_x_programa','id_programa'
            ,'id_usuario')->withPivot('id_tipo_usuario');
    }
    public function tipoUsuario() {
        return $this->belongsToMany('App\TipoUsuario','usuario_x_programa','id_programa'
            ,'id_tipo_usuario')->withPivot('id_usuario');
    }

    public function usuarioXProgramas()
    {
        return $this->hasMany('App\UsuarioxPrograma', '$id_programa', '$id_programa');
    }
}
