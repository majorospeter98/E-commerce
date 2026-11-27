<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('account/Order', [
            'orders' => Auth::user()->orders()->with('items')->latest('id')->get(),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $order = Order::create(['user_id' => Auth::id()]);
            $prices = Item::whereKey($request->validated('orders.*.item_id'))->pluck('price', 'id');

            foreach ($request->validated('orders') as $item) {
                OrderItem::create([
                    'item_id' => $item['item_id'],
                    'order_id' => $order->id,
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $prices[$item['item_id']],
                ]);
            }
        });

        if (! Auth::check()) {
            return redirect('/cart');
        }

        Inertia::flash('message', __('msg.order.success'));

        return redirect('/account/order');
    }
}
