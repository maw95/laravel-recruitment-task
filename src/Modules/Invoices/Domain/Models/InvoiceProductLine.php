<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Database\Factories\Modules\Invoices\Domain\Models\ProductLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Shared\Application\Casts\UuidCast;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @property UuidInterface $id
 * @property string $name
 * @property int $quantity
 * @property int $price
 * @property Invoice $invoice
 */
final class InvoiceProductLine extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'quantity',
        'price',
        'invoice_id',
    ];

    protected $casts = [
        'id' => UuidCast::class,
        'invoice_id' => UuidCast::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $invoiceProductLine): void {
            if (empty($invoiceProductLine->id)) {
                $invoiceProductLine->id = Uuid::uuid4()->toString();
            }

            if ($invoiceProductLine->quantity <= 0 || $invoiceProductLine->price <= 0) {
                throw new \InvalidArgumentException('Quantity and Unit Price must be positive integers.');
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTotalUnitPrice(): int
    {
        return $this->quantity * $this->price;
    }

    protected static function newFactory(): ProductLineFactory
    {
        return ProductLineFactory::new();
    }
}
