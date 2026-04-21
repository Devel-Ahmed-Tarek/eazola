<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\AiCustomPageBlueprint;
use App\Models\Page;
use App\Models\PageBuilder;
use App\Services\Ai\CustomPageSchemaService;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PagesController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:page-list|page-edit|page-delete',['only' => ['all_pages','page_builder']]);
        $this->middleware('permission:page-create',['only' => ['create_page','store_new_page']]);
        $this->middleware('permission:page-edit',['only' => ['edit_page','update']]);
        $this->middleware('permission:page-delete',['only' => ['delete']]);
    }

    public function all_pages()
    {
        $all_pages = Page::orderBy('id','desc')->get();

        return view('landlord.admin.pages.index',compact('all_pages'));
    }

    public function create_page()
    {
        return view('landlord.admin.pages.create');
    }

    public function page_builder($id)
    {
        $page = Page::with('metainfo')->findOrfail($id);
        return view('landlord.admin.pages.page-builder',compact('page'));
    }

    public function edit_page($id)
    {
        $page = Page::with(['metainfo', 'aiCustomBlueprint'])->findOrfail($id);
        return view('landlord.admin.pages.edit',compact('page'));
    }

    public function store_new_page(Request $request)
    {
        $this->validate($request, [
            'status' => 'required|integer',
            'visibility' => 'required|integer',
            'lang' => 'required|string',
            'title' => 'required|string',
            'page_content' => 'nullable|string',
            'navbar_variant' => 'nullable|string',
            'footer_variant' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_image' => 'nullable|integer',
            'tw_image' => 'nullable|integer',
            'fb_image' => 'nullable|integer',
            'meta_tw_title' => 'nullable|string',
            'meta_tw_description' => 'nullable|string',
            'meta_fb_title' => 'nullable|string',
            'meta_fb_description' => 'nullable|string',
            'ai_custom_mode' => 'nullable|string|in:structured,raw_html',
            'ai_custom_schema_json' => 'nullable|string',
            'ai_custom_bindings_json' => 'nullable|string',
            'ai_custom_required_routes_json' => 'nullable|string',
            'ai_custom_sanitized_html' => 'nullable|string',
        ]);

        if(tenant()) {
            $current_package = tenant()->user()->first()->payment_log()->first()->package ?? [];
            $pages_count = Page::count();
            $permission_page = $current_package->page_permission_feature;
            if(!empty($permission_page) && $pages_count >= $permission_page){
                return response()->danger(ResponseMessage::delete(sprintf('You can not create page above %d in this package',$permission_page)));
            }
        }

        $page_data = new Page();

        $slug = !empty($request->slug) ? $request->slug : Str::slug($request->title);
        $created_slug = create_slug($slug,'Page');
        $page_data->slug = $created_slug;

        $page_data->setTranslation('title',$request->lang, SanitizeInput::esc_html($request->title));
        $page_data->setTranslation('page_content',$request->lang, $request->page_content);
        $page_data->visibility = $request->visibility;
        $page_data->status = $request->status;

        if(tenant()){
            $page_data->navbar_variant = $request->navbar_variant;
            $page_data->footer_variant = $request->footer_variant;
        }



        $page_data->page_builder = is_null( $request->page_builder) ? 0 : 1;
        $page_data->breadcrumb = is_null( $request->breadcrumb) ? 0 : 1;
        // per-page header/footer visibility (default ON)
        $page_data->show_header = is_null($request->show_header) ? 0 : 1;
        $page_data->show_footer = is_null($request->show_footer) ? 0 : 1;
        $page_data->show_social_header = is_null($request->show_social_header) ? 0 : 1;

        $Metas = [
            'title' => [$request->lang => SanitizeInput::esc_html($request->meta_title)],
            'description' => [$request->lang => SanitizeInput::esc_html($request->meta_description)],
            'image' => $request->meta_image,
            //twitter
            'tw_image' => $request->tw_image,
            'tw_title' => SanitizeInput::esc_html($request->meta_tw_title),
            'tw_description' => SanitizeInput::esc_html($request->meta_tw_description),
            //facebook
            'fb_image' => $request->fb_image,
            'fb_title' =>  SanitizeInput::esc_html($request->meta_fb_title),
            'fb_description' =>  SanitizeInput::esc_html($request->meta_fb_description),
        ];

        $page_data->save();
        $page_data->metainfo()->create($Metas);
        $this->syncAiCustomBlueprint($page_data, $request);

        return response()->success(ResponseMessage::SettingsSaved());
    }



    public function update(Request $request)
    {
        $this->validate($request, [
            'status' => 'required|integer',
            'visibility' => 'required|integer',
            'lang' => 'required|string',
            'title' => 'required|string',
            'page_content' => 'nullable|string',
            'navbar_variant' => 'nullable|string',
            'footer_variant' => 'nullable|string',
            'slug' => ['required', 'string', Rule::unique(Page::class, 'slug')->ignore($request->id)],
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_image' => 'nullable|integer',
            'tw_image' => 'nullable|integer',
            'fb_image' => 'nullable|integer',
            'meta_tw_title' => 'nullable|string',
            'meta_tw_description' => 'nullable|string',
            'meta_fb_title' => 'nullable|string',
            'meta_fb_description' => 'nullable|string',
            'ai_custom_mode' => 'nullable|string|in:structured,raw_html',
            'ai_custom_schema_json' => 'nullable|string',
            'ai_custom_bindings_json' => 'nullable|string',
            'ai_custom_required_routes_json' => 'nullable|string',
            'ai_custom_sanitized_html' => 'nullable|string',
        ]);

        $page_data = Page::find($request->id);

        $slug = !empty($request->slug) ? $request->slug : Str::slug($request->title);
        $created_slug = create_slug($slug, 'Page');
        $page_data->slug = $page_data->slug == $request->slug ? $page_data->slug : $created_slug;

        $page_data->setTranslation('title', $request->lang, purify_html($request->title));
        $page_data->setTranslation('page_content', $request->lang, $request->page_content);
        $page_data->visibility = $request->visibility;
        $page_data->status = $request->status;

        if (tenant()) {
            $page_data->navbar_variant = $request->navbar_variant;
            $page_data->footer_variant = $request->footer_variant;
        }

        $page_data->page_builder = is_null($request->page_builder) ? 0 : 1;
        $page_data->breadcrumb = is_null($request->breadcrumb) ? 0 : 1;
        // per-page header/footer visibility (default ON)
        $page_data->show_header = is_null($request->show_header) ? 0 : 1;
        $page_data->show_footer = is_null($request->show_footer) ? 0 : 1;
        $page_data->show_social_header = is_null($request->show_social_header) ? 0 : 1;
        $page_data->save();


        $metaData = [
            'title' => [$request->lang => SanitizeInput::esc_html($request->meta_title)],
            'description' => [$request->lang => SanitizeInput::esc_html($request->meta_description)],
            'image' => $request->meta_image,
            //twitter
            'tw_image' => $request->tw_image,
            'tw_title' => SanitizeInput::esc_html($request->meta_tw_title),
            'tw_description' => SanitizeInput::esc_html($request->meta_tw_description),
            //facebook
            'fb_image' => $request->fb_image,
            'fb_title' => SanitizeInput::esc_html($request->meta_fb_title),
            'fb_description' => SanitizeInput::esc_html($request->meta_fb_description),
        ];

        if (is_null($page_data->metainfo()->first())){
            $page_data->metainfo()->create($metaData);
        }else{
            $page_data->metainfo()->update($metaData);
        }

        $this->syncAiCustomBlueprint($page_data, $request);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    private function syncAiCustomBlueprint(Page $page, Request $request): void
    {
        $schema = $this->decodeJsonInput((string) $request->input('ai_custom_schema_json', ''));
        $bindings = $this->decodeJsonInput((string) $request->input('ai_custom_bindings_json', ''));
        $requiredRoutes = $this->decodeJsonInput((string) $request->input('ai_custom_required_routes_json', ''));
        $mode = $request->input('ai_custom_mode');
        $sanitizedHtml = $request->input('ai_custom_sanitized_html');

        $hasAny = !empty($schema) || !empty($bindings) || !empty($requiredRoutes) || !empty($sanitizedHtml);
        if (!$hasAny) {
            return;
        }

        $schemaService = app(CustomPageSchemaService::class);
        $normalizedSchema = is_array($schema) ? $schemaService->normalizeSchema($schema) : [];
        $entityName = (string) ($normalizedSchema['entity_name'] ?? data_get($schema, 'entity_name', 'custom_page_record'));

        AiCustomPageBlueprint::updateOrCreate(
            ['page_id' => $page->id],
            [
                'mode' => in_array($mode, ['structured', 'raw_html'], true) ? $mode : 'structured',
                'entity_name' => $entityName,
                'schema_json' => $normalizedSchema ?: null,
                'data_bindings' => is_array($bindings) ? $bindings : null,
                'required_routes' => is_array($requiredRoutes) ? $requiredRoutes : null,
                'sanitized_html' => is_string($sanitizedHtml) ? $schemaService->sanitizeRawHtml($sanitizedHtml) : null,
                'ai_prompt' => null,
            ]
        );
    }

    /**
     * @return array<mixed>|null
     */
    private function decodeJsonInput(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function delete($id){
        $page = Page::find($id);
        $page->metainfo()->delete();
        $page->delete();
        return response()->danger(ResponseMessage::delete());
    }

    public function download($id)
    {
        $page = Page::findorFail($id);

        if ($page->page_builder)
        {
            $page_contents = PageBuilder::where('addon_page_id', $page->id)->orderBy('id', 'ASC')->get()->toJson();
        } else {
            $array = [
                [
                    'text' => $page->page_content,
                    'addon_page_type' => 'simple_page'
                ]
            ];
            $page_contents = json_encode($array);
        }

        $fileName = $page->slug. '-layout.json';

        header('Content-Disposition: attachment; filename='.$fileName.'');
        header('Content-Type: application/json');
        echo $page_contents;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'page_layout' => 'required|mimes:json',
            'page_id' => 'required'
        ]);


        DB::beginTransaction();
        try {
            $file_contents = json_decode(file_get_contents($request->file('page_layout')));


            $contentArr = [];
            if (current($file_contents)->addon_page_type == 'dynamic_page')
            {
                foreach ($file_contents as $key => $content)
                {
                    unset($content->id);
                    $content->addon_page_id = (int)trim($request->page_id);
                    $content->created_at = now();
                    $content->updated_at = now();

                    foreach ($content as $key2 => $con)
                    {
                        $contentArr[$key][$key2] = $con;
                    }
                }

                Page::findOrFail($request->page_id)->update(['page_builder' => 1]);

                PageBuilder::where('addon_page_id', $request->page_id)->delete();
                PageBuilder::insert($contentArr);
            } else {
                Page::findOrFail($request->page_id)->update([
                    'page_builder' => 0,
                    'page_content' => current($file_contents)->text
                ]);
            }

            DB::commit();
            $type = 'success';
            $message = 'Page layout uploaded successfully.';
        } catch (\Exception $exception) {
            DB::rollBack();
            $type = 'danger';
            $message = 'Please upload correct format of file';
        }

        return back()->with([
            'type' => $type,
            'msg' => $message
        ]);
    }

}
