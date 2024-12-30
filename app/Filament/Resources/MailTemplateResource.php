<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateResource\Pages;
use App\Filament\Resources\MailTemplateResource\RelationManagers;
use App\Models\MailTemplate;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Mail';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales']);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Mail Template Details')
                    ->description('Create or update an email template with content and recipients.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter a meaningful template name')
                            ->columnSpanFull(),


                            TextInput::make('subject')
                            ->label('Subject of the  Emails')
                            ->required(),

                        Select::make('selected_users')
                            ->label('Select Users')
                            ->multiple()
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Search and select users to send the email')
                            ->helperText('You can select multiple users to send the email template.')
                            ->columnSpanFull(),

                        Textarea::make('additional_emails')
                            ->label('Additional Email IDs')
                            ->placeholder('e.g., user1@example.com, user2@example.com')
                            ->helperText('Enter multiple email IDs separated by commas.')
                            ->rows(3)
                            ->autosize()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Email Content')
                    ->description('Design your email content with the rich editor.')
                    ->schema([
                        TiptapEditor::make('content')
                            ->label('Email Body')
                            ->required()

                            ->placeholder('Design your email content here...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Template Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('selected_users')
                    ->label('Recipients')
                    ->getStateUsing(function ($record) {
                        return $record->selected_users
                            ? implode(', ', User::whereIn('id', $record->selected_users)->pluck('name')->toArray())
                            : 'No recipients selected';
                    })
                    ->toggleable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('additional_emails')
                    ->label('Additional Emails')
                    ->toggleable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('updated_at')
                //     ->label('Updated At')
                //     ->dateTime()
                //     ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('recent')
                    ->label('Recently Created')
                    ->query(fn(Builder $query) => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListMailTemplates::route('/'),
            'create' => Pages\CreateMailTemplate::route('/create'),
            // 'edit' => Pages\EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
