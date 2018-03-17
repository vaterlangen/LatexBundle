<?php

namespace vaterlangen\LatexBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vaterlangen_latex','array');

        $rootNode
        	->addDefaultsIfNotSet()
        	->children()
        		->booleanNode('enabled')->defaultFalse()->end()
        		->scalarNode('temp')->defaultValue('/tmp/vaterlangen_latex')->end()
				->scalarNode('include')->defaultValue(NULL)->end()
        		->arrayNode('path')
        			->addDefaultsIfNotSet()
        			->children()
        				->scalarNode('latex')->defaultValue(NULL)->end()
        				->scalarNode('pdflatex')->defaultValue(NULL)->end()
        				->scalarNode('dvips')->defaultValue(NULL)->end()
        				->scalarNode('ps2pdf')->defaultValue(NULL)->end()
						->scalarNode('bash')->defaultValue(NULL)->end()
        			->end()
	        	->end()
	        ->end()
	 	;
        			/*->useAttributeAsKey('id')
        			->prototype('array')
        				->children()
			        		->scalarNode('server')->isRequired()->end()
			        		->scalarNode('ssl')->defaultValue(true)->end()
			        		->scalarNode('user')->isRequired()->end()
			        		->scalarNode('email')->defaultValue(NULL)->end()
			        		->scalarNode('password')->isRequired()->end()
			        		->scalarNode('resource')->isRequired()->end()
			        		->scalarNode('categories')->defaultValue(NULL)->end()
			        		/*->arrayNode('categories')
			        			->prototype('array')
			        				->children()
			        					
			        				->end()
			        			->end()
			        		->end()*
			        	->end()
			        ->end()*
			   	->end()
			->end()*/
        ;

        return $treeBuilder;
    }
}
