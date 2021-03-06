<?php

declare(strict_types=1);

/*
 * This file is part of Zenify
 * Copyright (c) 2012 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace Zenify\DoctrineFixtures\DI;

use Faker\Provider\Base;
use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\Fixtures\Parser\Methods\MethodInterface;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;


final class FixturesExtension extends CompilerExtension
{

	/**
	 * @var array[]
	 */
	private $defaults = [
		'locale' => 'cs_CZ',
		'seed' => 1
	];


	public function loadConfiguration()
	{
		$containerBuilder = $this->getContainerBuilder();
		$services = $this->loadFromFile(__DIR__ . '/services.neon');
		$this->compiler->parseServices($containerBuilder, $services);
	}


	public function beforeCompile()
	{
		$containerBuilder = $this->getContainerBuilder();
		$containerBuilder->prepareClassList();

		$this->loadFakerProvidersToAliceLoader();
		$this->loadParsersToAliceLoader();
	}


	private function loadFakerProvidersToAliceLoader()
	{
		$containerBuilder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$this->getDefinitionByType(Loader::class)->setArguments([
			$config['locale'],
			$containerBuilder->findByType(Base::class),
			$config['seed']
		]);
	}


	private function loadParsersToAliceLoader()
	{
		$containerBuilder = $this->getContainerBuilder();

		$aliceLoaderDefinition = $this->getDefinitionByType(Loader::class);
		foreach ($containerBuilder->findByType(MethodInterface::class) as $parserDefinition) {
			$aliceLoaderDefinition->addSetup('addParser', ['@' . $parserDefinition->getClass()]);
		}
	}


	private function getDefinitionByType(string $type) : ServiceDefinition
	{
		$containerBuilder = $this->getContainerBuilder();
		return $containerBuilder->getDefinition($containerBuilder->getByType($type));
	}

}
