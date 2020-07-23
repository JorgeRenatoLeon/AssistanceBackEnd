<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_usuario
 * @property int $id_tipo_usuario
 * @property int $id_usuario_x_programa
 * @property int $id_programa
 * @property Usuario $usuario
 * @property Programa $programa
 * @property TipoUsuario $tipoUsuario
 */
class UsuarioXPrograma extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuario_x_programa';

    /**
     * @var array
     */
    protected $fillable = ['id_programa'];
    public $timestamps = false;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoUsuario()
    {
        return $this->belongsTo('App\TipoUsuario', 'id_tipo_usuario', 'id_tipo_usuario');
    }
}
