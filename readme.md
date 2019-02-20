# Lumen 快速开发工具包

目标：数据库设计好，整套 RESTful API 规范的接口就出来了

## 源码清单

```
.
├── composer.json
├── LICENSE
├── readme.md
└── src
    ├── BaseTransformer.php
    ├── ControllerHelper.php
    ├── Controller.php
    ├── dev-helpers.html
    ├── HelpersController.php # 脚手架控制器
    ├── helpers.php
    ├── LumenToolsServiceProvider.php
    └── Scaffold
        ├── ControllerCreator.php
        ├── MigrationCreator.php
        ├── ModelCreator.php
        ├── RouteCreator.php
        ├── stubs
        │   ├── controller.stub
        │   ├── create.stub
        │   ├── model.stub
        │   ├── route.stub
        │   └── transformer.stub
        └── TransformerCreator.php
```

## 依赖

- PHP >= 7.1
- Lumen >= 5.5
- tymon/jwt-auth 1.0.0-rc.3
- dingo/api >= 2.0

## 安装

```bash
composer require fxiao/lumen-tools
```

## 脚手架配置

`.env` 同时 设置 `APP_DEBUG=true` 和 `DEV_HELPERS=true` 有效，如：

```php
# dev
DEV_HELPERS=true
# App\Models\
DEV_HELPERS_MODELS_PATH=
# App\Http\Controllers\
DEV_HELPERS_CONTROLLER_PATH=App\Controllers\
# App\Transformers\
DEV_HELPERS_TRANSFORMER_PATH=
# routes\
DEV_HELPERS_ROUTE_PATH=
```

## 脚手架使用

URL：`/dev-helpers`

表名 为复数，生成的模型和控制器名称自动转换为 单数
