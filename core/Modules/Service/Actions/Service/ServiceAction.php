<?php

namespace Modules\Service\Actions\Service;
use App\Facades\GlobalLanguage;
use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Blog;
use Modules\Service\Entities\Service;

class ServiceAction
{
    public $message;

    public function __construct($message = [])
    {
        $this->message = $message;
    }

    public function store_execute(Request $request) {

        try {
            \DB::beginTransaction();
            $service = new Service();
            $this->common_action($service, $request,'create');
            $service->save();
            $this->mergeAiBulkTranslationsIfPresent($service, $request);

            $this->message['msg'] = __('Service Created Successfully..!');
            $this->message['type'] = 'success';
            \DB::commit();

        }catch (\Exception $e){
            \DB::rollBack();
            $this->message['msg'] = __($e->getMessage());
            $this->message['type'] = 'danger';
        }

        return $this->message;
    }


    public function update_execute(Request $request ,$id)
    {
        try {
            $service =  Service::findOrFail($id);

            if (is_null($service->metainfo()->first())){
                $this->common_action($service, $request,'create');
            }else{
                $this->common_action($service, $request,'update');
            }
            $this->mergeAiBulkTranslationsIfPresent($service, $request);

            $this->message['msg'] = __('Service updated Successfully..!');
            $this->message['type'] = 'success';
            \DB::commit();

        }catch (\Exception $e){
            \DB::rollBack();
            $this->message['msg'] = __($e->getMessage());
            $this->message['type'] = 'danger';
        }

        return $this->message;
    }


    private function common_action(Service $service, Request $request ,$meta_action): void
    {
        $service->setTranslation('title', $request->lang, SanitizeInput::esc_html($request->title))
            ->setTranslation('description', $request->lang,$request->description);
        $service->slug = empty($request->slug) ? Str::slug($request->title) : Str::slug($request->slug);
        $service->category_id = $request->category_id;
        $service->image = $request->image;
        $service->meta_tag = $request->meta_tag;
        $service->meta_description = $request->meta_description;
        $service->status = $request->status;
        $service->save();

        $service->metainfo()->$meta_action([
            'title' => [$request->lang => SanitizeInput::esc_html($request->meta_title)],
            'description' => [$request->lang => SanitizeInput::esc_html($request->meta_description)],
            'image' => $request->meta_image,
            //twitter
            'tw_image' => $request->meta_tw_image ?? $request->tw_image,
            'tw_title' =>  SanitizeInput::esc_html($request->meta_tw_title),
            'tw_description' => SanitizeInput::esc_html($request->meta_tw_description),
            //facebook
            'fb_image' => $request->meta_fb_image ?? $request->fb_image,
            'fb_title' => SanitizeInput::esc_html($request->meta_fb_title),
            'fb_description' => SanitizeInput::esc_html($request->meta_fb_description),
        ]);


    }

    /**
     * @param array<string, array<string, mixed>> $bulk
     */
    public function applyAiBulkTranslationsArray(Service $service, array $bulk): void
    {
        foreach ($bulk as $slug => $t) {
            if (!is_string($slug) || !is_array($t)) {
                continue;
            }
            $service->setTranslation('title', $slug, SanitizeInput::esc_html((string) ($t['title'] ?? '')))
                ->setTranslation('description', $slug, (string) ($t['description'] ?? ''));
        }
        $service->save();

        $meta = $service->metainfo;
        if ($meta === null) {
            return;
        }

        foreach ($bulk as $slug => $t) {
            if (!is_string($slug) || !is_array($t)) {
                continue;
            }
            $meta->setTranslation('title', $slug, SanitizeInput::esc_html((string) ($t['meta_title'] ?? '')));
            $meta->setTranslation('description', $slug, SanitizeInput::esc_html((string) ($t['meta_description'] ?? '')));
        }

        $def = GlobalLanguage::default_slug();
        if (isset($bulk[$def]) && is_array($bulk[$def])) {
            $b = $bulk[$def];
            $meta->fb_title = SanitizeInput::esc_html((string) ($b['meta_fb_title'] ?? ''));
            $meta->fb_description = SanitizeInput::esc_html((string) ($b['meta_fb_description'] ?? ''));
            $meta->tw_title = SanitizeInput::esc_html((string) ($b['meta_tw_title'] ?? ''));
            $meta->tw_description = SanitizeInput::esc_html((string) ($b['meta_tw_description'] ?? ''));
        }
        $meta->save();
    }

    private function mergeAiBulkTranslationsIfPresent(Service $service, Request $request): void
    {
        $raw = $request->input('ai_bulk_translations_json');
        if (!is_string($raw) || trim($raw) === '') {
            return;
        }

        $bulk = json_decode($raw, true);
        if (!is_array($bulk)) {
            return;
        }

        $this->applyAiBulkTranslationsArray($service, $bulk);
    }


}
