<?php


namespace App\Support\Traits;

use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;

trait EnumMigration
{
    public function enumFromClass(Blueprint $table, string $column, string $enumClass, ?string $default = null)
    {
        $reflection = new ReflectionClass($enumClass);
        $cases = $reflection->getConstants();

        $values = array_map(function ($case) {
            return $case instanceof \BackedEnum ? $case->value : (string)$case;
        }, $cases);

        return $table->enum($column, $values)->default($default);
    }
}
