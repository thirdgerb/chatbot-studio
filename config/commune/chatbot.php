<?php

use Commune\Chatbot\OOHost\Session\Scope;

return [
    // debug 模式会记录更多的日志.
    'debug' => env('COMMUNE_DEBUG', true),

    // 在这里可以预先绑定一些用 Option 类封装的配置.
    // 会将该配置预绑定到reactor容器上, 作为单例.
    // 有三种绑定方式:
    // 1. 只写类名, 默认使用 stub 里的配置.
    // 2. 类名 => 数组,  会用数组内的值覆盖 stub 的相关参数.
    // 3. 类名 => 子类名, 会用子类的实例来绑定父类类名.
    'configBindings' => [
        \Commune\Chatbot\App\Platform\ConsoleConfig::class,
    ],

    // 预加载的组件. 使用方法类似 configBindings
    // 但component 不仅会预加载配置, 而且还能注册各种组件, 进行初始化等.
    'components' => [
        // 加载 demo 里的contexts和intents. 没啥用
        \Commune\Demo\App\DemoOption::class,
        // 管理 nlu example 的组件, /redirect nlu.examples.manager 可以直达.
        \Commune\Chatbot\App\Components\NLUExamplesComponent::class => [
            'repository' => storage_path('/chatbot/nlu/examples.json'),
        ],
        // 使用文件来加载意图的组件.
        \Commune\Chatbot\App\Components\SimpleFileIntentComponent::class => [
            'resourcePath' => storage_path('/chatbot/sfi/'),
        ],
        // rasa 组件配置. 了解详情请查看 rasa.com
        \Commune\Chatbot\App\Components\RasaComponent::class => [
            'server' => 'localhost:5005',
            'jwt' => '',
            'pipe' => \Commune\Chatbot\App\Components\Rasa\RasaNLUPipeImpl::class,
            'threshold' => 70,
            'output' => realpath(__DIR__ .'/../../commune/rasa/data/nlu.md'),
            'synonym' => [
                //SynonymOption::stub(),
            ],
            'lookup' => [
                //LookupOption::stub(),
            ],
            'regex' => [
                //RegexOption::stub(),
            ]
        ],
    ],
    'reactorProviders' => [
        \Commune\Studio\Providers\StudioServiceProvider::class,
    ],
    'conversationProviders' => [
        // laravel DB 组件的注册使用. 用到了laravel
        \Commune\Chatbot\Laravel\Providers\LaravelDBServiceProvider::class,
        // 各种权限功能的管理.
        \Commune\Studio\Providers\AbilitiesServiceProvider::class,
    ],
    'chatbotPipes' => [
        'onUserMessage' => [
            \Commune\Chatbot\App\ChatPipe\MessengerPipe::class,
            \Commune\Chatbot\App\ChatPipe\ChattingPipe::class,
            \Commune\Chatbot\OOHost\OOHostPipe::class,
        ],
    ],
    'translation' => [
        'loader' => 'php',
        'resourcesPath' => resource_path('/lang/chatbot'),
        'defaultLocale' => 'zh',
        'cacheDir' => NULL,
    ],
    'logger' => [
        'name' => 'chatbot',
        'path' => storage_path('/logs/chatbot.log'),
        'days' => 7,
        'level' => 'debug',
        'bubble' => true,
        'permission' => NULL,
        'locking' => false,
    ],
    'defaultMessages' => [
        'platformNotAvailable' => 'system.platformNotAvailable',
        'chatIsTooBusy' => 'system.chatIsTooBusy',
        'systemError' => 'system.systemError',
        'farewell' => 'dialog.farewell',
        'messageMissMatched' => 'dialog.missMatched',
    ],

    // 在对话系统中注册的事件机制.
    'eventRegister' =>[

    ],

    'host' => [
        'rootContextName' => \Commune\Demo\App\Contexts\Welcome::class,
        'maxBreakpointHistory' => 10,
        'maxRedirectTimes' => 20,
        'sessionExpireSeconds' => 3600,
        'sessionCacheSeconds' => 60,
        'sessionPipes' => [
            // 用户可用的命令.
            \Commune\Studio\SessionPipes\UserCommands::class,
            // 系统可用的命令.
            \Commune\Studio\SessionPipes\Analyser::class,
            // 本组件, 可以使用 #intentName# 直接命中某个意图, 主要用于测试.
            \Commune\Chatbot\App\SessionPipe\MarkedIntentPipe::class,
            // 优先级最高, 用于导航的意图中间件.
            \Commune\Chatbot\App\SessionPipe\NavigationPipe::class,
            // 使用rasa 匹配意图的中间件.
            \Commune\Chatbot\App\Components\Rasa\RasaNLUPipe::class,
        ],
        'navigatorIntents' => [
        ],
        'memories' => [
            [
                'name' => 'sandbox',
                'desc' => 'sandbox only used for test',
                'scopes' => [Scope::SESSION_ID],
                'entities' => [
                    'test'
                ]
            ],
        ],
    ],

];