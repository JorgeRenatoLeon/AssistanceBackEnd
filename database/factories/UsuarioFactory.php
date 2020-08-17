<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Usuario;
use Faker\Generator as Faker;

$factory->define(Usuario::class, function (Faker $faker) {
    return [
        'nombre' => $faker->firstName . ' ' . $faker->firstName,
        'apellidos' => $faker->lastName . ' ' . $faker->lastName,
        'codigo'=>$faker->unique()->randomNumber(8,true),
        'correo' => preg_replace('/@example\..*/', '@pucp.edu.pe', $faker->unique()->safeEmail),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'sexo' => $faker->randomElement(['M', 'F']),
        'telefono'=>$faker->unique()->randomNumber(9,true),
//        '$condicion_alumno'=>$faker->randomElement(['']),
        'estado' => 'act',
        'usuario_creacion'=> 1,
    ];
});
