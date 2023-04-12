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
        $this->redirect_uri = "https://ilgjnilamjnkbfhoohaddnmfoecablec.chromiumapp.org/";
        $this->audience = "https://dev-1fo4733y5vtbyqis.us.auth0.com/api/v2/";
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

                $access_token = $this->get_access_token();

                $user_id = $request['user_id'];
                 
                $url = "https://".$this->domain."/api/v2/users/".$user_id;
                
                $metadata = [
                                'stripe_subscription_id' => $response->subscription,
                                'subscription_start' => date('Y-m-d H:i:s',$response->created),
                                'subscription_end' => date('Y-m-d H:i:s',$response->expires_at),
                                'role' => 'paid',
                            ];
                                
                $metadata = json_encode($metadata);
                
                $this->update_user_meta($access_token,$user_id,$url,$metadata);
                
                return redirect()->route("thankyou_page");

            }
           
        }
    }
    
    public function update_user_meta($access_token,$user_id,$url,$metadata)
    {
         $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "PATCH",
          CURLOPT_POSTFIELDS => "{\"user_metadata\": $metadata}",
          CURLOPT_HTTPHEADER => [
            "authorization: Bearer $access_token",
            "content-type: application/json"
          ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        //   dd("cURL Error #:" . $err);
        } else {
        //   dd($response);
        }
    }
    
    
    public function get_access_token()
    {
            $curl = curl_init();

            curl_setopt_array($curl, [
              CURLOPT_URL => "https://".$this->domain."/oauth/token",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=".$this->client_id."&client_secret=".$this->client_secret."&audience=".$this->audience,
              CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded"
              ],
            ]);
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);
            
            if ($err) {
                  dd("cURL Error #:" . $err);
                } else {
                $response = json_decode($response);
                 return $response->access_token;
                }
    }
    
    public function cancel(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        if (!empty($request['session_id']) && !empty($request['user_id'])) {
            $response = Session::retrieve($request['session_id']);
            if ($response->payment_status == "unpaid") {

                $access_token = $this->get_access_token();

                $user_id = $request['user_id'];
                 
                $url = "https://".$this->domain."/api/v2/users/".$user_id;
                
                $metadata = [
                                'stripe_subscription_id' => $response->subscription,
                                'subscription_start' => date('Y-m-d H:i:s',$response->created),
                                'subscription_end' => date('Y-m-d H:i:s',$response->expires_at),
                                'role' => 'free',
                            ];
                                
                $metadata = json_encode($metadata);
                
                $this->update_user_meta($access_token,$user_id,$url,$metadata);
                
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