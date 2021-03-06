<?php
/**
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Renderer;

use Cecil\Builder;
use Cecil\Renderer\Twig\Extension as TwigExtension;

/**
 * Class Twig.
 */
class Twig implements RendererInterface
{
    /** @var \Twig\Environment */
    protected $twig;
    /** @var string */
    protected $templatesDir;
    /** @var \Twig\Profiler\Profile */
    public $profile;

    /**
     * {@inheritdoc}
     */
    public function __construct(Builder $builder, $templatesPath)
    {
        // load layouts
        $loader = new \Twig\Loader\FilesystemLoader($templatesPath);
        // Twig
        $this->twig = new \Twig\Environment($loader, [
            'debug'            => getenv('CECIL_DEBUG') == 'true' ? true : false,
            'strict_variables' => true,
            'autoescape'       => false,
            'cache'            => false,
            'auto_reload'      => true,
        ]);
        // set date format & timezone
        $this->twig->getExtension(\Twig\Extension\CoreExtension::class)
            ->setDateFormat($builder->getConfig()->get('date.format'));
        $this->twig->getExtension(\Twig\Extension\CoreExtension::class)
            ->setTimezone($builder->getConfig()->get('date.timezone'));
        // adds extensions
        $this->twig->addExtension(new TwigExtension($builder));
        $this->twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        // internationalisation
        if (extension_loaded('intl')) {
            $this->twig->addExtension(new \Twig_Extensions_Extension_Intl());
        }
        if (extension_loaded('gettext')) {
            $this->twig->addExtension(new \Twig_Extensions_Extension_I18n());
        }
        if (getenv('CECIL_DEBUG') == 'true') {
            // dump()
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
            // profiler
            $this->profile = new \Twig\Profiler\Profile();
            $this->twig->addExtension(new \Twig\Extension\ProfilerExtension($this->profile));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addGlobal(string $name, $value): void
    {
        $this->twig->addGlobal($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $template, array $variables): string
    {
        return $this->twig->render($template, $variables);
    }
}
