<?php


namespace Commune\Studio\Components\Demo\Guide;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\App\Messages\QA\Choice;
use Commune\Chatbot\App\Messages\QA\Confirmation;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class GuideScript extends ScriptDef
{
    use ScriptTrait;

    const DESCRIPTION = 'demo 使用指引';


    public function __onFinal(Stage $stage): Navigator
    {
        return $stage
            ->buildTalk()
            ->info("谢谢, 以上是使用指引的全部内容. 感谢您协助测试. ")
            ->fulfill();
    }

    public function __hearing(Hearing $hearing): void
    {
        $hearing->is('exit', [Redirector::class, 'cancel']);
    }

    public function getSlots(): array
    {
        return [];
    }


    public static function getScripts(): array
    {
        return [
            'use_empty' => <<<EOF
开始使用指引的内容. 输入"exit" 可以随时退出.

首先, 请尝试输入"," 或 "." 或 ";" 来表示一个空信息, 以继续.',
EOF
            ,
            'why_empty' => <<<EOF
谢谢. 多轮对话在许多场景里只需要回复一个空消息就可以继续. 但一些IM, 比如微信, 并不支持输入空消息, 所以用特殊字符代替.
EOF
            ,
            'is_testing' => <<<EOF
目前项目还在测试中, 由于作者精力有限, 没有仔细琢磨测试用例和文本.同样可能出现任何bug, 敬请谅解. 

感谢您的协助!
EOF
,
            'use_command' => <<<EOF
本项目支持类似 shell 的命令行操作 (参考了 symfony 的实现). 可输入 "#help" 查看可用命令.

常用导航类命令:

  #back          : 回到上一轮对话
  #quit          : 退出当前会话
  #cancel        : 退出当前语境
  #repeat        : 重复当前语境.
  #restart       : 重启当前语境
EOF
,
            'about_nlu' => <<<EOF
本项目加入了自然语言识别的功能单元(NLU). 使用了开源项目 Rasa 作为中间件.
一部分多轮对话已经加入了 NLU . 您可以在多轮对话的相关选项中查看测试用例.
EOF
,
            'about_nlu2' => <<<EOF
多轮对话要保证精度, 需要大量的用例和反复训练, 测试. 由于训练 NLU 的成本很高 (5,6个小时训练一次), 所以目前的自然语言识别效果很粗糙 (人工智障?), 请谅解.... 
EOF
            ,
            'choose_option' => <<<EOF
本项目大量用了 "选择" 的功能. 机器人会提示若干选项, 通常还有一个默认值.

这时输入选项前的数字序号 (有时是文字), 就能选中选项.

也可以输入 "." 这样表示空白的字符, 提示系统选择默认值. 
EOF
,
            'test_choose' => 'testChoose',
            'confirm' => <<<EOF
有时候机器人会请您确认某个信息. 会提示 [yes|no] (yes)

这是表示您可以输入 yes 或 no 表达您的选择. 其实也可以输入首字母, 比如 y 和 n , 同样能命中选项.

当然还可以输入 "." 表示同意默认值. 

EOF
,
            'test_confirm' => 'testConfirm',
            'after_confirm' => <<<EOF
此外确认环节还加入了自然语言识别, 有兴趣可以尝试一些表示肯定或否定的常用语, 看看机器人什么反应.
EOF
            ,
        ];
    }


    public function testChoose(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->askChoose(
                "请测试选择一个选项.",
                ['case 1', 'case 2' , 'case 3'],
                2
            )
            ->wait()
            ->hearing()
            ->isAnswer(function(Dialog $dialog, Choice $choice){

                $id = $choice->getChoice();
                $result = $choice->toResult();

                $dialog
                    ->say([
                        'id' => $id,
                        'result' => $result,
                    ])
                    ->info(' 选择了 %id%, 内容是"%result%".');

                return $dialog->goStage('askContinue');
            })
            ->end(function(Dialog $dialog){
                $dialog->say()->error("没有命中任何选项");
                return $dialog->repeat();
            });
    }

    public function testConfirm(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->askConfirm(
                "请测试是否判断. 苹果是水果吗? ",
                true,
                'yes',
                'no'
            )
            ->wait()
            ->hearing()
            ->isAnswer(function(Dialog $dialog, Confirmation $choice){

                $id = $choice->getChoice();
                $result = $id ? 'yes' : 'no';
                $dialog
                    ->say([
                        'result' => $result,
                    ])
                    ->info(' 选择了 %result% .');

                return $dialog->goStage('askContinue');
            })
            ->end(function(Dialog $dialog){
                $dialog->say()->error("没有命中任何选项");
                return $dialog->repeat();
            });
    }

    public function __onAskContinue(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info("输入任意内容继续:")
            ->wait()
            ->next();
    }


}