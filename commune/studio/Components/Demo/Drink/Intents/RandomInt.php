<?php


namespace Commune\Studio\Components\Demo\Drink\Intents;


use Commune\Chatbot\App\Intents\MessageIntent;

class RandomInt extends MessageIntent
{
    const DESCRIPTION = 'should define description';


    // 命令名. 可以用命令的方式来匹配
    const SIGNATURE = 'random';

    // 用正则来匹配
    const REGEX = [];

    // 用关键字来匹配.
    const KEYWORDS = [ ['随便', '都行', '都可以']];

    // 给NLU用的例句.
    const EXAMPLES = [
        '随便吧',
        '都可以啊',
        '都可以',
        '随便你',
        '你说的算',
        '你来选吧',
        '随你的便',
        '随便',
        '你帮我选吧',
    ];



}