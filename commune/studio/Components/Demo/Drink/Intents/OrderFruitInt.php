<?php


namespace Commune\Studio\Components\Demo\Drink\Intents;


use Commune\Chatbot\App\Intents\MessageIntent;

/**
 * @property string|null $fruit
 */
class OrderFruitInt extends MessageIntent
{
    const DESCRIPTION = '有没有想要的口味';

    // 命令名. 可以用命令的方式来匹配
    const SIGNATURE = 'order';
    // 用正则来匹配
    const REGEX = [];
    // 用关键字来匹配.
    const KEYWORDS = [];
    // 给NLU用的例句.
    const EXAMPLES = [
        '我要[西瓜](fruit)口味',
        '有没有[蜜桃](fruit)果汁',
        '来个[芒果](fruit)味的饮料',
        '来一杯[橘子](fruit)果汁',
        '我想要不加冰的[柠檬](fruit)汁',
        '有没有加冰的[梨](fruit)汁',
        '我想要[火龙果](fruit)饮料',
        '我要[桔子]饮料',
        '来个[蜜桃](fruit)',
        '你这儿有[香蕉](fruit)吗',
        '有没有[哈密瓜](fruit)的',
        '我想要[菠萝](fruit)的',
        '给我一个[蜜桃](fruit)的吧',
        '你都有些什么口味',
        '有哪些水果',
        '还有些什么',
        '还有哪些口味',
        '请问有些什么东西',
        '有没有饮料',
        '有没有果汁',
        '有没有什么喝的',
        '有没有[柠檬](fruit)汁',
        '有没有[苹果](fruit)',
        '有[哈密瓜](fruit)吗',
    ];


}