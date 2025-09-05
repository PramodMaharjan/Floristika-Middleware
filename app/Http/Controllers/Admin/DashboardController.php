<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        $totalOrders = $orders->count();
        return view('admin.dashboard', compact('orders', 'totalOrders'));
    }
}
