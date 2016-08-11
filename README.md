# Laravel Framework On Swoole

构建于 Swoole 扩展上的 Laravel 框架

## Features

- 基于 [woole](https://github.com/matyhtf/swoole "Swoole - PHP的异步、并行、高性能网络通信引擎") 提供的强大的异步、并行功能, 使得优雅的 Laravel 更加强劲
- 最小的改动, 使得几乎不需要过多的改动就可以快速的集成至已有的项目
- 更多特性还会在基于 Swoole 的基础上不断完善

## Installation && Configure

首先通过 Composer 安装:

`composer require chongyi/swoole-laravel-framework`

向 `config/app.php` 的 `providers` 中添加

`Swoole\Laravel\Foundation\Providers\SwooleServiceProvider::class`

然后执行以下命令, 用于生成必要的配置文件:

`php artisan vendor:publish`

您有必要拷贝一份 `Illuminate\Contracts\Http\Kernel` 的实现, 默认是在 `app/Http/Kernel.php`, 可以将该文件
拷贝一份并重命名(包括其中的类名, 比如 `SwooleKernel`), 并将其中的 `App\Http\Kernel` 的继承对象改为 `Swoole\Laravel\Foundation\Http\Kernel`,
最后, 编辑 `bootstrap/swoole-app.php`, 将 `Kernel` 的单例注册对象修改为您所重命名的那个, 例如:

```php
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\SwooleKernel::class
);
```

至此, 安装和配置完毕。

## Usage

执行以下命令即可启动服务:

`vendor/bin/swoole --host=<HOST> --port=<PORT>`

## License

[MIT](http://opensource.org/licenses/MIT "MIT License")