description: "特性: 高度组件化"
suggestions:
    - ./
examples:
---

组件化是 commune/chatbot 最核心的工程 feature 之一. 大多数环节都考虑了组件化, 表现在:

- 流程组件化 : 项目拆分成多个环节, 在管道中运行, 可以随意增减管道.

- 依赖注入 : 基于 IoC 容器开发, 主要的组件都是面向接口设计, 可依赖注入, 可替换.

- 对话组件化 : 本系统的对话内容, 都是组件化设计的, 理论上可以在各个对话机器人复用. 比如本 demo, 天气预报单元就是作为组件引入的.

- 功能组件化 : 各种类型的对话, 可以开发各种功能模块, 组件化引入. 例如您现在看到的内容, 是基于 "simpleFileChatComponent"组件开发的,

- 包管理 : 本项目按composer 的思路做了 package 拆分. 理想情况下所有组件, 对话单元, 都可以通过composer 加载到一个系统.
