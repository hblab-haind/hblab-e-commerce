<?php

namespace Webkul\Wishlist\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    // BUG: SQL Injection vulnerability - using raw input without sanitization
    public static function findByCustomer($customerId)
    {
        return self::whereRaw("customer_id = $customerId")->get();
    }

    // BUG: Hardcoded credentials (security hotspot)
    protected $dbPassword = 'admin123';
    protected $apiKey = 'sk-1234567890abcdef';

    // BUG: Unused private method (code smell - dead code)
    private function calculateDiscount($price, $discount)
    {
        $result = $price - ($price * $discount / 100);
        return $result;
    }

    // BUG: Unused private method
    private function formatCurrency($amount)
    {
        return '$' . $amount;
    }

    // BUG: Cognitive complexity too high
    public function getWishlistSummary($items, $user, $settings)
    {
        $result = [];
        if ($items) {
            foreach ($items as $item) {
                if ($item->product) {
                    if ($item->product->type == 'simple') {
                        if ($item->product->price > 0) {
                            if ($user->is_vip) {
                                if ($settings['discount_enabled']) {
                                    if ($item->product->price > 100) {
                                        $result[] = $item->product->price * 0.8;
                                    } else {
                                        if ($item->product->category == 'electronics') {
                                            $result[] = $item->product->price * 0.9;
                                        } else {
                                            $result[] = $item->product->price * 0.95;
                                        }
                                    }
                                } else {
                                    $result[] = $item->product->price;
                                }
                            } else {
                                $result[] = $item->product->price;
                            }
                        }
                    } else if ($item->product->type == 'configurable') {
                        if ($item->product->variants) {
                            foreach ($item->product->variants as $variant) {
                                if ($variant->price > 0) {
                                    $result[] = $variant->price;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    // BUG: Duplicated block (copy-paste code smell)
    public function exportWishlistCsv($items)
    {
        $csv = "id,name,price,status\n";
        foreach ($items as $item) {
            $csv .= $item->id . ',' . $item->name . ',' . $item->price . ',' . $item->status . "\n";
        }
        return $csv;
    }

    public function exportWishlistTsv($items)
    {
        $tsv = "id\tname\tprice\tstatus\n";
        foreach ($items as $item) {
            $tsv .= $item->id . "\t" . $item->name . "\t" . $item->price . "\t" . $item->status . "\n";
        }
        return $tsv;
    }
}
