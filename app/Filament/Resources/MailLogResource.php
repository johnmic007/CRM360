<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailLogResource\Pages;
use App\Models\MailLog;
use App\Models\MailTemplate;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class MailLogResource extends Resource
{
    protected static ?string $model = MailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';


    protected static ?string $navigationGroup = 'Mail';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->id()) // Set default value to the logged-in user ID
                    ->required(),

                Hidden::make('company_id')
                    ->default(fn() => auth()->user()?->company_id) // Set logged-in user's company_id
                    ->required(),

                Select::make('mail_template_id')
                    ->label('Select Email Format')
                    ->options(MailTemplate::pluck('name', 'id')->toArray())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $template = MailTemplate::find($state);
                            if ($template) {
                                $set('content', $template->content); // Set email content
                                $set('cc_emails', $template->additional_emails); // Set additional emails
                            }
                        } else {
                            // Clear the fields when no template is selected
                            $set('content', '');
                            $set('cc_emails', '');
                        }
                    })
                    ->preload()
                    ->searchable()
                    ->placeholder('Choose a template'),

                    TiptapEditor::make('content')
                    ->label('Email Content')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('to_emails')
                    ->label('Recipient Emails')
                    ->required()
                    ->placeholder('Enter email IDs, separated by commas'),

                TextInput::make('cc_emails')
                    ->label('CC Emails')
                    ->placeholder('Enter CC email IDs, separated by commas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                ->label('Sent By')
                ->sortable()
                ->searchable(),  

                TextColumn::make('mail_template.name')
                ->label('Template Used')
                ->sortable()
                ->searchable(),

                TextColumn::make('created_at')
                ->label('Sent At')
                ->dateTime()
                ->sortable(),
                
                ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailLogs::route('/'),
            'create' => Pages\CreateMailLog::route('/create'),
            'edit' => Pages\EditMailLog::route('/{record}/edit'),
        ];
    }
}
