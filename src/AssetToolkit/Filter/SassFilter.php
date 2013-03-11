<?php
namespace AssetToolkit\Filter;
use AssetToolkit\Collection;
use AssetToolkit\Process;
use RuntimeException;
use AssetToolkit\Utils;

class SassFilter 
{
    public $bin;
    public $fromFile = true;

    public $loadPaths = array();
    public $enableCompass = true;

    public function __construct($bin = null)
    {
        if ( $bin ) {
            $this->bin = $bin;
        } else {
            $this->bin = Utils::findbin('sass');
        }
    }

    public function setCompass($bool)
    {
        $this->enableCompass = $bool;
    }

    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }

    public function filter(Collection $collection)
    {
        if( $collection->filetype !== Collection::FILETYPE_SASS )
            return;

        $proc = new Process(array( $this->bin ));
        if ($this->enableCompass) {
            $proc->arg('--compass');
        }

        foreach( $this->loadPaths as $path ) {
            $proc->arg('--load-path');
            $proc->arg($path);
        }

        if($this->fromFile) {
            $filepaths = $collection->getSourcePaths(true);
            foreach( $filepaths as $filepath ) {
                $proc->arg($filepath);
            }
        } else {
            $proc->arg('-s');
            $proc->input($collection->getContent());
        }


        $code = $proc->run();
        if ( $code != 0 ) {
            throw new RuntimeException("SassFilter failure: $code. ");
        }
        $collection->setContent($proc->getOutput());
    }

}

