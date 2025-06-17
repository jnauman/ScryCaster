<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use App\Models\Monster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class MonsterInstancesRelationManager extends RelationManager
{
    protected static string $relationship = 'monsterInstances';

    protected static ?string $recordTitleAttribute = 'id'; // Using ID, monster name will be in a column

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('monster_id')
                    ->relationship(name: 'monster', titleAttribute: 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('current_health', Monster::find($state)?->max_health))
                    ->label('Monster Type')
                    ->columnSpanFull(),
                TextInput::make('current_health')
                    ->numeric()
                    ->required()
                    ->label('Current HP'),
                TextInput::make('initiative_roll')
                    ->numeric()
                    ->nullable()
                    ->label('Initiative Roll'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monster.name')->label('Monster')->searchable()->sortable(),
                TextColumn::make('current_health')->label('Current HP')->sortable(),
                TextColumn::make('monster.ac')->label('AC (Base)')->sortable(),
                TextColumn::make('monster.max_health')->label('Max HP (Base)')->sortable(),
                TextColumn::make('initiative_roll')->label('Initiative')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
