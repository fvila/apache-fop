<?php
namespace Goetas\ApacheFopBundle\Processor;

/**
 * @author goetas <http://www.goetas.com/>
 */
use Symfony\Component\Process\ProcessBuilder;

class Fop 
{
	const OTUPUT_PDF = 'application/pdf';
	const OTUPUT_RTF = 'text/rtf';
	
	protected $fopExecutable;
	protected $javaExecutable;

	protected $xslParameters = array();
	
	protected $configurationFile;
	
	public function __construct($fopExecutable) {
		$this->setFopExecutable($fopExecutable);
	}
	public function convertToPdf($source, $xsl = null) {
		return $this->convert($source, self::OTUPUT_PDF, $xsl);
	}
	public function convertToRtf($source, $xsl = null) {
		return $this->convert($source, self::OTUPUT_RTF, $xsl);
	}
	public function convert($source, $outputFormat, $xsl = null) {
		
		$process = new ProcessBuilder ();
		$process->setInput($source);
		$process->add ( $this->fopExecutable );
		
		/* $process->add ( "-q" ); */
		/* $process->add ( "-r" ); */

        foreach ($this->xslParameters as $key => $value) {
			$process->add("-param");
			$process->add($key);
			$process->add($value);
		}
        
		if($xsl!==null){
			$process->add ( "-xsl" );
			$process->add ( $xsl );

            $process->add ( "-out" );
            $process->add ( $outputFormat );
            $process->add ( "-" );

            $process->add ("-xml");
            $process->add ("-");
		}else{
            die ("Not tested");
			$process->add ( "-fo -" );

            $process->add ( "-out" );
            $process->add ( $outputFormat );
            $process->add ( "-" );
		}
		
		if ($this->configurationFile !== null) {		
			$process->add ( "-c" );
			$process->add ( $this->configurationFile );
		}

		$p = $process->getProcess();
		$p->run();

		if(!$p->isSuccessful()){
			$e = new \Exception("Apache FOP exception.\n" . $p->getErrorOutput());
			throw new \RuntimeException("Can't generate the document", null, $e);
		}

		return $p->getOutput();
	}
	public function getFopExecutable() {
		return $this->fopExecutable;
	}
	
	public function getConfigurationFile() {
		return $this->configurationFile;
	}
	
	public function setFopExecutable($fopExecutable) {
		if(!is_executable($fopExecutable)){
			throw new \RuntimeException(sprintf("Can't find %s command", $fopExecutable));
		}
		$this->fopExecutable = $fopExecutable;
	}
	
	public function setConfigurationFile($configurationFile) {
		if(!is_readable($configurationFile)){
			throw new \RuntimeException(sprintf("Can't find configuration file named '%s'", $configurationFile));
		}
		$this->configurationFile = $configurationFile;
	}
	public function getJavaExecutable() {
		return $this->javaExecutable;
	}

	public function setJavaExecutable($javaExecutable) {
		$this->javaExecutable = $javaExecutable;
	}

	public function addParam($name, $value) {
		$this->xslParameters[$name] = $value;	
	}

}
