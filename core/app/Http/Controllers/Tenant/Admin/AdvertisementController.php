<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Helpers\FlashMsg;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    private const BASE_PATH = 'tenant.admin.advertisement.';

    public function __construct()
    {
        $this->middleware('permission:advertisement-list|advertisement-edit|advertisement-delete', ['only' => ['index']]);
        $this->middleware('permission:advertisement-create', ['only' => ['new_advertisement', 'store_advertisement']]);
        $this->middleware('permission:advertisement-edit', ['only' => ['edit_advertisement', 'update_advertisement']]);
        $this->middleware('permission:advertisement-delete', ['only' => ['bulk_action', 'delete_advertisement']]);
    }

    public function index(Request $request)
    {
        $all_advertisements = Advertisement::latest()->get();

        return view(self::BASE_PATH.'index', [
            'all_advertisements' => $all_advertisements,
            'default_lang' => $request->get('lang') ?? GlobalLanguage::default_slug(),
        ]);
    }

    public function new_advertisement(Request $request)
    {
        return view(self::BASE_PATH.'new', [
            'default_lang' => $request->get('lang') ?? GlobalLanguage::default_slug(),
        ]);
    }

    public function store_advertisement(Request $request)
    {
        $request->validate([
            'lang' => 'required|string|max:20',
            'title' => 'required|string|max:2000',
            'type' => 'required|string',
            'size' => 'required',
            'status' => 'required',
            'slot' => 'nullable',
            'embed_code' => 'nullable',
            'redirect_url' => 'nullable',
            'image' => 'nullable',
            'ai_bulk_translations_json' => 'nullable|string|max:5000000',
        ]);

        $adv = new Advertisement;
        $adv->setTranslation('title', $request->lang, purify_html($request->title));
        $adv->type = $request->type;
        $adv->size = $request->size;
        $adv->status = $request->status;
        $adv->slot = $request->slot;
        $adv->embed_code = $request->embed_code;
        $adv->redirect_url = purify_html($request->redirect_url);
        $adv->image = $request->image;
        $adv->save();

        $this->mergeAiBulkTranslationsIfPresent($adv, $request);

        return redirect()->back()->with(FlashMsg::item_new('New Advertisement Created Successfully'));
    }

    public function edit_advertisement(Request $request, $id)
    {
        $add = Advertisement::findOrFail($id);

        return view(self::BASE_PATH.'edit', [
            'add' => $add,
            'default_lang' => $request->get('lang') ?? GlobalLanguage::default_slug(),
        ]);
    }

    public function update_advertisement(Request $request, $id)
    {
        $request->validate([
            'lang' => 'required|string|max:20',
            'title' => 'required|string|max:2000',
            'type' => 'required|string',
            'size' => 'required',
            'status' => 'required',
            'slot' => 'nullable',
            'embed_code' => 'nullable',
            'redirect_url' => 'nullable',
            'image' => 'nullable',
            'ai_bulk_translations_json' => 'nullable|string|max:5000000',
        ]);

        $adv = Advertisement::findOrFail($id);
        $adv->setTranslation('title', $request->lang, purify_html($request->title));
        $adv->type = purify_html($request->type);
        $adv->size = $request->size;
        $adv->status = $request->status;
        $adv->slot = $request->slot;
        $adv->embed_code = $request->embed_code;
        $adv->redirect_url = purify_html($request->redirect_url);
        $adv->image = $request->image;
        $adv->save();

        $this->mergeAiBulkTranslationsIfPresent($adv, $request);

        return redirect()->back()->with(FlashMsg::item_new(' Advertisement Updated Successfully'));
    }

    public function delete_advertisement($id)
    {
        Advertisement::find($id)->delete();

        return redirect()->back()->with(FlashMsg::item_new(' Advertisement Deleted Successfully'));
    }

    public function bulk_action(Request $request)
    {
        Advertisement::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => 'ok']);
    }

    private function mergeAiBulkTranslationsIfPresent(Advertisement $adv, Request $request): void
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
            $adv->setTranslation('title', $slug, purify_html((string) ($t['title'] ?? '')));
        }

        $adv->save();
    }
}
