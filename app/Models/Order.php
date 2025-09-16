<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'vat_rate',
        'vat_amount',
        'discount_amount',
        'subtotal',
        'total',
        'user_id'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getCustomerName()
    {
        if ($this->customer_name) {
            return $this->customer_name;
        }
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }

    public function total()
    {
        // If total is already calculated and stored, return it
        if ($this->total > 0) {
            return $this->total;
        }

        // Otherwise calculate from items (backward compatibility)
        return $this->items->map(function ($i) {
            return $i->price;
        })->sum();
    }

    public function formattedTotal()
    {
        return number_format($this->total(), 2);
    }

    public function receivedAmount()
    {
        return $this->payments->map(function ($i) {
            return $i->amount;
        })->sum();
    }

    public function formattedReceivedAmount()
    {
        return number_format($this->receivedAmount(), 2);
    }

    public function getSubtotal()
    {
        if ($this->subtotal > 0) {
            return $this->subtotal;
        }

        // Calculate from items if not stored
        return $this->items->map(function ($i) {
            return $i->price;
        })->sum();
    }

    public function getVatAmount()
    {
        return $this->vat_amount;
    }

    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }

    public function getVatRate()
    {
        return $this->vat_rate;
    }
}
