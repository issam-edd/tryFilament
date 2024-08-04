<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    const DEFAULT_PASSWORD = 'changeme123';
    const DEFAULT_ROLE = 'USER';

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping(),
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['email', 'unique:users,email']),
            ImportColumn::make('role')
                ->label('Role')
                ->rules([Rule::in(['ADMIN', 'USER', 'EDITOR'])])
                ->requiredMapping(false),

        ];
    }

    public function resolveRecord(): ?User
    {
        $role = $this->data['role'] ?? self::DEFAULT_ROLE;
        return User::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'role' => $role,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
        ]);

        // return new User();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
