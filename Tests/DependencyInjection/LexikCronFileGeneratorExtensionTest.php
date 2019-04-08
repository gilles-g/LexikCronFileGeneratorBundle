<?php

namespace Lexik\Bundle\CronFileGeneratorBundle\Tests\DependencyInjection;

use Lexik\Bundle\CronFileGeneratorBundle\DependencyInjection\LexikCronFileGeneratorExtension;
use Lexik\Bundle\CronFileGeneratorBundle\LexikCronFileGeneratorBundle;
use Lexik\Bundle\CronFileGeneratorBundle\Tests\Stubs\Autowired;
use Lexik\Bundle\CronFileGeneratorBundle\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TemplatingPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class LexikCronFileGeneratorExtensionTest extends TestCase
{
    public function testLoadEmptyConfiguration()
    {
        $container = $this->createContainer([
            'framework' => [
                'secret' => 'testing',
                'templating' => ['engines' => ['twig']],
                ],
            'twig' => [
                'strict_variables' => true,
            ],
            'lexik_cron_file_generator' => [
                'env_available' => [
                    'staging', 'prod',
                ],
                'user' => [
                    'staging' => 'project_staging',
                    'prod' => 'project_prod',
                ],
                'php_version' => 'php7.3',
                'absolute_path' => [
                    'staging' => 'path/to/staging',
                    'prod' => 'path/to/prod',
                ],
                'output_path' => 'path/to/cron_file',
                'crons' => [
                    [
                        'name' => 'test',
                        'command' => 'app:test',
                        'env' => [
                            'staging' => '* * * * *',
                            'prod' => '* 5 * * *',
                        ],
                    ],
                ],
            ],
        ]);

        $container
            ->register('autowired', Autowired::class)
            ->setPublic(true)
            ->setAutowired(true);

        $this->compileContainer($container);
dd($container->get('templating'));
        $autowired = $container->get('autowired');
    }

    private function createContainer(array $configs = [])
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => __DIR__,
            'kernel.root_dir' => __DIR__,
            'kernel.project_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'kernel.environment'      => 'test',
            'kernel.debug' => false,
            'kernel.bundles_metadata' => [],
            'kernel.container_class'  => 'AutowiringTestContainer',
            'kernel.bundles' => [
                'FrameworkBundle' => FrameworkBundle::class,
                'TwigBundle' => TwigBundle::class,
                'LexikCronFileGeneratorBundle' => LexikCronFileGeneratorBundle::class,
            ],
        ]));

        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new TwigExtension());
        $container->registerExtension(new LexikCronFileGeneratorExtension());

        foreach ($configs as $extension => $config) {
            $container->loadFromExtension($extension, $config);
        }

        return $container;
    }

    private function compileContainer(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TemplatingPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();
    }
}