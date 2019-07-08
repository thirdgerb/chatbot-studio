<?php


return [
    'hello' => [
        'world'  => '你好,世界',
    ],

    'system' => [
        'platformNotAvailable' => '系统不可用',
        'chatIsTooBusy' => '输入太频繁, 请稍后再试',
        'systemError' => '系统发生错误, 惭愧...',
    ],

    'dialog' => [
        'farewell' => '再见, 欢迎下次再来!',
        'missMatched' => '不好意思...没明白啥意思...',
    ],

    'command' => [
        'notExists' => '命令 %name% 不存在',
        'invalidArgument' => '参数 %name% 不正确',
        'notValid' => '%name% 不是合法的命令',
        'available' => "可用的命令: \n%available%",
        'contextNotExists' => 'context %contextName% 未注册',
        'navigateToContext' => '导航到 context %contextName%',
    ],

    'ask' => [
        'default' => '请输入 %name% (%default%)',
        'needs' => '您可能需要:',
        'needMore' => '您还有别的需要吗?',
    ],

    'errors' => [
        'badAnswer' => '您输入的信息不正确, 请重新输入',
        'mustBeSupervisor' => '只有管理员允许访问当前语境',
    ],

    'messageTypeNames' => [
        \Commune\Chatbot\App\Messages\Text::class => '文字',
    ]

];
