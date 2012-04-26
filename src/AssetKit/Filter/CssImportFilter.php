<?php
namespace AssetKit\Filter;

class CssImportFilter
{

    public function filter($collection) 
    {
        // get css files and find @import statement to import related content
        $assetDir = $collection->asset->getPublicDir();
        $contents = '';
        foreach( $collection->getSourcePaths() as $path ) {
            $dir = dirname($path);
            $content = file_get_contents( $path );

            /**
             * Looking for things like:
             *
             *    @import url("jquery.ui.core.css");
             */
            $content = preg_replace_callback('#
                @import 
                \s+
                url\(   
                    (\'|"|)
                    (?<url>.*?)
                    \1
                \)\s*;
                #xs', 
                function($matches) use ($path,$dir) {
                    $path = $matches['url'];
                    $content = '/*****************************' . "\n"
                        . "IMPORT FROM $path \n*********************/\n\n";

                    if( preg_match( '#^https?://#' , $path ) ) {
                        $content .= file_get_contents( $path );
                    }
                    else {
                        $path = $dir . DIRECTORY_SEPARATOR . $path;
                        if( ! file_exists( $path ) )
                            throw new Exception("CSS Import error, file $path not found.");
                        $content .= file_get_contents($path);
                    }
                    return $content;
            }, $content );
            $contents .= $content;
        }
        $collection->setContent( $contents );
    }

}

