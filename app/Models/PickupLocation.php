<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    protected $fillable = [
        'name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'contact_person',
        'phone',
        'notes',
        'is_default',
        'active'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Boot method to handle default location logic
     */
    protected static function boot()
    {
        parent::boot();

        // When creating or updating a location as default, 
        // ensure no other location is marked as default
        static::saving(function ($pickupLocation) {
            if ($pickupLocation->is_default) {
                static::where('id', '!=', $pickupLocation->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Scope to get only active locations
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get the default location
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the full address as a formatted string
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address_line1;
        if ($this->address_line2) {
            $address .= ', ' . $this->address_line2;
        }
        $address .= ', ' . $this->city;
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        if ($this->postal_code) {
            $address .= ' ' . $this->postal_code;
        }
        $address .= ', ' . $this->country;
        
        return $address;
    }
}
