<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportStatus;
use App\Enums\ImportType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TransactionImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entity_id',
        'import_type',
        'file_name',
        'import_date',
        'row_count',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'import_type' => ImportType::class,
        'import_date' => 'timestamp',
        'status' => ImportStatus::class,
        'row_count' => 'integer',
    ];

    /**
     * Get the entity that owns this import.
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Get all transactions created by this import.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'import_id');
    }

    /**
     * Get the import type label.
     */
    public function getTypeLabel(): string
    {
        return $this->import_type->label();
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColor(): string
    {
        return $this->status->color();
    }

    /**
     * Get the status icon.
     */
    public function getStatusIcon(): string
    {
        return $this->status->icon();
    }

    /**
     * Check if the import is complete.
     */
    public function isComplete(): bool
    {
        return $this->status->isComplete();
    }

    /**
     * Check if the import was successful.
     */
    public function isSuccess(): bool
    {
        return $this->status->isSuccess();
    }

    /**
     * Check if the import has errors.
     */
    public function hasError(): bool
    {
        return $this->status->isError();
    }

    /**
     * Check if this import type requires a file.
     */
    public function requiresFile(): bool
    {
        return $this->import_type->requiresFile();
    }

    /**
     * Get accepted file extensions for this import type.
     *
     * @return array<string>
     */
    public function getAcceptedExtensions(): array
    {
        return $this->import_type->acceptedExtensions();
    }

    /**
     * Get the import type icon.
     */
    public function getTypeIcon(): string
    {
        return $this->import_type->icon();
    }

    /**
     * Update the import status and row count.
     */
    public function updateStatus(ImportStatus $status, int $rowCount = null, string $errorMessage = null): void
    {
        $this->update([
            'status' => $status,
            'row_count' => $rowCount ?? $this->row_count,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, ImportStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by import type.
     */
    public function scopeOfType(Builder $query, ImportType $type): Builder
    {
        return $query->where('import_type', $type);
    }

    /**
     * Scope to filter completed imports.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ImportStatus::Imported,
            ImportStatus::Failed,
            ImportStatus::Duplicate,
        ]);
    }

    /**
     * Scope to filter successful imports.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', ImportStatus::Imported);
    }

    /**
     * Scope to filter failed imports.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ImportStatus::Failed,
            ImportStatus::Duplicate,
        ]);
    }
}
