<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonsterResource\Pages;
use App\Filament\Resources\MonsterResource\RelationManagers;
use App\Models\Monster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Set; // For FileUpload afterStateUpdated (commented out)
// use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; // Or use Illuminate\Http\UploadedFile;

class MonsterResource extends Resource
{
    protected static ?string $model = Monster::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('monster_json_upload')
                    ->label('Upload Monster JSON')
                    ->acceptedFileTypes(['application/json'])
                    ->columnSpanFull()
                    // ->afterStateUpdated(function (Set $set, TemporaryUploadedFile $file) {
                    //     $data = self::parseMonsterJson($file->get());
                    //     if ($data) {
                    //         $set('name', $data['name'] ?? null);
                    //         $set('ac', $data['ac'] ?? null);
                    //         // ... set other fields
                    //         $set('data', json_encode($data['data'] ?? []));
                    //     }
                    // })
                    ->helperText('Upload a JSON file to pre-fill monster data. Manual fields below will override JSON values if subsequently edited.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('ac')
                    ->label('Armor Class')
                    ->numeric()
                    ->required(),
                TextInput::make('max_health')
                    ->label('Max Health')
                    ->numeric()
                    ->default(10), // Removed required as it has a default
                TextInput::make('strength')
                    ->numeric()
                    ->default(10), // Removed required
                TextInput::make('dexterity')
                    ->numeric()
                    ->default(10), // Removed required
                TextInput::make('constitution')
                    ->numeric()
                    ->default(10), // Removed required
                TextInput::make('intelligence')
                    // ->tel() // Removed tel() type, it's for telephone numbers
                    ->numeric()
                    ->default(10), // Removed required
                TextInput::make('wisdom')
                    ->numeric()
                    ->default(10), // Removed required
                TextInput::make('charisma')
                    ->numeric()
                    ->default(10), // Removed required
                Textarea::make('data')
                    ->label('Additional Data (JSON)')
                    ->columnSpanFull()
                    ->nullable(),
                Select::make('user_id')
                    ->label('Owner (GM)')
                    ->relationship('user', 'name')
                    ->nullable()
                    ->placeholder('Global Monster (Shared)')
                    ->default(fn () => auth()->id()),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('ac')->label('AC')->sortable(),
                TextColumn::make('max_health')->label('Max HP')->sortable(),
                TextColumn::make('user.name')->label('Owner (GM)')->sortable()->placeholder('Global'),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function parseMonsterJson(string $jsonContent): ?array
    {
        // TODO: Implement monster JSON parsing similar to CharacterResource if needed.
        // For now, this is a placeholder.
        // Ensure this method can handle potential JSON errors gracefully.
        // Example structure:
        // $data = json_decode($jsonContent, true);
        // if (json_last_error() !== JSON_ERROR_NONE) { return null; }
        // return $data; // or transform $data to fit model structure
        return null;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonsters::route('/'),
            'create' => Pages\CreateMonster::route('/create'),
            'edit' => Pages\EditMonster::route('/{record}/edit'),
        ];
    }
}
