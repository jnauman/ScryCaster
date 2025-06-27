<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use App\Models\CampaignImage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;

class CampaignImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'campaignImages';

	public function form(Schema $schema): Schema
	{
		return $schema
		->schema([
                FileUpload::make('image_path')
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
                TextInput::make('original_filename')
                    ->label('Original Filename')
                    ->maxLength(255)
                    ->helperText('Will be automatically filled from uploaded file, but can be overridden.'),
                Textarea::make('caption')
                    ->label('Caption')
                    ->columnSpanFull(),
                Hidden::make('uploader_user_id')
                    ->default(fn () => auth()->id()), // Automatically set the uploader
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_filename')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image Preview')
                    ->disk('public')
                    ->width(100)
                    ->height(100),
                TextColumn::make('original_filename')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('caption')
                    ->searchable(),
                TextColumn::make('uploader.name') // Assuming User model has a 'name' attribute
                    ->label('Uploaded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['uploader_user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->after(function (CampaignImage $record) {
                        // Delete the actual file from storage
                        if ($record->image_path) {
                            Storage::disk('public')->delete($record->image_path);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function (Collection $records) {
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
