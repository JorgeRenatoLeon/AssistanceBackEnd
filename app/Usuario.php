<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id_usuario
 * @property int $codigo
 * @property string $estado
 * @property string $password
 * @property string $fecha_creacion
 * @property string $fecha_actualizacion
 * @property int $usuario_creacion
 * @property int $usuario_actualizacion
 * @property string $correo
 * @property string $telefono
 * @property string $imagen
 * @property string $nombre
 * @property string $apellidos
 * @property string $ap_materno
 * @property string $sexo
 * @property string $ultimo_logueo_fallido
 * @property string $bloqueado
 * @property int $intentos_fallidos
 * @property string $notas
 * @property string $condicion_alumno
 * @property string $token_recuperacion
 * @property Disponibilidad[] $disponibilidads
 * @property Cita[] $citas
 * @property PlanAccion[] $planAccionsTutor
 * @property PlanAccion[] $planAccionsAlumno
 * @property Solicitud[] $solicitudsSolicitante
 * @property Solicitud[] $solicitudsRemitente
 * @property UsuarioXPrograma[] $usuarioXProgramas
 * @property TipoTutoria[] $tipoTutorias
 * @property RegistroAlumno[] $registroAlumnosTutor
 * @property RegistroAlumno[] $registroAlumnosAlumnos
 */
class Usuario extends Authenticatable
{
    use Notifiable, \Laravel\Passport\HasApiTokens;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuario';

    public $timestamps = false;
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_institucion', 'estado', 'password', 'fecha_creacion', 'fecha_actualizacion', 'usuario_creacion', 'usuario_actualizacion', 'correo', 'telefono', 'imagen', 'nombre', 'apellidos', 'sexo','ultimo_logueo_fallido','bloqueado','intentos_fallidos','notas', 'google_id',
    'codigo','condicion_alumno','token_recuperacion'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function getAuthPassword(){
        return $this['password'];
    }
    public function getAuthIdentifier(){
        return $this['id_usuario'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disponibilidads()
    {
        return $this->hasMany('App\Disponibilidad', 'id_usuario', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function citas()
    {
        return $this->belongsToMany('App\Cita', 'cita_x_usuario', 'id_usuario', 'id_cita')
            ->withPivot('asistencia');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function planAccionsTutor()
    {
        return $this->hasMany('App\PlanAccion', 'id_tutor', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function planAccionsAlumno()
    {
        return $this->hasMany('App\PlanAccion', 'id_alumno', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitudsSolicitante()
    {
        return $this->hasMany('App\Solicitud', 'id_solicitante', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitudsRemitente()
    {
        return $this->hasMany('App\Solicitud', 'id_remitente', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarioXProgramas()
    {
        return $this->hasMany('App\UsuarioxPrograma', 'id_usuario', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tipoTutorias()
    {
        return $this->belongsToMany('App\TipoTutoria', 'tipo_tutoria_x_usuario', 'id_usuario', 'id_tipo_tutoria');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registroAlumnosTutor()
    {
        return $this->hasMany('App\RegistroAlumno', 'id_tutor', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registroAlumnosAlumno()
    {
        return $this->hasMany('App\RegistroAlumno', 'id_alumno', 'id_usuario');
    }

    public function programa() {
        return $this->belongsToMany('App\Programa','usuario_x_programa','id_usuario'
            ,'id_programa')->withPivot('id_tipo_usuario');
    }
    public function tipoUsuario() {
        return $this->belongsToMany('App\TipoUsuario','usuario_x_programa','id_usuario'
            ,'id_tipo_usuario')->withPivot('id_programa');
    }

}

