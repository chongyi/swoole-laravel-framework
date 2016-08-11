# Laravel Framework On Swoole

构建于 Swoole 扩展上的 Laravel 框架

## Features

- 基于 [swoole](https://github.com/matyhtf/swoole "Swoole - PHP的异步、并行、高性能网络通信引擎"), 快速拥有 swoole 强大的功能特性
- 依旧不改动优雅框架设计的思路, 并且拥有更为强劲的性能和更多的可能
- 更低的迁移难度, 使得几乎不需要过多的改动就可以快速的集成至已有的项目
- 更多特性还会在基于 Swoole 的基础上不断完善

## Requirement

1. 由于是基于 Laravel 5.1 开发, 因此要求 PHP >= 5.5.9
2. 如上, 要求 Laravel Framework >= 5.1
3. **Swoole 扩展**

> Swoole 扩展目前不支持 Windows, 将来也不太可能支持。不过只要部署目标机器是 *nix 系统即可。 对于 Windows 下开发的人群, 
可利用 Vagrant +  Laravel 框架自带的 Homestead Box, 就可以快速配置出开发环境, 需要注意, 默认 Homestead 未安装 swoole
扩展, 可自行在虚拟机中安装配置, 并重新打包分发至团队。

## Installation && Configuration

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

## 

## License

[MIT](http://opensource.org/licenses/MIT "MIT License")