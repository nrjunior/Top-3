<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;


final class Top3
{
    private $fileXML;

    public function __invoke(Request $request, Response $response, $args)
    {
       $this->setFileXML(__DIR__ . '/../../../data/top3.xml');

        if(file_exists($this->getFileXML()))
        {
            $amount = isset($args['amount']) ? $args['amount'] : 5;
            $forceFileCached = isset($request->getQueryParams()['forceFileCached']) ? $request->getQueryParams()['forceFileCached'] : false;

            FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
            $key = FileSystemCache::generateCacheKey('cache', null);
            $newXML = FileSystemCache::retrieve($key);


            if($newXML === false || $forceFileCached == true)
            {
                $reader = json_decode(json_encode(simplexml_load_file($this->getFileXML())), true);
                $reader = $reader['top'];
                $newXML = array();

                if(count($reader) < $amount)
                {
                    $amount = count($reader);
                }

                for ($i = 0; $i < $amount; $i++)
                {

                    $indice = rand(0, count($reader) - 1);
                    $newXML[] = array(
                        'title' => $reader[$indice]['title'],
                        'itens' => $reader[$indice]['itens']
                    );
                    unset($reader[$indice]);
                    shuffle($reader);
                };

                FileSystemCache::store($key, $newXML,432000);
            }

            $xmlMaker = new XMLBuilder('root');
            $xmlMaker->load($newXML);
            $xml_output = $xmlMaker->createXML(true);
            $response->write($xml_output);
            $response = $response->withHeader('content-type', 'text/xml');
            return $response;


           // print_r($newXML);
        }
        else
        {
            echo "o arquivo não existe";
        }
    }

    /**
     * @return mixed
     */
    public function getFileXML()
    {
        return $this->fileXML;
    }

    /**
     * @param mixed $fileXML
     * @return Top3
     */
    public function setFileXML($fileXML)
    {
        $this->fileXML = $fileXML;
        return $this;
    }


}