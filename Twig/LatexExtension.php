<?php 
namespace vaterlangen\LatexBundle\Twig;

class LatexExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('latex', array($this, 'escapeLatex')),
        );
    }

    /**
     * Escape LATEX specific characters
	 *
     * @param string $text
     */
    public function escapeLatex($text)
    {
    	/* chars to escape in latex */
    	$chars = array('%','#','&','~');
    	
    	foreach ($chars as $c)
    	{
        	$text = str_ireplace($c, '\\'.$c, $text);
    	}
    	
    	return $text;
    }

    public function getName()
    {
        return 'vaterlangen_latex_filter';
    }
}