<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CartItem;
use Illuminate\Support\Facades\Log;

class RemoveCartItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userid;
    public $cartItemId;

    public function __construct($userid, $cartItemId)
    {
        $this->userid = $userid;
        $this->cartItemId = $cartItemId;
    }

    public function handle()
    {
        $cartItem = CartItem::where('id', $this->cartItemId)
            ->where('user_id', $this->userid)
            ->first();

        if ($cartItem) {
            $cartItem->delete();
            Log::info("Cart item deleted: " . $this->cartItemId);
        } else {
            Log::info("Cart item not found for deletion: " . $this->cartItemId);
        }
    }
}
