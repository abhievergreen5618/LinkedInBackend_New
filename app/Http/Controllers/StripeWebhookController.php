<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Event;
use Stripe\Customer;
use Illuminate\Support\Facades\Storage;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $payload = $request->getContent();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Event::constructFrom(
                json_decode($payload, true),
                $sig_header,
                env('STRIPE_WEBHOOK_SECRET')
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Save the webhook response to a file
        $filename = 'stripe_webhook_canceled.json';
        Storage::put($filename, $payload);

        // Handle the event
        switch ($event->type) {
                case 'subscription_schedule.canceled':
                $subscriptionSchedule = $event->data->object;
                $customer = Customer::retrieve($subscriptionSchedule->customer->customer);
                dd($customer);
                break;
                case 'subscription_schedule.expiring':
                    $subscriptionSchedule = $event->data->object;
                break;
            // Add more cases for other event types you want to handle
            default:
                // Unexpected event type
                return response()->json(['error' => 'Unexpected event type'], 400);
        }


        return response()->json(['success' => true]);
    }

    public function replayWebhook($filename)
    {
        // Get the contents of the specified file
        $payload = Storage::get($filename);

        // Construct a new event object from the payload
        $event = \Stripe\Event::constructFrom(json_decode($payload, true));

        // Handle the event
        switch ($event->type) {
            case 'charge.succeeded':
                // Handle successful charge event
                break;
            case 'charge.failed':
                // Handle failed charge event
                break;
            // Add more event types here
        }

        return response()->json(['success' => true]);
    }
}
