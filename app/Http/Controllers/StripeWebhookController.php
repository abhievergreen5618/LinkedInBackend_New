<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Event;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
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

        // Handle the event
        switch ($event->type) {
                case 'subscription_schedule.canceled':
                $subscriptionSchedule = $event->data->object;
                // get_customer($subscriptionSchedule.data.customer);
                dd($subscriptionSchedule->customer);
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
}
