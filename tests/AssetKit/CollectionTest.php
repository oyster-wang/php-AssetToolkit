<?php

class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function testCollection()
    {
        /*
        $cln = new AssetKit\Collection;
        $cln->addFile( 'public/assets/jquery/jquery/dist/jquery.js' );
        ok($cln);
         */
    }


    public function testCoffeeFiles()
    {
        /*
        $config = Test::getConfig();
        $loader = Test::getLoader($config);
        $test = $loader->load('test');
        foreach( $test->getCollections() as $cln ) {
            $files = $cln->getFilePaths();
            ok( $files );

            // read content from files
            $content = $cln->getContent();
            ok( $content );

            // ok, now let's use a compressor
            $filter = new AssetKit\Filter\CoffeeScriptFilter;
            ok( $filter );

            // XXX: should only filter coffeescript
            $filter->filter( $cln );
            $content = $cln->getContent();
            ok( $content );
            like( '/Generated by CoffeeScript/', $content );
        }
        */
    }

    function testJsFiles()
    {
        /*
        $config = Test::getConfig();
        $loader = Test::getLoader($config);
        $jquery = $loader->load('jquery');

        foreach( $jquery->getCollections() as $cln ) {

            $files = $cln->getSourcePaths();
            ok( $files );

            foreach( $files as $file ) {
                file_ok( $file );
            }

            // read content from files
            $content = $cln->getContent();
            ok( $content );

            // ok, now let's use a compressor
            $compressor = new AssetKit\Compressor\Yui\JsCompressor(
                'utils/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar',
                '/usr/bin/java');
            ok( $compressor );
            $compressor->compress( $cln );

            $content = $cln->getContent();

            like( '/jQuery/', $content );
        }
        */
    }
}

