<?php

namespace Modules\HotelBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => "required|unique:room_types,name,". @$this->room_type->id,
            "max_adult" => "required|numeric",
            "max_child" => "required|numeric",
            "max_guest" => "required|numeric",
            "no_bedroom" => "required|numeric",
            "no_living_room" => "required|numeric",
            "no_bathrooms" => "required|numeric",
            "base_charge" => "required|numeric",
            "breakfast_price" => "required|numeric",
            "lunch_price" => "required|numeric",
            "dinner_price" => "required|numeric",
            "description" => "required",
            "amenities" => "required",
            "amenities.*" => "required",
            "extra_adult" => "nullable",
            "extra_child" => "nullable",
            "bed_type_id" => "nullable",
            "extra_bed_type_id" => "nullable",
            "hotel_id" => "required",
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            "extra_adult" => null,
            "extra_child" => null,
            "breakfast_price" => $this->breakfast_charge,
            "bed_type_id" => $this->bed_type,
            "extra_bed_type_id" => null,
            "no_bedroom" => $this->no_of_bedroom,
            "no_living_room" => $this->no_of_living_room,
            "no_bathrooms" => $this->no_of_bathroom,
            "hotel_id" => $this->hotel_id
        ]);
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
