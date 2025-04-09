<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Database\Factories\Modules\Invoices\Domain\Models\InvoiceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Shared\Application\Casts\UuidCast;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @property UuidInterface $id
 * @property StatusEnum $status
 * @property string $customer_name
 * @property string $customer_email
 * @property Collection<InvoiceProductLine> $invoiceProductLines
 */
class Invoice extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'status',
        'customer_name',
        'customer_email',
    ];

    protected $casts = [
        'id' => UuidCast::class,
        'status' => StatusEnum::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $invoice): void {
            if (empty($invoice->id)) {
                $invoice->id = Uuid::uuid4();
            }

            $invoice->status = StatusEnum::Draft;
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function invoiceProductLines(): HasMany
    {
        return $this->hasMany(InvoiceProductLine::class);
    }

    public function addProductLine(InvoiceProductLine $productLine): void
    {
        $this->invoiceProductLines()->save($productLine);
        $this->save();
    }

    public function getTotalPrice(): int
    {
        return $this->invoiceProductLines->sum(fn ($line) => $line->price * $line->quantity);
    }

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }
}
