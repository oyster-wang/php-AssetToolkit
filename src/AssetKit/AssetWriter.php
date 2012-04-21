<?php
namespace AssetKit;
use Exception;

class AssetWriter
{
    public $loader;
    public $assets;
    public $in;
    public $name;

    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    public function from($assets)
    {
        $this->assets = $assets;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function in($in)
    {
        $this->in = $in;
        return $this;
    }


    /**
     * Aggregate stylesheet/javascript content
     */
    public function aggregate()
    {
        $css = '';
        $js = '';
        foreach( $this->assets as $asset ) {
            $collections = $asset->getFileCollections();
            foreach( $collections as $collection ) {
                if( $collection->filters ) {
                    foreach( $collection->filters as $filtername ) {
                        if( $filter = $this->loader->getFilter( $filtername ) ) {
                            $filter->filter($collection);
                        }
                        else {
                            throw new Exception("filter $filtername not found.");
                        }
                    }
                }

                if( $this->loader->enableCompressor && $collection->compressors ) {
                    foreach( $collection->compressors as $compressorname ) {
                        if( $compressor = $this->loader->getCompressor( $compressorname ) ) {
                            $compressor->compress($collection);
                        }
                        else { 
                            throw new Exception("compressor $compressorname not found.");
                        }
                    }
                }

                if( $collection->isJavascript ) {
                    $js .= $collection->getContent();
                } elseif( $collection->isStylesheet ) {
                    $css .= $collection->getContent();
                }
                else {
                    throw new Exception("Unknown asset type");
                }
            }
        }

        return array(
            'javascript' => $js,
            'stylesheet' => $css,
        );
    }


    public function write()
    {
        $contents = $this->aggregate();
        $return = array();

        if( ! file_exists($this->in) )
            mkdir( $this->in , 0755, true );


        if( isset($contents['stylesheet']) ) {
            $return['stylesheet'] = $this->in . DIRECTORY_SEPARATOR 
                        . $this->name . '-' 
                        . md5( $contents['stylesheet']) . '.css';
            file_put_contents( $return['stylesheet'] , $contents['stylesheet'] );
        }
        if( isset($contents['javascript']) ) {
            $return['javascript'] = $this->in . DIRECTORY_SEPARATOR 
                        . $this->name . '-' 
                        . md5( $contents['javascript']) . '.js';
            file_put_contents( $return['javascript'] , $contents['javascript'] );
        }
        return $return;
    }
}


