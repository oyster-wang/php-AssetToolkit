<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;
use AssetKit\FileUtils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


/**
 * Asset class
 *
 * Asset object can be created from a manifest file.
 * Or can just be created with no arguments.
 */
class Asset
{
    public $stash;

    /* manifest file (related path, relate to config file) */
    public $manifest;

    /* asset dir (related path, relate to config file) */
    public $sourceDir;


    /**
     * @var AssetKit\Config
     */
    public $config;

    public $collections = array();

    /**
     * @param array|string|null $arg manifest array, manifest file path, or asset name
     */
    public function __construct($arg = null)
    {
        // load from array
        if( $arg && is_array($arg) ) {
            $this->stash     = @$arg['stash'];
            $this->manifest  = @$arg['manifest'];
            $this->sourceDir       = @$arg['source_dir'];
            $this->name      = isset($arg['name']) ? $arg['name'] : null;
        }
        elseif( $arg && file_exists($arg) ) 
        {
            // load from file
            $file = $arg;

            $this->sourceDir = dirname($file);
            $this->name = basename(dirname($file));
            $this->manifest = $file;

            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if( 'yml' === $ext ) {
                $serializer = new SerializerKit\Serializer('yaml');
                $this->stash = $serializer->decode(file_get_contents($file));
            } else {
                $this->stash = require $file;
            }

            // expand manifest glob pattern
            if( ! isset($this->stash['assets']) ) {
                throw new Exception('assets tag is not defined.');
            }
            else {
                $this->expandManifest();
            }

        }
        elseif( $arg && is_string($arg) ) {
            $this->name = $arg;
        }

        if( isset($this->stash['assets']) ) {
            $this->collections = FileCollection::create_from_manfiest($this);
        }
    }

    public function expandManifest()
    {
            foreach( $this->stash['assets'] as & $a ) {
                $dir = $this->sourceDir;
                $files = array();
                foreach( $a['files'] as $p ) {
                    if( strpos($p,'*') !== false ) {
                        $expanded = array_map(function($item) use ($dir) { 
                            return substr($item,strlen($dir) + 1);
                                 }, glob($this->sourceDir . DIRECTORY_SEPARATOR . $p));
                        $files = array_unique( array_merge( $files , $expanded ) );
                    }
                    elseif( is_dir( $dir . DIRECTORY_SEPARATOR . $p ) ) {
                        // expand files from dir
                        $ite = new RecursiveDirectoryIterator( $dir . DIRECTORY_SEPARATOR . $p );
                        $expanded = array();
                        foreach (new RecursiveIteratorIterator($ite) as $path => $info) {
                            if( $info->getFilename() === '.' || $info->getFilename() === '..' )
                                continue;
                            $expanded[] = $path;
                        }
                        $expanded = array_map(function($path) use ($dir) { 
                            return substr($path,strlen($dir) + 1);
                                } , $expanded);
                        $files = array_unique(array_merge( $files , $expanded ));
                    } else {
                        $files[] = $p;
                    }
                }
                $a['files'] = $files;
            }
    }

    public function createFileCollection()
    {
        $collection = new FileCollection;
        $collection->asset = $this;
        $collections[] = $collection;
        return $collection;
    }

    public function getFileCollections()
    {
        return $this->collections;
    }

    public function export()
    {
        return array(
            'stash' => $this->stash,
            'manifest' => $this->manifest,
            'source_dir'  => $this->sourceDir,
            'name' => $this->name,
        );
    }

    public function compile()
    {
        // compile assets
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSourceDir($absolute = false)
    {
        if( $absolute ) {
            return $this->config->getRoot() . DIRECTORY_SEPARATOR . $this->sourceDir;
        }
        return $this->sourceDir;
    }

    /**
     * Return the public dir of this asset
     */
    public function getPublicDir($absolute = false)
    {
        $public = $this->config->getPublicRoot($absolute);
        return $public . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->name;
    }

    public function hasSourceFiles()
    {
        $this->sourceDir;
        foreach( $this->collections as $collection ) {
            $paths = $collection->getSourcePaths(true);
            foreach( $paths as $path ) {
                if( ! file_exists($path) )
                    return false;
            }
        }
        return true;
    }

    /**
     * Init Resource file and update to public asset dir ?
     */
    public function initResource($update = false)
    {
        $updater = new \AssetKit\ResourceUpdater($this);
        return $updater->update($update);
    }
}



