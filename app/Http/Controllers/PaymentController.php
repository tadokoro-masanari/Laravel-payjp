<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payjp\Charge;
use Payjp\Customer;
use Payjp\Payjp;

class PaymentController extends Controller
{
    public function index(){
        $user = auth()->user();
        $cardList = [];

        //既にpayjpに登録済みの場合
        if(!empty($user->payjp_customer_id)){
            //カード一覧情報を取得
            Payjp::setApiKey(config('payjp.secret_key'));
            $cardDatas = Customer::retrieve($user->payjp_customer_id)->cards->data;
            foreach ($cardDatas as $cardData){
                $cardList[] = [
                    'id' =>$cardData->id,
                    'cardNumber' => "**** **** **** {$cardData->last4}",
                    'brand' => $cardData->brand,
                    'exp_year' => $cardData->exp_year,
                    'exp_month' => $cardData->exp_month,
                    'name' => $cardData->name,
                ];
            }
        }

        return view('payment', compact('cardList'));
    }

    public function payment(Request $request) {
        if(empty($request->get('payjp-token')) && !$request->get('payjp_card_id')){
            abort(404);
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();
            //  シークレットキーを設定
            Payjp::setApiKey(config('payjp.secret_key'));
            if(!empty($request->get('payjp_card_id'))){
                $customer = Customer::retrieve($user['payjp_customer_id']);
                $customer->default_card = $request->get('payjp_card_id');
                $customer->save();
            }elseif(!empty($user['pay_customer_id'])) {
                $customer = Customer::retrieve($user['pay_customer_id']);
                $card = $customer->cards->create([
                    'card' => $request->get('payjp-token'),
                ]);
                $customer->delete_card = $card->id;
                $customer->save();
            }else {
                $customer = Customer::create([
                    'card' => $request->get('payjp-token'),
                ]);

                $user->payjp_customer_id = $customer->id;
                $user->save();
            }

            Charge::create([
                'customer' => $customer->id,
                'amount' => 100,
                "currency" => 'jpy',
            ]);

            DB::commit();

            return redirect(route('payment'))->with('message', 'お支払いが完了しました');

        }catch (\Exception $e){
            Log::error($e);
            DB::rollBack();

            if (strpos($e, 'has already been used') !== false){
                return redirect()->back()->with('error-message', '既に登録されているカード情報です');
            }
            return redirect()->back();
        }
    }
}
