<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Employee Managment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("User Location")
                ->description("Select the user name location details")
                ->collapsible()
                ->schema([
                    Forms\Components\Select::make('country_id')
                        ->relationship(name: 'country', titleAttribute: 'name')
                        ->searchable()
                        ->live() // listen to the select
                        ->afterStateUpdated(function(Set $set) {
                            $set('state_id', null);
                            $set('city_id', null);
                        }) // update the next select
                        ->preload(),
                    Forms\Components\Select::make('state_id')
                        ->options(fn(Get $get): Collection => State::query()
                            ->where('country_id', $get('country_id'))
                            ->pluck('name', 'id'))
                        ->live() // listen to the select
                        ->afterStateUpdated(fn(Set $set) => $set('city_id', null)) // update the next select
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('city_id')
                        ->options(fn(Get $get): Collection => City::query()
                            ->where('state_id', $get('state_id'))
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('department_id')
                        ->relationship(name: 'department', titleAttribute: 'name')
                        ->searchable()
                        ->live()
                        ->preload(),
                ])->columns(4),

               Forms\Components\Section::make("User Name")
               ->description("insert the user name details")
               ->collapsible()
               ->schema([
                Forms\Components\TextInput::make('fist_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('middle_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
               ])->columns(3),

               Forms\Components\Section::make("User address")
               ->description("Insert the user address")
               ->collapsible()
               ->schema([
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip_code')
                    ->required()
                    ->maxLength(255),
               ])->columns(2),

               Forms\Components\Section::make("Dates")
               ->description("Shoose the dates")
               ->collapsible()
               ->schema([
                Forms\Components\DatePicker::make('date_of_birth')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('date_of_hire')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
               ])->columns(2),
                            
            ])->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
       return $infolist->schema([
        InfoSection::make("Employee Location")
        ->description("The Employee name location details")
        ->collapsible()
        ->schema([
            TextEntry::make('country.name'),
            TextEntry::make('state.name'),
            TextEntry::make('city.name'),
            TextEntry::make('department.name'),
        ])->columns(4),

        InfoSection::make("Employee Name")
       ->description("The Employee name details")
       ->collapsible()
       ->schema([
            TextEntry::make('fist_name'),
            TextEntry::make('middle_name'),
            TextEntry::make('last_name'),
       ])->columns(3),

       InfoSection::make("Employee address")
       ->description("The Employee address")
       ->collapsible()
       ->schema([
            TextEntry::make('address'),
            TextEntry::make('zip_code'),
       ])->columns(2),

       InfoSection::make("Dates")
       ->description("The Employee dates")
       ->collapsible()
       ->schema([
            TextEntry::make('date_of_birth'),
            TextEntry::make('date_of_hire'),
       ])->columns(2),
           
       ]); 
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fist_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_hire')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->label('State')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Department')
                ->relationship('department', 'name')
                ->searchable()
                ->preload()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
