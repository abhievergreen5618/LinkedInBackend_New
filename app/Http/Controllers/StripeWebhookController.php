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

    public function replayWebhook()
    {
        // Get the contents of the specified file
        $json = '{
            "id": "evt_1N13CzBXwIeXC3Ja9SD3EXda",
            "object": "event",
            "api_version": "2022-11-15",
            "created": 1682495773,
            "data": {
                "object": {
                    "id": "sub_sched_1N13CyBXwIeXC3JaRNkzdCuN",
                    "object": "subscription_schedule",
                    "application": null,
                    "canceled_at": 1682495773,
                    "completed_at": null,
                    "created": 1682495772,
                    "current_phase": null,
                    "customer": "cus_NmcRoDAuOGcy1x",
                    "default_settings": {
                        "application_fee_percent": null,
                        "automatic_tax": {
                            "enabled": false
                        },
                        "billing_cycle_anchor": "automatic",
                        "billing_thresholds": null,
                        "collection_method": "charge_automatically",
                        "default_payment_method": null,
                        "default_source": null,
                        "description": null,
                        "invoice_settings": null,
                        "on_behalf_of": null,
                        "transfer_data": null
                    },
                    "end_behavior": "release",
                    "livemode": false,
                    "metadata": {},
                    "phases": [{
                            "add_invoice_items": [

                            ],
                            "application_fee_percent": null,
                            "billing_cycle_anchor": null,
                            "billing_thresholds": null,
                            "collection_method": null,
                            "coupon": null,
                            "currency": "usd",
                            "default_payment_method": null,
                            "default_tax_rates": [

                            ],
                            "description": null,
                            "end_date": 1685087772,
                            "invoice_settings": null,
                            "items": [{
                                "billing_thresholds": null,
                                "metadata": {},
                                "plan": "price_1N13CxBXwIeXC3Ja9wG70mhd",
                                "price": "price_1N13CxBXwIeXC3Ja9wG70mhd",
                                "quantity": 1,
                                "tax_rates": [

                                ]
                            }],
                            "metadata": {},
                            "on_behalf_of": null,
                            "proration_behavior": "create_prorations",
                            "start_date": 1682495772,
                            "transfer_data": null,
                            "trial_end": null
                        },
                        {
                            "add_invoice_items": [

                            ],
                            "application_fee_percent": null,
                            "billing_cycle_anchor": null,
                            "billing_thresholds": null,
                            "collection_method": null,
                            "coupon": null,
                            "currency": "usd",
                            "default_payment_method": null,
                            "default_tax_rates": [

                            ],
                            "description": null,
                            "end_date": 1687766172,
                            "invoice_settings": null,
                            "items": [{
                                "billing_thresholds": null,
                                "metadata": {},
                                "plan": "price_1N13CxBXwIeXC3Ja9wG70mhd",
                                "price": "price_1N13CxBXwIeXC3Ja9wG70mhd",
                                "quantity": 2,
                                "tax_rates": [

                                ]
                            }],
                            "metadata": {},
                            "on_behalf_of": null,
                            "proration_behavior": "create_prorations",
                            "start_date": 1685087772,
                            "transfer_data": null,
                            "trial_end": null
                        }
                    ],
                    "released_at": null,
                    "released_subscription": null,
                    "renewal_interval": null,
                    "status": "canceled",
                    "subscription": "sub_1N13CyBXwIeXC3JavH4e64wK",
                    "test_clock": null
                },
                "previous_attributes": {
                    "canceled_at": null,
                    "current_phase": {
                        "end_date": 1685087772,
                        "start_date": 1682495772
                    },
                    "status": "active"
                }
            },
            "livemode": false,
            "pending_webhooks": 1,
            "request": {
                "id": "req_mrYuPNxYJunfxj",
                "idempotency_key": "d8f318c4-7dcf-4926-8907-5fd83b584521"
            },
            "type": "subscription_schedule.canceled"
        }';

        $payload = json_decode($json,true);
        // Construct a new event object from the payload
        $event = \Stripe\Event::constructFrom($payload);

        // Handle the event
        switch ($event->type) {
            case 'subscription_schedule.canceled':
            $subscriptionSchedule = $event->data->object;
            // $customer = Customer::retrieve($subscriptionSchedule->customer->customer);
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
