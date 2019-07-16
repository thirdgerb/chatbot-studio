<?php

return [
    'server' => env('RASA_SERVER', 'localhost:5005'),
    'jwt' => env('RASA_JWT', ''),
    'pipe' => \Commune\Chatbot\App\Components\Rasa\RasaNLUPipeImpl::class,
    // 计算意图命中的阈值. 低于阈值的不列入可能意图.
    'threshold' => 70,
    'output' => base_path('commune/rasa/data/nlu.md'),
    'synonym' => [
    ],
    'lookup' => [
        [
            'name' => 'city',
            'list' => [
                '北京', '上海', '长沙', '广州', '西安', '洛阳',
                '暴风城', '奥格瑞玛', '雷霆崖', '铁炉堡',
                '达纳苏斯', '幽暗城',
            ]
        ],
        [
            'name' => 'date',
            'list' => [
                '明天',
                '后天',
                '大后天',
                '昨天',
                '前天',
            ],
        ],
        [
            'name' => 'fruit',
            'list' => \Commune\Studio\Components\Demo\Drink\Intents\WantDrinkInt::FRUITS,
        ],
    ],
    'regex' => [
        //RegexOption::stub(),
    ]
];
