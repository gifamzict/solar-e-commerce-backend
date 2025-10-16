<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implement admin authorization check (RBAC permission: preorder.notify)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mode' => 'required|in:ready,balance',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:email,sms',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'fulfillment_method' => 'required|in:pickup,delivery',
            
            // Balance mode requirements
            'payment_deadline' => 'required_if:mode,balance|date|date_format:Y-m-d|after:today',
            'reason' => 'required_if:mode,balance|string|max:500',
            
            // Ready mode requirements
            'ready_date' => 'required_if:mode,ready|date|date_format:Y-m-d',
            'override_payment_check' => 'sometimes|boolean',
            
            // Pickup requirements
            'pickup_location' => 'required_if:fulfillment_method,pickup|string|max:500',
            
            // Delivery requirements
            'shipping_address' => 'string|max:500',
            'city' => 'string|max:100',
            'state' => 'string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'mode.required' => 'Notification mode is required',
            'mode.in' => 'Mode must be either "ready" or "balance"',
            'channels.required' => 'At least one notification channel is required',
            'channels.min' => 'At least one notification channel must be selected',
            'channels.*.in' => 'Each channel must be either "email" or "sms"',
            'payment_deadline.required_if' => 'Payment deadline is required for balance notifications',
            'payment_deadline.after' => 'Payment deadline must be in the future',
            'reason.required_if' => 'Reason is required for balance notifications',
            'ready_date.required_if' => 'Ready date is required for ready notifications',
            'pickup_location.required_if' => 'Pickup location is required when fulfillment method is pickup',
            'fulfillment_method.in' => 'Fulfillment method must be either "pickup" or "delivery"',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation for delivery mode
            if ($this->fulfillment_method === 'delivery' && $this->mode === 'ready') {
                if (empty($this->shipping_address) && empty($this->getCustomerPreOrder()?->shipping_address)) {
                    $validator->errors()->add('shipping_address', 'Shipping address is required for delivery fulfillment.');
                }
                if (empty($this->city) && empty($this->getCustomerPreOrder()?->city)) {
                    $validator->errors()->add('city', 'City is required for delivery fulfillment.');
                }
                if (empty($this->state) && empty($this->getCustomerPreOrder()?->state)) {
                    $validator->errors()->add('state', 'State is required for delivery fulfillment.');
                }
            }
        });
    }

    /**
     * Get the customer pre-order from route parameters
     */
    private function getCustomerPreOrder()
    {
        return $this->route('customerPreOrder');
    }
}
