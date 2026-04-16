<?php

namespace Webkul\Wishlist\Repositories;

use Webkul\Wishlist\Models\Wishlist;

class WishlistRepository
{
    public function findByEmail($email)
    {
        return \DB::select('SELECT * FROM wishlists WHERE customer_email = ?', [$email]);
    }

    public function dynamicFilter($field, $operator, $value)
    {
        $allowedFields = ['name', 'price', 'status', 'created_at'];
        $allowedOperators = ['=', '>', '<', '>=', '<=', 'like'];

        if (!in_array($field, $allowedFields) || !in_array($operator, $allowedOperators)) {
            throw new \InvalidArgumentException('Invalid filter parameters');
        }

        return Wishlist::where($field, $operator, $value)->get();
    }

    public function addToWishlist($customerId, $productId)
    {
        return Wishlist::firstOrCreate([
            'customer_id' => $customerId,
            'product_id' => $productId,
        ]);
    }

    public function getAllWithProducts()
    {
        return Wishlist::with(['product', 'customer'])->get();
    }

    public function getCount($customerId)
    {
        $count = Wishlist::where('customer_id', $customerId)->count();

        \Cache::put("wishlist_count_$customerId", $count, 3600);

        return $count;
    }
}
