<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Filament\Resources\TransferResource\RelationManagers;
use App\Models\Transfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            \Filament\Forms\Components\Section::make('แบบฟอร์มเบิกจ่ายยา')
                ->description('เลือกยาและคลังปลายทางที่ต้องการเบิกจ่าย')
                ->schema([
                    // 1. เลือกยาจากบาร์โค้ดหรือชื่อ
                    \Filament\Forms\Components\Select::make('medicine_id')
                        ->label('เลือกยา')
                        ->relationship('medicine', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    // 2. เลือกคลังต้นทาง (ล็อคไว้ที่ Master ก่อน)
                    \Filament\Forms\Components\Select::make('from_location')
                        ->label('เบิกจากคลัง')
                        ->options([
                            'stock_master' => 'คลังใหญ่ (Master)',
                        ])
                        ->default('stock_master')
                        ->required(),

                    // 3. เลือกคลังปลายทาง
                    \Filament\Forms\Components\Select::make('to_location')
                        ->label('ไปยังคลัง')
                        ->options([
                            'stock_a' => 'คลัง A',
                            'stock_b' => 'คลัง B',
                            'stock_c' => 'คลัง C',
                        ])
                        ->required(),

                    // 4. ระบุจำนวน
                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label('จำนวนที่เบิก')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])->columns(2)
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('created_at')
                ->label('วันที่เบิก')
                ->dateTime()
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('medicine.name')
                ->label('ชื่อยา'),
            \Filament\Tables\Columns\TextColumn::make('from_location')
                ->label('จาก'),
            \Filament\Tables\Columns\TextColumn::make('to_location')
                ->label('ไปที่'),
            \Filament\Tables\Columns\TextColumn::make('quantity')
                ->label('จำนวน')
                ->badge(),
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
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
            'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
