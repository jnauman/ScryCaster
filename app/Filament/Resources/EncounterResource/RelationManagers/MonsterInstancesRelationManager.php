<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Monster;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker; // Added import
use Filament\Tables\Columns\TextColumn;

class MonsterInstancesRelationManager extends RelationManager
{
    protected static string $relationship = 'monsterInstances';

    protected static ?string $recordTitleAttribute = 'id'; // Using ID, monster name will be in a column

	public function form(Schema $schema): Schema
	{
		return $schema
		->schema([
                Select::make('monster_id')
                    ->relationship(name: 'monster', titleAttribute: 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $monster = Monster::find($state);
                        if ($monster) {
                            $set('max_health', $monster->max_health);
                            $set('current_health', $monster->max_health); // Default current_health to max_health
                        } else {
                            $set('max_health', null);
                            $set('current_health', null);
                        }
                    })
                    ->label('Monster Type')
                    ->columnSpanFull(),
                TextInput::make('display_name')
                    ->label('Display Name (Optional)')
                    ->placeholder('Will use monster name if blank')
                    ->nullable()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('max_health')
                    ->numeric()
                    ->required()
                    ->label('Max HP'),
                TextInput::make('current_health')
                    ->numeric()
                    ->required()
                    ->label('Current HP'),
                TextInput::make('initiative_roll')
                    ->numeric()
                    ->nullable()
                    ->label('Initiative Roll'),
                TextInput::make('initiative_group')
                    ->label('Initiative Group (Optional)')
                    ->nullable()
                    ->maxLength(255)
                    ->helperText('Monsters in the same group will share initiative.'),
                ColorPicker::make('group_color')
                    ->label('Group Color')
                    ->helperText('Assign a color to visually group this monster with others sharing the same Initiative Group name.')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monster.name')->label('Base Monster')->searchable()->sortable(),
                TextColumn::make('display_name')->label('Display Name')->searchable()->sortable()->placeholder('N/A'),
                TextColumn::make('current_health')->label('Current HP')->sortable(),
                TextColumn::make('monster.max_health')->label('Max HP (Base)')->sortable(),
                TextColumn::make('monster.ac')->label('AC (Base)')->sortable(),
                TextColumn::make('initiative_roll')->label('Initiative')->sortable(),
                TextColumn::make('initiative_group')->label('Group')->sortable()->placeholder('N/A'),
                TextColumn::make('group_color')->label('Group Color')->sortable()->placeholder('N/A'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
