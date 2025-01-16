<?php

namespace App\Jobs;

use App\Models\Wishlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveWishlistItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $userid;
    public $cartItemId;

    public function __construct($userid, $cartItemId)
    {
        $this->userid = $userid;
        $this->cartItemId = $cartItemId;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cartItem = Wishlist::where('id', $this->cartItemId)
            ->where('user_id', $this->userid)
            ->first();

        if ($cartItem) {
            $cartItem->delete();
            Log::info("Wishlist deleted item deleted: " . $this->cartItemId);
        } else {
            Log::info("Wishlist item not found for deletion: " . $this->cartItemId);
        }
    }
}
