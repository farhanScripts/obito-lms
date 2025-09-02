<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Pricing;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Customers';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Product and Price')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('pricing_id')
                                        ->relationship('pricing', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // variabel state isinya id dari pricing 
                                            $pricing = Pricing::find($state);
                                            $price = $pricing->price;
                                            $duration = $pricing->duration;

                                            $subTotal = $price;
                                            $tax = $subTotal * 0.12;
                                            $totalAmount = $subTotal + $tax;

                                            $set('sub_total_amount', $subTotal);
                                            $set('total_tax_amount', $tax);
                                            $set('grand_total_amount', $totalAmount);
                                            $set('duration', $duration);
                                        })
                                        ->afterStateHydrated(function (callable $set, $state) {
                                            $pricingId = $state;
                                            if ($pricingId) {
                                                $pricing = Pricing::find($pricingId);
                                                $set('duration', $pricing->duration);
                                            }
                                        }),
                                    TextInput::make('duration')
                                        ->required()
                                        ->numeric()
                                        ->readOnly()
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sub_total_amount')
                                        ->required()
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->readOnly()
                                        ->label('Sub Total Amount'),
                                    TextInput::make('total_tax_amount')
                                        ->required()
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->readOnly()
                                        ->label('Total Tax Amount'),
                                    TextInput::make('grand_total_amount')
                                        ->required()
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->readOnly()
                                        ->label('Grand Total Amount'),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('started_at')
                                        ->live()
                                        ->afterStateUpdated(
                                            function ($state, callable $set, callable $get) {
                                                $duration = $get('duration');
                                                if ($state && $duration) {
                                                    $endedAt = \Carbon\Carbon::parse($state)->addMonth($duration);
                                                    $set('ended_at', $endedAt->format('Y-m-d'));
                                                }
                                            }
                                        )
                                        ->required(),
                                    DatePicker::make('ended_at')
                                        ->readOnly()
                                        ->required()
                                ]),
                        ]),
                    Step::make('Customer Information')
                        ->schema([
                            Select::make('user_id')
                                ->label('Student Email')
                                ->options(User::role('student')->pluck('email', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $findUser = User::find($state);
                                    $name = $findUser->name;
                                    $email = $findUser->email;

                                    $set('name', $name);
                                    $set('email', $email);
                                })
                                ->afterStateHydrated(function (callable $set, $state) {
                                    $userId = $state;
                                    if ($userId) {
                                        $findUser = User::find($userId);
                                        $set('name', $findUser->name);
                                        $set('email', $findUser->email);
                                    }
                                }),
                            TextInput::make('name')
                                ->required()
                                ->disabled(),
                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->disabled(),
                        ]),
                    Forms\Components\Wizard\Step::make('Payment Information')
                        ->schema([
                            ToggleButtons::make('is_paid')
                                ->label('Apakah sudah membayar?')
                                ->boolean()
                                ->grouped()
                                ->icons([
                                    true => 'heroicon-o-pencil',
                                    false => 'heroicon-o-clock',
                                ])
                                ->required(),

                            Forms\Components\Select::make('payment_type')
                                ->options([
                                    'Midtrans' => 'Midtrans',
                                    'Manual' => 'Manual',
                                ])
                                ->required(),

                            Forms\Components\FileUpload::make('proof')
                                ->image(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->columns(1)
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('student.photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('student.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pricing.name'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Terverifikasi'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->action(function (Transaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        // Trigger the custom notification
                        Notification::make()
                            ->title('Order Approved')
                            ->success()
                            ->body('The Order has been successfully approved.')
                            ->send();

                        // kirim email, kirim sms

                    })
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(Transaction $record) => !$record->is_paid),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
