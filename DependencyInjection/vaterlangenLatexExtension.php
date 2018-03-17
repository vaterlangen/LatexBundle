<?php

namespace vaterlangen\LatexBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class vaterlangenLatexExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        /* load service container only if enabled */
        if ($config['enabled'] === true)
        {
        	$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        	$loader->load('services.yml');
			$this->checkEnvironment($config);
        }
        
        /* add parameters to container */
        $container->setParameter($this->getAlias().'.temp', $config['temp']);
        $container->setParameter($this->getAlias().'.path', $config['path']);
 		$container->setParameter($this->getAlias().'.include', $config['include']);
    }
    
    /**
     * Check if all required modules are installed
     */
    private function checkEnvironment(array &$config)
    {
    	/* check if server is using Linux*/
    	if (strtoupper(substr(PHP_OS, 0, 5)) !== 'LINUX')
    	{
    		throw new InvalidConfigurationException("The server seems to be running the unsupported operating system '".PHP_OS."'.");
    	}
    	
    	/* search for installed modules */
    	foreach ($config['path'] as $key => &$value)
    	{
    		$nix = array();
    		if ($value === NULL)
    		{
    			
    			/* search for modules */
    			$value = exec("which $key",$nix,$erg);
    			
    			if ($erg !== 0)
    			{
    				throw new InvalidConfigurationException("Could not determine path to '$key'. Please add path to config.");
    			}
    			
    		}else{
    			exec("test -f $value",$nix,$erg);
    			 
    			if ($erg !== 0)
    			{
    				throw new InvalidConfigurationException("The module '$key' could not found at '$value'.");
    			}
    		}
    	}
    }
}
