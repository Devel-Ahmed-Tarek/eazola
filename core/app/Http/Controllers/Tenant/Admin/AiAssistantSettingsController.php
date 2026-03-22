<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiSiteContextService;
use Illuminate\Http\Request;

class AiAssistantSettingsController extends Controller
{
    public function edit()
    {
        $reference = get_static_option(AiSiteContextService::optionKey(), '');

        return view('tenant.admin.ai-assistant-settings.index', [
            'reference' => old('ai_site_reference', $reference),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'ai_site_reference' => 'nullable|string|max:50000',
        ]);

        update_static_option(
            AiSiteContextService::optionKey(),
            (string) $request->input('ai_site_reference', '')
        );

        return redirect()->back()->with([
            'msg' => __('AI site reference saved successfully.'),
            'type' => 'success',
        ]);
    }
}
