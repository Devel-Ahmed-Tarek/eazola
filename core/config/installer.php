<?php

return [
    'app_name' => 'Multisaas',
    'super_admin_role_id' => 1,
    'admin_model' => \App\Models\Admin::class,
    'admin_table' => 'admins',
    'multi_tenant' => true,
    'author' => 'byteseed',
    'product_key' => '2f4f7829a3bdcd3899dfc24ff09d738f26236c53',
    'php_version' => '8.1',
    'extensions' => ['BCMath', 'Ctype', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'cURL', 'fileinfo'],
    'website' => 'https://bytesed.com',
    'email' => 'support@bytesed.com',
    'env_example_path' => public_path('env-sample.txt'),
    'broadcast_driver' => 'log',
    'cache_driver' => 'file',
    'queue_connection' => 'sync',
    'mail_port' => '587',
    'mail_encryption' => 'tls',
    'model_has_roles' => true,
    'bundle_pack' => true,
    'bundle_pack_key' => 'd2d44938e3bd030f93b6ed53e4d2b74e0f84efac',
];
