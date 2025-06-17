<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Monster;

class MonsterInstancesRelationManager extends RelationManager
{
    protected static string $relationship = 'monsterInstances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('monster_id')
                    ->label('Monster')
                    ->options(Monster::all()->pluck('name', 'id')->toArray())
                    ->reactive()
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('current_health', Monster::find($state)?->max_health))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('current_health')
                    ->required()
                    ->numeric()
                    ->label('Current Health'),
                Forms\Components\TextInput::make('initiative_roll')
                    ->numeric()
                    ->label('Initiative Roll'),
                // Order is calculated, data is nullable json
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('monster.name') // Accessing via relationship
            ->columns([
                Tables\Columns\TextColumn::make('monster.name')->label('Name'),
                Tables\Columns\TextColumn::make('current_health')->label('HP'),
                Tables\Columns\TextColumn::make('initiative_roll')->label('Initiative'),
                Tables\Columns\TextColumn::make('order')->label('Order')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['current_health']) && !empty($data['monster_id'])) {
                            $monster = Monster::find($data['monster_id']);
                            if ($monster) {
                                $data['current_health'] = $monster->max_health;
                            }
                        }
                        return $data;
                    }),
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
