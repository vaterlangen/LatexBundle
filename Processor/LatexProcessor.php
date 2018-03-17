<?php

namespace vaterlangen\LatexBundle\Processor;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class LatexProcessor
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;
	
	protected $usedFolders = array();
	
	
	/**
	 * @param ContainerInterface $container
	 * @return LatexProcessor
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		return $this;
	}
	
	public function __destruct()
	{
		/* clean up */
		foreach ($this->usedFolders as $folder)
		{
			$this->removeFolder($folder);
		}
	}
	
	/**
	 * Generate pdf from given template
	 * 
	 * @param string $template
	 * @param array $values
	 * @param string $scope
	 * @param string $filename
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function generatePdfFromTemplate($template, $values = array(), $scope = NULL, $filename = NULL, $returnSource = false)
	{
		return $this->generatePdf($this->container->get('templating')->render($template, $values),$scope,$filename,$returnSource);
	}
    
    /**
	 * Gerates pdf from given latex syntax
	 * 
	 * @param string $latex
	 * @param string $scope
	 * @param string $filename
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function generatePdf($input, $scope = NULL, $filename = NULL,$returnSource = false)
	{
		/* get path from config */
		$path = $this->container->getParameter('vaterlangen_latex.path');

		/* get include path */
		$include = $this->container->getParameter('vaterlangen_latex.include');

		$this->exec('echo $BASHOPTS');
		
		/* build file and folder names */
		$texName = $scope === NULL ? 'vaterlangen' : $scope;
		$texName = substr($texName,-4) == '.tex' ? $texName : $texName.'.tex';
		$pdfName = substr($texName,0,-4).'.pdf';
		$scope = $scope === NULL ? uniqid() : $scope;
		$filename = $filename ? (substr($filename,-4) == '.pdf' ? $filename : $filename.'.pdf') : 'file.pdf';
		

		/* build working directory */
		$tmp = $this->container->getParameter('vaterlangen_latex.temp').'/'.$scope;

		/* build file system structure */
		$this->removeFolder($tmp);
		$this->createFolder($tmp);
		$this->createLinks($include,$tmp);

		/* store foldername for garbage handling */
		$usedFolders[] = $tmp;
		
		/* dump latex code to file */ 
		file_put_contents("$tmp/$texName", print_r($input,true));


		/* run pdflatex */
		for ($i = 0; $i < 2; $i++) 
		{
			$this->exec("cd $tmp && ".$path['pdflatex']." --shell-escape --synctex=1 --interaction=nonstopmode $texName");
		}

		/* check what we should return */
		if ($returnSource)
		{
			$file_return = $this->makeZipPackage($tmp);
			$filename = substr($filename,0,-4).'.zip';
		}else{
			$file_return = "$tmp/$pdfName";
		}

		if (!$file_return)
		{
			throw new Exception("Invalid File!");
		}

		/* build and return response */
		$response = new BinaryFileResponse($file_return,200,array('Content-MD5' => base64_encode(md5($file_return).'1234')),false);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$filename,preg_replace('/[^(\x20-\x7F)]*/','_', $filename));
		$response::trustXSendfileTypeHeader();
		
		return $response;
	}

	private function makeZipPackage($rootPath)
	{
		$path = $this->container->getParameter('vaterlangen_latex.temp');
		$archiveName = $this->exec("mktemp --tmpdir=$path XXXXXXXX.vaterlangen.zip");

		HZip::zipDir($rootPath, $archiveName); 
		return $archiveName;
	}
	
	
	/**
	 * Clean up temp folder
	 * 
	 * @param string $scope
	 */
	public function removeWorkingSet($scope)
	{
		$this->removeFolder($this->container->getParameter('vaterlangen_latex.temp').'/'.$scope);
	}
	
	/**
	 * remove folder from disk
	 * 
	 * @param string $folder
	 */
	private function removeFolder($folder)
	{
		$this->exec("rm -f ".$folder.'/*',false);
		$this->exec("rmdir ".$folder,false);
	}

	/**
	 * create folder on disk
	 * 
	 * @param string $folder
	 */
	private function createFolder($folder)
	{
		$this->exec("mkdir -p $folder");
	}

	/**
	 * Link all files and folders bewlow given source path
	 * excluding all .bak files
	 * 
	 * @param string $folder
	 */
	private function createLinks($source,$target)
	{
		$this->exec("ln -sf $source/!(*.bak) $target/");
	}
	
	/**
	 * Executes a command and returns output if exit code === 0
	 * 
	 * @param string $command
	 * @return string
	 */
	private function exec($command,$throwException = true)
	{
		$bash = $this->container->getParameter('vaterlangen_latex.path')['bash'];

		/* generate enviroment variables for command */
		$env = 'export PATH=$PATH; export SHELL='.$bash.'; export BASHOPTS=extglob';

		$output = array();
		$return = exec("$env ; $bash -c \"$command\"",$output,$erg);
		if ($throwException && $erg !== 0)
		{
			throw new Exception("Executing '$command' with ENV='$env' failed with status '$erg'!\nTrace:\n$return\n".join("\n- ",$output));
		}
		
		return $return;
	}
}
