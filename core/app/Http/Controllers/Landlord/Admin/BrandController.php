<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Facades\GlobalLanguage;
use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:brand-list|brand-create|brand-edit|brand-delete',['only' => ['index']]);
        $this->middleware('permission:brand-create',['only' => ['store']]);
        $this->middleware('permission:brand-edit',['only' => ['update','clone']]);
        $this->middleware('permission:brand-delete',['only' => ['delete','bulk_action']]);
    }
    public function index(Request $request)
    {
        $all_brands = Brand::latest()->get();

        return view('landlord.admin.brand.brand', [
            'all_brands' => $all_brands,
            'default_lang' => $request->get('lang') ?? GlobalLanguage::default_slug(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'lang' => 'required|string|max:20',
            'url' => 'required|string|max:2000',
            'image' => 'required|string',
            'status' => 'nullable|string',
            'ai_bulk_translations_json' => 'nullable|string|max:5000000',
        ]);

        $brand = new Brand;
        $brand->setTranslation('url', $request->lang, SanitizeInput::esc_html($request->url));
        $brand->image = $request->image;
        $brand->status = $request->status;
        $brand->save();
        $this->mergeAiBulkTranslationsIfPresent($brand, $request);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'lang' => 'required|string|max:20',
            'url' => 'required|string|max:2000',
            'image' => 'nullable|string',
            'status' => 'nullable|string',
            'ai_bulk_translations_json' => 'nullable|string|max:5000000',
        ]);

        $brand = Brand::findOrFail($request->id);
        $brand->setTranslation('url', $request->lang, SanitizeInput::esc_html($request->url));
        $brand->image = $request->image;
        $brand->status = $request->status;
        $brand->save();
        $this->mergeAiBulkTranslationsIfPresent($brand, $request);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    private function mergeAiBulkTranslationsIfPresent(Brand $brand, Request $request): void
    {
        $raw = $request->input('ai_bulk_translations_json');
        if (! is_string($raw) || trim($raw) === '') {
            return;
        }

        $bulk = json_decode($raw, true);
        if (! is_array($bulk)) {
            return;
        }

        foreach ($bulk as $slug => $t) {
            if (! is_string($slug) || ! is_array($t)) {
                continue;
            }
            $brand->setTranslation('url', $slug, SanitizeInput::esc_html((string) ($t['url'] ?? '')));
        }

        $brand->save();
    }

    public function delete(Request $request,$id){
        Brand::findOrFail($id)->delete();
        return response()->danger(ResponseMessage::delete());
    }

    public function bulk_action(Request $request){

        Brand::whereIn('id',$request->ids)->delete();
        return redirect()->back()->with([
            'msg' => __('Client Delete Success...'),
            'type' => 'danger'
        ]);
    }
}
