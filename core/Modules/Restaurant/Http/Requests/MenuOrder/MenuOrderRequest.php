<?php

namespace Modules\Restaurant\Http\Requests\MenuOrder;

use Illuminate\Foundation\Http\FormRequest;

class MenuOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "delivery_option" => "required",
            "state" => "required",
            "city" => "nullable",
            "email" => "required|email",
            "full_name" => "required",
            "phone" => "required",
            "selected_payment_gateway" => "required",
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
