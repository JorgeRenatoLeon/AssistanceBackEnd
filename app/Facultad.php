<?php

namespace App;

use App\Institucion;
use App\Programa;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_facultad
 * @property int $id_institucion
 * @property string $codigo
 * @property string $nombre
 * @property string $descripcion
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property string $correo
 * @property string $estado
 * @property Institucion $institucion
 * @property Programa[] $programas
 */
class Facultad extends Model
{
    public $timestamps=false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facultad';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_facultad';

    /**
     * @var array
     */
    protected $fillable = ['id_institucion', 'nombre', 'descripcion', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'correo', 'estado'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institucion()
    {
        return $this->belongsTo('App\Institucion', 'id_institucion', 'id_institucion');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function programas()
    {
        return $this->hasMany('App\Programa', 'id_facultad', 'id_facultad');
    }
}
