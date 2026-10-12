<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Filament\Resources\MedicineResource\RelationManagers;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form {
    return $form->schema([
        Forms\Components\Section::make('ข้อมูลยา')
            ->schema([
                Forms\Components\TextInput::make('barcode')->required()->autofocus(), // รองรับเครื่องสแกน
                Forms\Components\TextInput::make('name')->required()->label('ชื่อยา'),
                Forms\Components\TextInput::make('supplier_name')->label('บริษัทผู้จำหน่าย'),
                Forms\Components\TextInput::make('stock_master')->numeric()->label('จำนวนในคลังใหญ่ (Master)')->default(0),
            ])->columns(2)
    ]);
}

public static function table(Table $table): Table {
    return $table->columns([
        Tables\Columns\TextColumn::make('barcode')->searchable(),
        Tables\Columns\TextColumn::make('name')->searchable(),
        Tables\Columns\BadgeColumn::make('stock_master')->label('Master')->color('primary'),
        Tables\Columns\TextColumn::make('stock_a')->label('คลัง A'),
        Tables\Columns\TextColumn::make('stock_b')->label('คลัง B'),
        Tables\Columns\TextColumn::make('stock_c')->label('คลัง C'),
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
            'index' => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
