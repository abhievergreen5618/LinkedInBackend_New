<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Auth0\SDK\Auth0;
use Auth0\SDK\API\Management;

class StripeController extends Controller
{
    public function __construct()
    {
        $this->domain = env('AUTH0_DOMAIN');
        $this->client_id = env('AUTH0_CLIENT_ID');
        $this->client_secret = env('AUTH0_CLIENT_SECRET');
    }

    public function customerPortal(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $customer = $request->user_id;

        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $customer,
            'return_url' => route('home'),
        ]);

        return response()->json(["session_url"  => $session->url]);
    }

    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $parm = "?user_id=".$request['user_id']."&session_id={CHECKOUT_SESSION_ID}";
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $request['lineItems'],
            'mode' => 'subscription',
            'success_url' => route('success_url').$parm,
            'cancel_url' => route('cancel_url') .$parm,
            'customer' => $request['clientReferenceId'],
        ]);

        return response()->json(['session_id' => $session->id]);

    }

    public function success(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        if (!empty($request['session_id']) && !empty($request['user_id'])) {
            $response = Session::retrieve($request['session_id']);
            if ($response->payment_status == "paid") {

                $access_token = get_access_token();

                $user_id = $request['user_id'];

                $url = "https://".$this->domain."/api/v2/users/".$user_id;

                $metadata = [
                                'stripe_subscription_id' => $response->subscription,
                                'subscription_start' => date('Y-m-d H:i:s',$response->created),
                                'subscription_end' => date('Y-m-d H:i:s',$response->expires_at),
                                'role' => 'paid',
                            ];

                $metadata = json_encode($metadata);

                update_user_meta($access_token,$url,$metadata);

                return redirect()->route("thankyou_page");

            }

        }
    }

    public function cancel(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        if (!empty($request['session_id']) && !empty($request['user_id'])) {
            $response = Session::retrieve($request['session_id']);
            if ($response->payment_status == "unpaid") {

                $access_token = get_access_token();

                $user_id = $request['user_id'];

                $url = "https://".$this->domain."/api/v2/users/".$user_id;

                $metadata = [
                                'stripe_subscription_id' => $response->subscription,
                                'subscription_start' => date('Y-m-d H:i:s',$response->created),
                                'subscription_end' => date('Y-m-d H:i:s',$response->expires_at),
                                'role' => 'free',
                            ];

                $metadata = json_encode($metadata);

                update_user_meta($access_token,$url,$metadata);

                return redirect()->route("failed_page");

            }

        }
    }

    public function thankyou(Request $request)
    {
        return view("thankyoupage");
    }

     public function failed(Request $request)
    {
        return view("cancelpage");
    }
}
