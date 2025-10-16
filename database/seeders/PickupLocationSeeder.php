<?php

namespace Database\Seeders;

use App\Models\PickupLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PickupLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Main Store - Downtown',
                'address_line1' => '123 Commerce Street',
                'address_line2' => 'Suite 100',
                'city' => 'Lagos',
                'state' => 'Lagos State',
                'postal_code' => '100001',
                'country' => 'Nigeria',
                'contact_person' => 'John Doe',
                'phone' => '+234-801-234-5678',
                'notes' => 'Main headquarters with full inventory. Open 24/7.',
                'is_default' => true,
                'active' => true,
            ],
            [
                'name' => 'Victoria Island Branch',
                'address_line1' => '45 Ahmadu Bello Way',
                'address_line2' => null,
                'city' => 'Lagos',
                'state' => 'Lagos State',
                'postal_code' => '106104',
                'country' => 'Nigeria',
                'contact_person' => 'Jane Smith',
                'phone' => '+234-802-345-6789',
                'notes' => 'Premium location with express pickup service.',
                'is_default' => false,
                'active' => true,
            ],
            [
                'name' => 'Ikeja City Mall',
                'address_line1' => 'Obafemi Awolowo Way',
                'address_line2' => 'Ground Floor, Shop G12',
                'city' => 'Ikeja',
                'state' => 'Lagos State',
                'postal_code' => '100271',
                'country' => 'Nigeria',
                'contact_person' => 'Mike Johnson',
                'phone' => '+234-803-456-7890',
                'notes' => 'Mall location with extended weekend hours.',
                'is_default' => false,
                'active' => true,
            ],
            [
                'name' => 'Abuja Central Hub',
                'address_line1' => '78 Gana Street',
                'address_line2' => 'Maitama District',
                'city' => 'Abuja',
                'state' => 'FCT',
                'postal_code' => '900103',
                'country' => 'Nigeria',
                'contact_person' => 'Sarah Williams',
                'phone' => '+234-804-567-8901',
                'notes' => 'Serving FCT and surrounding areas.',
                'is_default' => false,
                'active' => true,
            ],
            [
                'name' => 'Port Harcourt Outlet',
                'address_line1' => '12 Trans-Amadi Industrial Layout',
                'address_line2' => null,
                'city' => 'Port Harcourt',
                'state' => 'Rivers State',
                'postal_code' => '500102',
                'country' => 'Nigeria',
                'contact_person' => 'David Brown',
                'phone' => '+234-805-678-9012',
                'notes' => 'Currently being renovated. Limited hours.',
                'is_default' => false,
                'active' => false,
            ],
        ];

        foreach ($locations as $location) {
            PickupLocation::create($location);
        }
    }
}
