<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id_encuesta
 * @property int $id_tutor
 * @property int $id_alumno
 * @property int $agrupador
 * @property int $id_programa
 * @property string $respuesta
 * @property string $estado
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property Encuestum $encuestum
 * @property Usuario $usuario
 * @property Usuario $usuario1
 * @property Programa $programa
 */
class PreguntasxAlumno extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'preguntas_x_alumno';

    protected $primaryKey = ['agrupador', 'id_encuesta','id_tutor','id_alumno'];
    public $incrementing = false;
    /**
     * @var array
     */
    protected $fillable = ['respuesta', 'estado', 'fecha_creacion', 'fecha_actualizacion','id_programa'];

    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encuestum()
    {
        return $this->belongsTo('App\Encuestum', 'id_encuesta', 'id_encuesta');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'id_alumno', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario1()
    {
        return $this->belongsTo('App\Usuario', 'id_tutor', 'id_usuario');
    }


    public function programa()
    {
        return $this->belongsTo('App\Programa', 'id_programa', 'id_programa');
    }
}
