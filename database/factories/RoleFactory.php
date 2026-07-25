<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class RoleFactory extends Factory { public function definition(): array { $name=fake()->unique()->jobTitle(); return ['name'=>$name,'code'=>fake()->unique()->slug(2)]; } }
