<?php


namespace Commune\Studio\Components\Demo\Drink;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Callables\Actions\ToNext;
use Commune\Chatbot\App\Components\Predefined\Attitudes;
use Commune\Chatbot\App\Contexts\TaskDef;
use Commune\Chatbot\Blueprint\Message\Message;
use Commune\Chatbot\Blueprint\Message\VerboseMsg;
use Commune\Chatbot\OOHost\Context\Depending;
use Commune\Chatbot\OOHost\Context\Exiting;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\Demo\Drink\Intents;
use Commune\Studio\Components\Demo\Drink\Memories\OrderMem;
use Illuminate\Support\Arr;

/**
 * @property Intents\WantDrinkInt|null $order
 * @property bool $free
 * @property bool|null $ifPack
 * @property int $paid
 * @property int|null $shouldPay
 *
 *
 * 步骤设计:
 *   - welcome:欢迎语
 *   - sameAsLast: 检查历史订单
 *   - fruit: 确定口味
 *   - ifIce: 是否加冰
 *   - ifCup: 是碗装还是杯装
 *   - pay: 要求付钱
 *   - ifPack: 是否打包
 *   - deliver: 交货
 *
 *   - change: 重新确认订单
 */
class DrinkTask extends TaskDef
{
    const DESCRIPTION = '果汁小店测试';

    public function __construct(Intents\WantDrinkInt $order = null)
    {
        $paid = 0;
        $ifPack = null;
        $free = false;
        $shouldPay = null;
        $order = $order ?? new Intents\WantDrinkInt();
        parent::__construct(get_defined_vars());
    }

    public static function __depend(Depending $depending): void
    {
    }

    /**
     * start
     * @param Stage $stage
     * @return Navigator
     */
    public function __onStart(Stage $stage): Navigator
    {
        return $stage->buildTalk()
            ->goStage('welcome');
    }

    public function __hearing(Hearing $hearing) : void
    {
        $hearing
            ->is('b', [Redirector::class, 'cancel'])

            // 是不是点单, 重新点单
            ->isIntent(
                Intents\WantDrinkInt::class,
                function(Dialog $dialog, Intents\WantDrinkInt $order){
                    if (isset($this->order)) {
                        if (isset($order->fruit)) {
                            $this->order->fruit = $order->fruit;
                        }

                        if (isset($order->cup)) {
                            $this->order->cup = $order->cup;
                        }

                        if (isset($order->ice)) {
                            $this->order->ice = $order->ice;
                        }
                    }

                    return $dialog->goStage('fruit');
                }
            )

            ->isIntent(
                Intents\OrderFruitInt::class,
                function(Dialog $dialog, Intents\OrderFruitInt $intent){

                    if (isset($intent->fruit)) {
                        $this->order->fruit = $intent->fruit;
                    }

                    return $dialog->goStage('confirmFruit');
                }
            )

            // 随便. 给用户随便一下.
            ->isIntent(Intents\RandomInt::class, function(Dialog $dialog){
                if (!isset($this->order)) {
                    $this->order = new Intents\WantDrinkInt();
                }

                if (!isset($this->order->fruit)) {
                    $this->order->fruit = Arr::random(Intents\WantDrinkInt::FRUITS);
                }

                $this->order->ice = true;
                $this->order->cup = true;

                $dialog->say()->info("那我就替您选了");

                return $dialog->goStage('fruit');
            })

            // 打招呼
            ->isIntent(Attitudes\GreetInt::class, function(Dialog $dialog){

                $dialog->say()->info("您好, 亲爱的顾客, 希望我能让您满意.");
                return $dialog->repeat();

            })

            // 用户想走逑了. 这个也是negative, 所以应该在hearing end 之前.
            ->isIntent(Attitudes\DontInt::class, [Redirector::class, 'cancel'])

            // 订单错了
            ->isIntent(Attitudes\WrongInt::class, function(Dialog $dialog){
                return $dialog->goStage('recheck');

            })
            // 被用户夸了, 免个单吧
            ->isIntent(Attitudes\AwesomeInt::class, function(Dialog $dialog){

                // 免单过了
                if ($this->free) {
                    $dialog->say()->info("谢谢, 谢谢");
                    return $dialog->wait();
                }

                // 没付钱, 没免单
                if (!$this->paid) {
                    $this->free = true;
                    $dialog->say()->info('谢谢您, 干脆这一单给您算五折了');
                } else {
                    $dialog->say()->info('非常感谢');
                }

                return $dialog->wait();
            })

            // 用户谢谢了
            ->isIntent(Attitudes\ThanksInt::class, function(Dialog $dialog){

                $dialog->say()->info('这是我应该做的 ~~');
                return $dialog->wait();
            })

            // 匹配
            ->isInstanceOf(VerboseMsg::class, function(Dialog $dialog, VerboseMsg $msg){
                $text = $msg->getTrimmedText();
                [$fruit, $ifIce, $ifCup, $ifPack] = $this->fetchParams($text);

                $changed = false;

                if (!isset($this->order)) {
                    $this->order = new Intents\WantDrinkInt();
                }

                if (isset($fruit) && $fruit !== $this->order->fruit) {
                    $changed = true;
                    $this->order->fruit = $fruit;
                }

                if (isset($ifIce) && $ifIce !== $this->order->ice) {
                    $changed = true;
                    $this->order->ice = $ifIce;
                }

                if (isset($ifCup) && $ifIce !== $this->order->cup) {
                    $changed = true;
                    $this->order->cup =  $ifCup;
                }

                if (isset($ifPack) && $ifPack !== $this->ifPack) {
                    $changed = true;
                    $this->ifPack = $ifPack;
                }

                if ($changed) {
                    return $dialog->goStage('change');
                }

                return null;

            });

    }

    protected function fetchParams(string $text) : array
    {
        $fruit = null;
        $ifIce = null;
        $ifCup = null;
        $ifPack = null;

        $fruits = implode('|', Intents\WantDrinkInt::FRUITS);
        if (preg_match('/('.$fruits.')/', $text, $matches)) {
            $fruit = $matches[1];
        }

        // 冰
        foreach (['不加冰', '不要冰','别加冰', '常温'] as $noIceStr) {
            if (mb_strpos($text, $noIceStr) !== false) {
                $ifIce = false;
                break;
            }
        }
        if (!isset($ifIce) && mb_strpos($text, '冰') !== false) {
            $ifIce = true;
        }

        // 碗装杯装

        if (mb_strpos($text, '碗') !== false) {
            $ifCup = false;
        } elseif (mb_strpos($text, '杯') !== false) {
            $ifCup = true;
        }

        if (preg_match('/(不要打包|不用打包|不打包)/', $text)) {
            $ifPack = false;

        } elseif (preg_match('/(打包|带走|包起来)/', $text)) {
            $ifPack = true;
        }

        return [$fruit, $ifIce, $ifCup, $ifPack];
    }

    /**
     * @param Stage $stage
     * @return Navigator
     */
    public function __onWelcome(Stage $stage) : Navigator
    {
        $mem = OrderMem::from($this);

        $mem->times = $mem->times + 1;

        return $stage->buildTalk()
            ->withSlots([
                'times' => $mem->times
            ])
            ->info("欢迎来到果汁店, %user.name%! 这是您第%times%次光临")
            ->goStage('sameAsLast');
    }

    public function __onSameAsLast(Stage $stage) : Navigator
    {
        if (isset($this->order)) {
            return $stage->dialog->goStage('fruit');
        }

        $mem = OrderMem::from($this);

        if (isset($mem->last)) {
            $order = $mem->last->toOrderStr();
            return $stage->buildTalk()
                ->info("您上次点的是 $order; 还要一样的吗?")
                ->wait()
                ->hearing()
                ->isPositive(function(Dialog $dialog) use ($mem){
                    $this->order = $mem->last;
                    return $dialog->next();
                })
                ->isNegative(new ToNext('fruit'))
                ->end();
        }


        return $stage->buildTalk()->goStage('fruit');
    }

    public function __onConfirmFruit(Stage $stage) : Navigator
    {
        if (!isset($this->order->fruit)) {
            return $stage->dialog->goStage('fruit');
        }

        $fruit = $this->order->fruit;

        if (in_array($fruit, [Intents\WantDrinkInt::FRUITS])) {
            return $stage->buildTalk()
                ->info("确认您要的是 $fruit 吗? ")
                ->wait()
                ->hearing()
                ->isPositive(function(Dialog $dialog){
                    return $dialog->goStage('ifIce');
                })
                ->isNegative(function(Dialog $dialog){
                    unset($this->order->fruit);
                    return $dialog->goStage('fruit');
                })
                ->end();
        }

        return $stage->dialog->goStage('fruit');
    }

    public function __onFruit(Stage $stage) : Navigator
    {
        if (!isset($this->order)) {
            $this->order = new Intents\WantDrinkInt();
        }

        $avail = implode(',', Intents\WantDrinkInt::FRUITS);
        $info = "请问您想要什么水果的果汁, 我们这儿热卖的有香蕉, 苹果, 哈密瓜";

        if (isset($this->order->fruit)) {
            $fruit = $this->order->fruit;

            if (in_array($fruit, Intents\WantDrinkInt::FRUITS)){
                return $stage->dialog->goStage('ifIce');
            }

            $info = "对不起, 我们没有$fruit 口味的\n 我们现在只有 $avail.\n 请问您需要哪一种?";
        }

        return $stage->buildTalk()
            ->info($info)
            ->wait()
            ->hearing()
            ->fallback(function(Dialog $dialog) use ($avail) {

                $dialog->say()->info(
                    "sorry, 您要的我们可能没有, 我们现在只有 $avail. \n 请问您需要什么口味的?");

                return $dialog->wait();
            })
            ->end();
    }

    public function __onIfIce(Stage $stage) : Navigator
    {
        if (isset($this->order->ice)) {
            return $stage->dialog->goStage('ifCup');
        }

        return $stage->buildTalk()
            ->info('请问要加冰吗?')
            ->wait()
            ->hearing()
            ->isPositive(function(Dialog $dialog){

                $this->order->ice = true;
                return $dialog->goStage('ifCup');

            })
            ->isNegative(function(Dialog $dialog){

                $this->order->ice = false;
                return $dialog->goStage('ifCup');

            })
            ->end();
    }


    public function __onIfCup(Stage $stage) : Navigator
    {
        if (isset($this->order->cup)) {
            OrderMem::from($this)->last = $this->order;
            return $stage->dialog->goStage('pay');
        }

        $bei = function(Dialog $dialog){
            $this->order->cup = true;
            OrderMem::from($this)->last = $this->order;
            return $dialog->goStage('pay');
        };

        $wan = function(Dialog $dialog){
            $this->order->cup = false;
            OrderMem::from($this)->last = $this->order;
            return $dialog->goStage('pay');
        };

        return $stage->buildTalk()
            ->info('请问是杯装吗 ? (或者碗装) ')
            ->wait()
            ->hearing()
            ->isPositive($bei)
            ->isNegative($wan)
            ->hasKeywords(['杯'], $bei)
            ->hasKeywords(['碗'], $wan)
            ->defaultFallback()
            ->end(function(Dialog $dialog){

                $dialog->say()->info('杯装方便用吸管吸, 碗装可以用勺吃');
                return $dialog->repeat();
            });
    }

    public function __onPay(Stage $stage) : Navigator
    {
        if (!isset($this->shouldPay)) {
            $this->shouldPay = $this->free === true ? 7.5 : 15;
        }

        if ($this->shouldPay == 0) {
            return $stage->dialog->goStagePipes(['ifPack', 'deliver']);
        }

        return $stage->buildTalk()
            ->withSlots([
                'order' => $this->order->toOrderStr(),
                'pay' => $this->shouldPay
            ])
            ->info("您点了 %order%, 请付%pay%元 (因为是测试, 请用数字模拟付钱)")
            ->wait()
            ->hearing()
            ->expect(
                function(Message $msg) : bool {
                    if (!$msg instanceof VerboseMsg) {
                        return false;
                    }
                    $text = $msg->getTrimmedText();
                    return is_numeric($text);
                },
                function(Dialog $dialog, Message $msg) : Navigator{
                    $paid = floatval($msg->getTrimmedText());

                    if ($paid < 0) {
                        $dialog->say([
                            'pay' => $this->shouldPay
                        ])->warning("不能要我倒给你钱吧....");
                        return $dialog->repeat();
                    }

                    if ($paid == 0) {
                        $dialog->say([
                            'pay' => $this->shouldPay
                        ])->warning("虽说是测试, 霸王餐也不好吧.... ");
                        return $dialog->repeat();
                    }

                    if ($paid >= $this->shouldPay) {


                        $dialog->say([
                            'paid' => $paid,
                            'return' => $paid - $this->shouldPay
                        ])->info("您付了 %paid%元, 找您 %return%元");

                        $this->paid += $this->shouldPay;
                        $this->shouldPay = 0;

                        return $dialog->goStagePipes([
                            'ifPack',
                            'deliver',
                        ], true);
                    }

                    $this->paid += $paid;
                    $this->shouldPay = $this->shouldPay - $paid;

                    $dialog->say([
                        'paid' => $paid,
                        'pay' => $this->shouldPay
                    ])->info('您给了 %paid%元, 还需要%pay%元.');

                    return $dialog->wait();
                }
            )
            ->defaultFallback()
            ->end(function(Dialog $dialog){
                $dialog->say()->info('请输入数字来表示金额');
            });
    }

    public function __onIfPack(Stage $stage) : Navigator
    {
        if (isset($this->ifPack)) {
            return $stage->dialog->goStage('deliver');
        }
        $pack = function(Dialog $dialog){
            $this->ifPack = true;
            return $dialog->next();
        };


        return $stage->buildTalk()
            ->info("需要打包走吗?")
            ->wait()
            ->hearing()
            ->isIntent(Intents\RandomInt::class, $pack)
            ->isPositive($pack)
            ->isNegative(function(Dialog $dialog){
                $this->ifPack = false;
                return $dialog->next();
            })
            ->defaultFallback()
            ->end();
    }

    public function __onDeliver(Stage $stage) : Navigator
    {
        $str = $this->order->toOrderStr();
        $packed = $this->ifPack ? '打包好的' : '';

        return $stage->buildTalk()
            ->info("这是您$packed $str, 请拿好. \n 感谢您的惠顾, 欢迎您下次光临 ")
            ->fulfill();
    }

    public function __onChange(Stage $stage) : Navigator
    {
        $name = $stage->name;

        if (!in_array($name, ['pay', 'ifPack'])) {
            return $stage->buildTalk()
                ->info("收到")
                ->goStage('fruit');
        }

        return $stage->buildTalk()
            ->withSlots(['order' => $this->order->toOrderStr()])
            ->info('您把订单改为 %order%')
            ->next();
    }

    public function __exiting(Exiting $listener): void
    {
        $listener->onCancel(function(Dialog $dialog){
            $dialog->say()->info('再见, 期待您下次光临');

            return $dialog->cancel(true);
        });
    }


}