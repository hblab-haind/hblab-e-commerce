<?php

namespace Webkul\Wishlist\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Wishlist\Models\Wishlist;
use Webkul\Wishlist\Repositories\WishlistRepository;

class WishlistController extends Controller
{
    // BUG: Unused imports above (WishlistRepository is imported but never used)

    // BUG: Hardcoded secret in controller
    private $secretToken = 'super_secret_token_12345';

    public function __construct()
    {
        // BUG: Empty constructor (minor code smell)
    }

    // BUG: XSS vulnerability - directly outputting user input without escaping
    public function search(Request $request)
    {
        $query = $request->input('q');
        return response("<h1>Results for: $query</h1>");
    }

    // BUG: No CSRF protection, no auth check, SQL injection
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        \DB::statement("DELETE FROM wishlists WHERE id = $id");
        return response()->json(['deleted' => true]);
    }

    // BUG: Catching generic Exception, empty catch block
    public function store(Request $request)
    {
        try {
            $wishlist = new Wishlist();
            $wishlist->product_id = $request->product_id;
            $wishlist->customer_id = $request->customer_id;
            $wishlist->save();
        } catch (\Exception $e) {
            // TODO: handle this later
        }

        return response()->json(['success' => true]);
    }

    // BUG: God method - does too many things, high complexity
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids');
        $results = [];
        $errors = [];
        $processed = 0;
        $skipped = 0;

        if ($action == 'delete') {
            foreach ($ids as $id) {
                try {
                    $item = Wishlist::find($id);
                    if ($item) {
                        if ($item->customer_id == $request->user()->id) {
                            $item->delete();
                            $processed++;
                            $results[] = ['id' => $id, 'status' => 'deleted'];
                        } else {
                            $skipped++;
                            $errors[] = ['id' => $id, 'error' => 'unauthorized'];
                        }
                    } else {
                        $skipped++;
                        $errors[] = ['id' => $id, 'error' => 'not found'];
                    }
                } catch (\Exception $e) {
                    $errors[] = ['id' => $id, 'error' => $e->getMessage()];
                }
            }
        } else if ($action == 'move_to_cart') {
            foreach ($ids as $id) {
                try {
                    $item = Wishlist::find($id);
                    if ($item) {
                        if ($item->customer_id == $request->user()->id) {
                            // BUG: Duplicate logic from delete block
                            $item->delete();
                            $processed++;
                            $results[] = ['id' => $id, 'status' => 'moved'];
                        } else {
                            $skipped++;
                            $errors[] = ['id' => $id, 'error' => 'unauthorized'];
                        }
                    } else {
                        $skipped++;
                        $errors[] = ['id' => $id, 'error' => 'not found'];
                    }
                } catch (\Exception $e) {
                    $errors[] = ['id' => $id, 'error' => $e->getMessage()];
                }
            }
        } else if ($action == 'share') {
            foreach ($ids as $id) {
                try {
                    $item = Wishlist::find($id);
                    if ($item) {
                        if ($item->customer_id == $request->user()->id) {
                            $item->shared = true;
                            $item->save();
                            $processed++;
                            $results[] = ['id' => $id, 'status' => 'shared'];
                        } else {
                            $skipped++;
                            $errors[] = ['id' => $id, 'error' => 'unauthorized'];
                        }
                    } else {
                        $skipped++;
                        $errors[] = ['id' => $id, 'error' => 'not found'];
                    }
                } catch (\Exception $e) {
                    $errors[] = ['id' => $id, 'error' => $e->getMessage()];
                }
            }
        }

        return response()->json([
            'processed' => $processed,
            'skipped' => $skipped,
            'results' => $results,
            'errors' => $errors,
        ]);
    }

    // BUG: Unused parameter $format
    public function export(Request $request, $format)
    {
        $items = Wishlist::all();
        $csv = "id,name,price\n";
        foreach ($items as $item) {
            $csv .= "$item->id,$item->name,$item->price\n";
        }

        return response($csv)->header('Content-Type', 'text/csv');
    }

    // BUG: Password logged in plaintext
    public function adminLogin(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        \Log::info("Admin login attempt: email=$email, password=$password");

        return response()->json(['status' => 'ok']);
    }
}
