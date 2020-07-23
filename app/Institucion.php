<?php

namespace App;

use App\Facultad;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_institucion
 * @property string $nombre
 * @property string $logo
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property string $direccion
 * @property string $telefono
 * @property string $siglas
 * @property string $estado
 * @property Facultad[] $facultads
 */
class Institucion extends Model
{
    public $timestamps=false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'institucion';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_institucion';

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'logo', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'direccion', 'telefono', 'siglas', 'estado'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function facultads()
    {
        return $this->hasMany('App\Facultad', 'id_institucion', 'id_institucion');
    }
}
