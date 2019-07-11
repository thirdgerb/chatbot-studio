<?php


namespace Commune\Studio\Components\Demo\Guide;


use Commune\Chatbot\App\Intents\RedirectorInt;

class GuideInt extends RedirectorInt
{
    const DESCRIPTION = '新手指引';

    const SIGNATURE = 'demo:guide';

    const EXAMPLES = [
        '新手指引',
        '使用说明',
        '新手教程',
        '新人必读',
    ];

    public function redirectTo()
    {
        return GuideScript::class;
    }


}