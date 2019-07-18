
developing   开发中....

多轮对话机器人框架 commune/chatbot 项目在 laravel 中搭建的 studio.


查看 Demo 请搜索微信公众号 CommuneChatbot

![wechat-qrcode](/docs/img/commune-qrcode.bmp)

交流qq群: 907985715


### 确认依赖

- swoole 4.x
- mysql
- redis

### 安装

下载源码 (没有发布版本)

    git clone https://github.com/thirdgerb/chatbot-studio.git

安装依赖

    composer install

检查 .env 文件

- 检查数据库配置
- 检查redis配置

.env 文件需要检查本项目的参数:


    // 是否debug模式
    COMMUNE_DEBUG=true
    // 是否开启 nlu. 默认关闭
    COMMUNE_NLU=false
    // 超级管理员id
    COMMUNE_SUPERVISORS=testUserId
    // rasa 服务的端口
    RASA_SERVER=localhost:5005
    RASA_JWT=null

    // 微信服务配置.
    WECHAT_SERVER_IP=127.0.0.1
    WECHAT_SERVER_PORT=80
    WECHAT_APP_ID=your-app-id
    WECHAT_SECRET=your-app-secret
    WECHAT_TOKEN=your-token
    WECHAT_AES_KEY=

初始化数据库

    php ./artisan migrate

### 运行

目前可以运行的指令:

    // 运行命令行
    php ./artisan commune:tinker

    // 运行tcp服务, 可以用 telnet 连接对话机器人
    php ./artisan commune:tcp

    // 运行 微信公众号的服务 (测试中)
    php ./artisan commune:wechat-server

## 使用rasa

demo 目前使用 rasa 做 nlu 服务

```root/commune/rasa``` 目录提供了 rasa 的代码. 不过没有 models 和includes, 需要自己复制.

训练:

    cd root/commune/rasa
    sh train.sh

运行web服务:

    cd root/commune/rasa
    rasa run --enable-api -m models/model-file-name
