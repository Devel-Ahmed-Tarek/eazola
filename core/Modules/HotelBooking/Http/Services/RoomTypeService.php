<?php

namespace Modules\HotelBooking\Http\Services;



use App\Helpers\SanitizeInput;
use Modules\HotelBooking\Entities\Room_type;
use Modules\HotelBooking\Entities\Room_typeAmenity;
use Modules\HotelBooking\Entities\RoomType;
use Modules\HotelBooking\Entities\RoomTypeAmenity;

class RoomTypeService
{
    public static function createOrUpdate($request,$roomType = null){
        if(is_null($roomType)){
            $roomType = new RoomType();
        }
        $roomType->setTranslation('name',$request['lang'], SanitizeInput::esc_html($request['name']));
        $roomType->setTranslation('description',$request['lang'], SanitizeInput::esc_html($request['description']));
        $roomType->max_guest = $request['max_guest'];
        $roomType->max_adult = $request['max_adult'];
        $roomType->max_child = $request['max_child'];
        $roomType->no_bedroom = $request['no_bedroom'];
        $roomType->no_living_room = $request['no_living_room'];
        $roomType->no_bathrooms = $request['no_bathrooms'];
        $roomType->base_charge = $request['base_charge'];
        $roomType->breakfast_price = $request['breakfast_price'];
        $roomType->lunch_price = $request['lunch_price'];
        $roomType->dinner_price = $request['dinner_price'];
        $roomType->bed_type_id = $request['bed_type_id'];
        $roomType->hotel_id = $request['hotel_id'];
        $roomType->save();

        return $roomType;
    }


    public static function CreateOrUpdateRoomTypeAmenities($data,$room_type, $type= null){

        if(is_null($type)){
            // insert room type amenities
            $amenities = self::store_room_type_amenities($data,$room_type->id);
        }else{
            // first delete previous amenities
            self::delete_room_type_amenities($room_type->id);
            //prepare amenities for store
            $amenities = self::prepare_room_type_amenities($data,$room_type->id);
            // insert amenities
            $amenities = self::store_room_type_amenities($data,$room_type->id);
        }
    }

    public static function store_room_type_amenities($data,$id){
        $data = self::prepare_room_type_amenities($data,$id);
        return RoomTypeAmenity::insert($data);
    }

    private static function prepare_room_type_amenities($data,$id){
        $dataVariable = [];
        foreach($data as $key => $value){
            $dataVariable[] = [
                "room_type_id" => $id,
                "amenity_id" => $value,
            ];
        }
        return $dataVariable;
    }

    public static function delete_room_type_amenities($id){
        return RoomTypeAmenity::where("room_type_id",$id)->delete();
    }

}
