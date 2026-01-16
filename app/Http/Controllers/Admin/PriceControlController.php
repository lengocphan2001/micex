<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceControl;
use Illuminate\Http\Request;

class PriceControlController extends Controller
{
    /**
     * Display price control panel
     */
    public function index()
    {
        $symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT'];
        $controls = [];
        
        foreach ($symbols as $symbol) {
            $controls[$symbol] = PriceControl::getOrCreate($symbol);
        }
        
        return view('admin.price-control', compact('controls', 'symbols'));
    }

    /**
     * Update price control settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'mode' => 'required|in:normal,up,down,trap',
            'strength' => 'required|integer|min:1|max:10',
            'enabled' => 'boolean',
            'bias_dir' => 'integer|in:-1,0,1',
            'last_seconds' => 'integer|min:1|max:60',
            'bias_power' => 'integer|min:0|max:100',
        ]);

        $control = PriceControl::getOrCreate($request->symbol);
        
        $control->update([
            'mode' => $request->mode,
            'strength' => $request->strength,
            'enabled' => $request->has('enabled') ? (bool)$request->enabled : false,
            'bias_dir' => $request->input('bias_dir', 0),
            'last_seconds' => $request->input('last_seconds', 10),
            'bias_power' => $request->input('bias_power', 10),
        ]);

        return redirect()->back()->with('success', 'Price control updated successfully!');
    }
}
