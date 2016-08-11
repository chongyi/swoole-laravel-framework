<?php
/**
 * SwooleServiceCommand.php
 *
 * Creator:    chongyi
 * Created at: 2016/08/11 02:06
 */

namespace Swoole\Laravel\Foundation\Console;

use Illuminate\Contracts\Http\Kernel;
use Swoole\Http\Server;
use Swoole\Laravel\Foundation\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SwooleServiceCommand extends Command
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('server:start')
            ->setDescription('Start the swoole server.')
            ->setHelp("You can use it to start the swoole http service.")
            ->addOption('host', 'H', InputOption::VALUE_REQUIRED, 'The http server host.', '0.0.0.0')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'The http server port.', 8001);
    }

    public function setApp(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');

        $server = new Server($host, $port);
        $kernel = $this->app->make(Kernel::class);

        $kernel->setSwooleServer($server);
        $kernel->start();
    }


}