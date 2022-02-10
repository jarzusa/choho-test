<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Adviser;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChohoController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            $advisers = Adviser::get();
            if (count($advisers) > 0) {
                foreach ($advisers as $key => &$adviser) {
                    $clients = $this->getClientsAssigned($adviser);
                    $orders = $this->getTotalOrdersByAdviser($adviser);
                    $adviser['clients'] = $clients;
                    $adviser['total_orders'] = $orders;
                    $adviser['clients_assigned'] = count($adviser['clients']);
                }
                return response()->json([
                    'success' => true,
                    'data' => $advisers,
                ]);
            }
            return response()->json([
                'success' => false,
                'msg' => 'No hay registros que mostrar',
            ]);
        } catch (Exception $e) {
            error_log($e, 0);
            return response()->json([
                'success'  => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtains orders by client
     *
     * @param [object] $client
     * @return void
     */
    public function getOrderByClient($client)
    {
        try {
            $orders = Order::where(['client_id' => $client->id])->get();
            foreach ($orders as $key => &$order) {
                $products = $this->getProductByOrder($order);
                $order['products'] = $products;
            }
            return $orders;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getProductByOrder($order)
    {
        try {
            $products = [];
            $ids = OrderProduct::where(['order_id' => $order->id])->get();
            foreach ($ids as $key => $id) {
                $product = Product::where(['id' => $id->product_id])->get();
                array_push($products, $product);
            }
            return $products;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtains clients assigned by adviser
     *
     * @param [object] $adviser
     * @return void
     */
    public function getClientsAssigned($adviser)
    {
        try {
            $clients = Client::where(['adviser_id' => $adviser->id])->get();
            foreach ($clients as $key => &$client) {
                $orders = $this->getOrderByClient($client);
                $client['detail_orders'] = $orders;
                $client['total_orders'] = count($client['detail_orders']);
            }
            return $clients;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtains orders by adviser
     *
     * @param [object] $adviser
     * @return void
     */
    public function getTotalOrdersByAdviser($adviser)
    {
        try {
            $orders = Order::where(['adviser_id' => $adviser->id])->get();
            return count($orders);
        } catch (Exception $e) {
            return 0;
        }
    }
}
