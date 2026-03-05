<?php

return [
    'api_key' => env('CONTRAGENT_API_KEY', 'fb6b191e709a2e474a5619855545ceeb'),
    'report_url' => env('CONTRAGENT_REPORT_URL', 'https://api.checklic.ru/api/report'),
    'result_url' => env('CONTRAGENT_RESULT_URL', 'https://api.checklic.ru/api/result'),
    
    'targets' => [
        'fssp-org',
        'cad-org',
        'bankrot-org',
        'inagent-org',
        'payment-block-org',
        'disqualification-org',
        'tax-regime-org',
        'employee-count-org',
        'sme-reg-org',
        'sme-support-org',
        'egrul-data-org',
        'leasing-org',
        'static-code-org',
        'blacklist-org',
    ],
    
    // Настройки
    'polling_interval' => 10,      // секунды между проверками
    'max_polling_attempts' => 60,  // максимум попыток  
];