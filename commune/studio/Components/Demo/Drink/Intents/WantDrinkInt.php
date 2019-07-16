<?php


namespace Commune\Studio\Components\Demo\Drink\Intents;


use Commune\Chatbot\App\Intents\NavigateIntent;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;

/**
 * @property string $fruit
 * @property bool $ice
 * @property bool $cup
 */
class WantDrinkInt extends NavigateIntent
{
    const SIGNATURE = 'drink';

    const EXAMPLES = [
        '我要点饮料',
        '来点喝的吧',
        '来点果汁',
        '我想要果汁',
        '来点饮料',
        '我要点个饮料',
        '有什么好吃的吗',
    ];

    const FRUITS = [
        '苹果', '香蕉', '哈密瓜', '梨', '橘子', '桔子', '柠檬', '火龙果',
        '西瓜', '蜜桃', '芒果', '菠萝'
    ];

    public function navigate(Dialog $dialog): ? Navigator
    {
        return $dialog->redirect->sleepTo();
    }


    public function toOrderStr() : string
    {
        $fruit = $this->fruit;
        $ice = $this->ice ?? false;
        $cup = $this->cup ?? false;

        if (empty($fruit)) {
            return '';
        }

        $ifIce = $ice ? '加冰' : '不加冰';
        $ifCup = $cup ? '杯装' : '碗装';

        return "$fruit 口味的果汁, $ifIce, $ifCup";
    }

}