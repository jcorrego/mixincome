<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Enums\ImportType;
use App\Models\Entity;
use App\Models\TransactionImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionImport>
 */
final class TransactionImportFactory extends Factory
{
    protected $model = TransactionImport::class;

    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'import_type' => $this->faker->randomElement(ImportType::cases()),
            'file_name' => $this->faker->word() . '.csv',
            'row_count' => $this->faker->numberBetween(1, 500),
            'status' => ImportStatus::Imported,
        ];
    }
}
