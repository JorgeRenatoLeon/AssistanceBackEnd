<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_compromiso
 * @property string $nombre
 * @property string $estado
 * @property int $id_plan_accion
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property PlanAccion $planAccion
 */
class Compromiso extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'compromiso';

    public $timestamps = false;
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_compromiso';

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'estado', 'id_plan_accion', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function planAccion()
    {
        return $this->hasOne('App\PlanAccion', 'id_plan_accion', 'id_compromiso');
    }
}
