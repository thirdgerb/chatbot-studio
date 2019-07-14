<?php

use Commune\Chatbot\OOHost\Session\Scope;
use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App;
use Commune\Studio;

return [
    // debug 模式会记录更多的日志.
    'debug' => env('COMMUNE_DEBUG', true),

    'slots' => [
        'self' => [
            'name' => 'cc',
            'project' => 'commune/chatbot',
            'fullname' => 'commune/chatbot demo',
            'author' => 'thirdgerb',
            'email' => 'thirdgerb@gmail.com',
            'desc' => '多轮对话机器人开发框架',
        ]

    ],

    // 在这里可以预先绑定一些用 Option 类封装的配置.
    // 会将该配置预绑定到reactor容器上, 作为单例.
    // 有三种绑定方式:
    // 1. 只写类名, 默认使用 stub 里的配置.
    // 2. 类名 => 数组,  会用数组内的值覆盖 stub 的相关参数.
    // 3. 类名 => 子类名, 会用子类的实例来绑定父类类名.
    'configBindings' => [
        App\Platform\ConsoleConfig::class => [
            'allowIPs' => [
                '127.0.0.1',
            ]
        ],
    ],

    // 预加载的组件. 使用方法类似 configBindings
    // 但component 不仅会预加载配置, 而且还能注册各种组件, 进行初始化等.
    'components' => [
        // 加载自己默认的context, intent, memory 类
        Studio\StudioLoader::class,
        // 加载 demo 里的contexts和intents.
        Studio\Components\DemoComponent::class,
        // 管理 nlu example 的组件, /redirect nlu.examples.manager 可以直达.
        App\Components\NLUExamplesComponent::class => [
            // 存储例句的json文件
            'repository' => base_path('commune/data/nlu/examples.json'),
        ],
        // 使用文件来加载意图的组件.
        App\Components\SimpleFileChatComponent::class => [
            'groups' => [
                [
                    'id' => 'demo',
                    'resourcePath' => base_path('commune/data/sfi/demo'),
                    'intentAlias' => [],
                    'defaultSuggestions' => [
                        '返回' => [ Redirector::class, 'backward'],
                        '结束' => [ Redirector::class, 'fulfill'],
                    ],
                    'question' => '您可能需要:'
                ],
            ]
        ],
        // rasa 组件配置. 了解详情请查看 rasa.com
        App\Components\RasaComponent::class => [
            'server' => env('RASA_SERVER', 'localhost:5005'),
            'jwt' => env('RASA_JWT', ''),
            'pipe' => App\Components\Rasa\RasaNLUPipeImpl::class,
            // 计算意图命中的阈值. 低于阈值的不列入可能意图.
            'threshold' => 70,
            'output' => base_path('commune/rasa/data/nlu.md'),
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
        // 系统自带的 context 的loader. 可选. 主要提供常见的打招呼之类的意图.
        App\Components\PredefinedIntComponent::class,
        // 简单闲聊的组件. 可以在这里编辑闲聊.
        App\Components\SimpleChatComponent::class => [
            'default' => 'demo',
            'resources' => [
                [
                    'id' => 'demo',
                    'resource' => base_path('commune/data/chats/demo.yml')
                ],
                [
                    'id' => 'example',
                    'resource' => base_path('commune/data/chats/example.yml')
                ],

            ],
        ],
        // 管理消息意图识别记录的组件.
        Studio\Components\IntentLogComponent::class,
    ],
    // 预定义的系统服务, 在这里可更改 service provider
    'baseServices' => \Commune\Chatbot\Config\Services\BaseServiceConfig::stub(),
    // 在reactor中注册的服务, 多个请求共享
    'reactorProviders' => [
        Studio\Providers\StudioServiceProvider::class,
    ],
    // 在conversation开始时才注册服务, 其单例在每个请求之间是隔离的.
    'conversationProviders' => [
        // 数据读写的组件, 用到了laravel DB 的redis 和 mysql
        \Commune\Chatbot\Laravel\Providers\LaravelDBServiceProvider::class,
        // 各种权限功能的管理.
        Studio\Providers\AbilitiesServiceProvider::class,
    ],
    'chatbotPipes' => [
        'onUserMessage' => [
            App\ChatPipe\MessengerPipe::class,
            App\ChatPipe\ChattingPipe::class,
            \Commune\Chatbot\OOHost\OOHostPipe::class,
        ],
    ],
    'translation' => [
        'loader' => 'php',
        'resourcesPath' => base_path('commune/data/lang/'),
        'defaultLocale' => 'zh',
        'cacheDir' => NULL,
    ],
    'logger' => [
        'name' => 'chatbot',
        'path' => storage_path('logs/chatbot.log'),
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
        // 默认的对话语境.
        'rootContextName' => Studio\Components\Demo\DemoHome::class,
        // 可回溯的对话断点数量.
        'maxBreakpointHistory' => 10,
        // 运行对话逻辑时, 语境变化的最大次数. 超过可能出现了重定向.
        'maxRedirectTimes' => 20,
        // session 过期时间.
        'sessionExpireSeconds' => 3600,
        // session 用到的数据缓存的时间.
        'sessionCacheSeconds' => 60,
        // session 中经历的管道.
        'sessionPipes' => array_merge([
                // 默认回复的消息.
                App\SessionPipe\DefaultReplyPipe::class,
                // 处理事件类消息的管道.
                Studio\SessionPipes\EventMsgHandler::class,
                // 用户可用的命令.
                Studio\SessionPipes\UserCommands::class,
                // 系统可用的命令.
                Studio\SessionPipes\Analyser::class,
                // 记录NLU中间件拿到的意图
                Studio\Components\IntentLog\IntentLogPipe::class,
                // 本组件, 可以使用 #intentName# 直接命中某个意图, 主要用于测试.
                App\SessionPipe\MarkedIntentPipe::class,
                // 优先级最高, 用于导航的意图中间件.
                App\SessionPipe\NavigationPipe::class,

            ],
            env('COMMUNE_NLU', '') === true
                ?  [
                    // 使用rasa 匹配意图的中间件.
                    App\Components\Rasa\RasaNLUPipe::class,
                ]
                : []
        ),
        // hearing 方法默认的fallback. 可选.
        'hearingFallback' => App\Components\SimpleChat\Callables\SimpleChatAction::class,
        // 这里的intent会对每一个请求进行强制的意图识别
        // 命中的话优先执行.
        'navigatorIntents' => [
        ],
        // session 预定义的记忆.
        'memories' => [
            [
                'name' => 'on_user',
                'desc' => 'user memories',
                'scopes' => [Scope::USER_ID],
                // entities 可以临时自定义.
                'entities' => []
            ],
        ],
    ],

];