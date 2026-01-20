<?php

namespace App\Enums;

enum CacheKeyEnums : string
{
    case ALL_USER_CACHE = 'all_user_cache';
    case ALL_TENANT_CACHE = 'all_tenant_cache';
    case TENANT_FRONTEND_HOMEPAGE = 'tenant_frontend_homepage';
    case ALL_LANGUAGES = 'all_languages';
    case USER_LANGUAGE_SLUG = 'user_lang_slug';
    case GLOBAL_VARIABLE_STATIC_OPTION = 'global_variable_static_options';
    case SITE_PRIMARY_MENU = 'site_primary_menu';

    case PAGE_BUILDER_WIDGET_CACHE = 'page_builder_widget';
    case ALL_AWS_S3_DEMO_IMAGES_FILES = 'all_demo_s3_Files';
}
