<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use App\Models\CampaignImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CampaignImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'campaignImages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('Image')
                    ->required()
                    ->image()
                    ->disk('public')
                    ->directory(fn (?Model $record) => 'campaign-images/' . ($record ? $record->campaign_id : $this->getOwnerRecord()->id) )
                    ->preserveFilenames()
                    ->afterStateUpdated(function (TemporaryUploadedFile $state, callable $set) {
                        // Automatically set original_filename from the uploaded file's client original name
                        if ($state) {
                            $set('original_filename', $state->getClientOriginalName());
                        }
                    })
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('original_filename')
                    ->label('Original Filename')
                    ->maxLength(255)
                    ->helperText('Will be automatically filled from uploaded file, but can be overridden.'),
                Forms\Components\Textarea::make('caption')
                    ->label('Caption')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('uploader_user_id')
                    ->default(fn () => auth()->id()), // Automatically set the uploader
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_filename')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image Preview')
                    ->disk('public')
                    ->width(100)
                    ->height(100),
                Tables\Columns\TextColumn::make('original_filename')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('caption')
                    ->searchable(),
                Tables\Columns\TextColumn::make('uploader.name') // Assuming User model has a 'name' attribute
                    ->label('Uploaded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploader_user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (CampaignImage $record) {
                        // Delete the actual file from storage
                        if ($record->image_path) {
                            Storage::disk('public')->delete($record->image_path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if ($record->image_path) {
                                    Storage::disk('public')->delete($record->image_path);
                                }
                            }
                        }),
                ]),
            ]);
    }
}
