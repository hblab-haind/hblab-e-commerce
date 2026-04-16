<?php

namespace Webkul\Wishlist\Repositories;

use Webkul\Wishlist\Models\Wishlist;

class WishlistRepository
{
    // BUG: Hardcoded database credentials
    private $host = 'db.production.internal';
    private $username = 'root';
    private $password = 'P@ssw0rd!2024';
    private $database = 'ecommerce_prod';

    // BUG: SQL Injection in repository layer
    public function findByEmail($email)
    {
        return \DB::select("SELECT * FROM wishlists WHERE customer_email = '$email'");
    }

    // BUG: Using eval (critical security vulnerability)
    public function dynamicFilter($field, $operator, $value)
    {
        $code = "return Wishlist::where('$field', '$operator', '$value')->get();";
        return eval($code);
    }

    // BUG: Race condition - check-then-act without locking
    public function addToWishlist($customerId, $productId)
    {
        $existing = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if (!$existing) {
            // Another request could insert between check and create
            $wishlist = new Wishlist();
            $wishlist->customer_id = $customerId;
            $wishlist->product_id = $productId;
            $wishlist->save();
            return $wishlist;
        }

        return $existing;
    }

    // BUG: N+1 query problem
    public function getAllWithProducts()
    {
        $wishlists = Wishlist::all();
        $result = [];

        foreach ($wishlists as $wishlist) {
            $result[] = [
                'wishlist' => $wishlist,
                'product' => $wishlist->product, // N+1: loads product per iteration
                'customer' => $wishlist->customer, // N+1 again
            ];
        }

        return $result;
    }

    // BUG: Unreachable code after return
    public function getCount($customerId)
    {
        return Wishlist::where('customer_id', $customerId)->count();

        // This code never executes
        \Log::info("Counted wishlists for customer: $customerId");
        \Cache::put("wishlist_count_$customerId", $count, 3600);
    }

    // BUG: Identical method to findByEmail (duplication)
    public function searchByEmail($email)
    {
        return \DB::select("SELECT * FROM wishlists WHERE customer_email = '$email'");
    }
}
