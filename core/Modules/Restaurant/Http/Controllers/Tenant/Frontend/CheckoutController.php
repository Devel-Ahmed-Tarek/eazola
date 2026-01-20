<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Frontend;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\CountryManage\Entities\State;
use Modules\Restaurant\Entities\FoodMenu;
use Modules\Restaurant\Entities\FoodMenuAttribute;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('restaurant::index');
    }

    public function confirmation()
    {
        return view('restaurant::frontend.order.confirmation');
    }

    public function menuContent(Request $request)
    {
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
        $food_menu = FoodMenu::where('id', $request->menu_id)->orWhere('menu_category_id',$request->input('menu_cat_id'))->first();

        if(!empty($food_menu)){
            $html = view('restaurant::components.frontent.menu.menu-content', compact('food_menu', 'lang_slug'))->render();
        }else{
            $html = "<p class='text-center text-danger'>" . __("No content available") . "</p>";
        }


        return response()->json(['html' => $html]);
    }

    public function addToCart(Request $request)
    {
        $redirect_url = $request->input('redirect_url');
        Session::put('redirect_url', $redirect_url);

        if (!Auth::guard('web')->check())
        {
            return response()->json(['redirect_url' => "success"]);
        }

        $food_menu = FoodMenu::with('menu_tax')->where('id', $request->menu_id)->first();

        if ($request->menu_quantity < $food_menu->min_purchase)
        {
            return response()->json([ "type" => "error","msg" => "Minimum purchase quantity is $food_menu->min_purchase" ]);
        }

        if (!$food_menu->max_purchase > 0)
        {
            $food_menu->max_purchase = 500;
        }

        if ($request->menu_quantity > $food_menu->max_purchase)
        {
            return response()->json([ "type" => "error","msg" => "Maximum purchase quantity is $food_menu->max_purchase" ]);
        }

        //  getting food menu attribute values
        $food_menu_attribute_ids = explode(",", $request->selected_attributes);


        if (count($food_menu_attribute_ids) < $request->attribute_count_value) {

            return response()->json(["type" => "error", "msg" => "Please select all attributes"]);
        }

        if (empty($request->selected_attributes) &&  $request->attribute_count_value > 0) {

            return response()->json(["type" => "error", "msg" => "Please select  attributes"]);
        }
        $food_menu_attribute = FoodMenuAttribute::whereIn('id', $food_menu_attribute_ids)->pluck('value');


        //  getting food menu additional total price and add with menu sale price
        $extra_price = FoodMenuAttribute::whereIn('id', $food_menu_attribute_ids)->sum('extra_price');

        $sal_price_with_extra = ($food_menu->sale_price + $extra_price);
        $states = State::all();

        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();

        $options = [
            'attribute_ids' => $request->selected_attributes,
            'attribute_values' => $food_menu_attribute,
            'image' => $food_menu->image_id ?? "",
            'tax' => $food_menu->menu_tax->tax ?? ""
        ];
        $menu_name = $food_menu->getTranslation("name", get_user_lang()) == "" ? "untitled" : $food_menu->getTranslation("name", get_user_lang());

        Cart::instance('restaurant')->add(['id' => $food_menu->id, 'name' => $menu_name, 'qty' => $request->menu_quantity, 'price' => $sal_price_with_extra, 'weight' => '0', 'options' => $options]);
        $cart_content = Cart::instance('restaurant')->content();

        $total_tax = $this->totalTaxCalculate();
        $subtotal = (float)str_replace(',', '', Cart::subtotal());
        $total = $subtotal + $total_tax;

        $html = view('restaurant::components.frontent.checkout.checkout-content', compact('food_menu', 'lang_slug', 'cart_content', 'total_tax', 'subtotal', 'total', 'states'))->render();

        return response()->json(['html' => $html, "msg" => "item added successfully"]);
    }

    public function cartQuantityIncrement(Request $request)
    {
        Cart::instance('restaurant')->get($request->rowId);
        $row = Cart::get($request->rowId);
        Cart::instance('restaurant')->update($request->rowId, $row->qty + 1);

        $total_tax = $this->totalTaxCalculate();

        $subtotal = (float)str_replace(',', '', Cart::instance('restaurant')->subtotal());
        $total = $subtotal + $total_tax;


        $html = view('restaurant::components.frontent.checkout.price-section', compact('total_tax', 'subtotal', 'total'))->render();

        return response()->json(['html' => $html]);
    }

    // price section update private method
    private function totalTaxCalculate()
    {
        $total_tax = 0; // Initialize total_tax before the loop

        foreach (Cart::instance('restaurant')->content() as $item) {
            // Accumulate the tax for each item
            $total_tax += ($item->price * $item->qty) * $item->options->tax / 100;
        }

        return $total_tax;
    }

    public function cartQuantityDecrement(Request $request)
    {
        $row = Cart::instance('restaurant')->get($request->rowId);
        Cart::instance('restaurant')->update($request->rowId, $row->qty - 1);

        $total_tax = $this->totalTaxCalculate();
        $subtotal = (float)str_replace(',', '', Cart::subtotal());
        $total = $subtotal + $total_tax;

        $html = view('restaurant::components.frontent.checkout.price-section', compact('total_tax', 'subtotal', 'total'))->render();
        return response()->json(['html' => $html]);
    }

    public function menuCheckoutPriceCalculate(Request $request)
    {
        $row = Cart::instance('restaurant')->get($request->rowId);
        Cart::instance('restaurant')->update($request->rowId, $request->quantity);

        $total_tax = $this->totalTaxCalculate();
        $subtotal = (float)str_replace(',', '', Cart::subtotal());
        $total = $subtotal + $total_tax;

        $html = view('restaurant::components.frontent.checkout.price-section', compact('total_tax', 'subtotal', 'total'))->render();
        return response()->json(['html' => $html]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('restaurant::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function menuPriceCalculate(Request $request)
    {
        $food_menu_attribute_ids = explode(",", $request->attribute_ids);
        $extra_price = FoodMenuAttribute::whereIn('id', $food_menu_attribute_ids)->sum('extra_price');
        $menu = FoodMenu::findOrFail($request->menu_id);

        $sal_price_with_extra = ($menu->sale_price + $extra_price) * $request->menu_quantity;

        $htmlData = '<span class="product__details__contents__price offer__price">' . @amount_with_currency_symbol($sal_price_with_extra) . '</span>' . ' ' .
            '<span class="product__details__contents__price regular__price">' . @amount_with_currency_symbol($menu->regular_price) . '</span>';

        return response()->json(['html' => $htmlData, 'sal_price_with_extra' => $sal_price_with_extra]);
    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('restaurant::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('restaurant::edit');
    }

    public function cartRemove(Request $request)
    {
        Cart::instance('restaurant')->remove($request->cart_id);

        $total_tax = $this->totalTaxCalculate();
        $subtotal = (float)str_replace(',', '', Cart::subtotal());
        $total = $subtotal + $total_tax;

        $html = view('restaurant::components.frontent.checkout.price-section', compact('total_tax', 'subtotal', 'total'))->render();
        return response()->json(['html' => $html]);

    }
}
