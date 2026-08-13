<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AddressService
{
    public function getForUser(User $user): Collection
    {
        return Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(User $user, array $data): Address
    {
        // If this is the user's first address or is_default is true, mark default
        $existingCount = Address::where('user_id', $user->id)->count();
        if ($existingCount === 0 || !empty($data['is_default'])) {
            $data['is_default'] = true;
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        } else {
            $data['is_default'] = false;
        }

        $data['user_id'] = $user->id;
        return Address::create($data);
    }

    public function update(Address $address, array $data): Address
    {
        if (!empty($data['is_default']) && !$address->is_default) {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        }

        $address->update($data);
        return $address->fresh();
    }

    public function delete(Address $address): void
    {
        $userId = $address->user_id;
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $first = Address::where('user_id', $userId)->first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        }
    }

    public function setDefault(Address $address): Address
    {
        Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return $address->fresh();
    }
}
