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
                    $newXML[$i] = array(
                        'title' => $reader[$indice]['title'],
                    );

                    for($j = 0; $j < count($reader[$indice]['itens']['item']); $j++)
                    {
                        $item = $reader[$indice]['itens']['item'][$j];
                        $newXML[$i]['itens'][$j] = array(
                            'title' => $item['title'],
                            'description' => $item['description'],
                            'image' => $this->getPathImages() . $item['image']
                        );
                    }

                    unset($reader[$indice]);
                    shuffle($reader);
                };

                FileSystemCache::store($key, $newXML,432000);
            }

        }

        else
        {
            $newXML = array(
                'status' => 'ERROR',
                'message' => 'Arquivo não encontrado'
            );
        }

        $xmlMaker = new XMLBuilder('root');
        $xmlMaker->load($newXML);
        $xml_output = $xmlMaker->createXML(true);
        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text/xml');

        if(isset($newXML['status']))
        {
            if($newXML['status'] == 'ERROR')
            {
                $response = $response->withStatus(404);
            }
        }

        return $response;
    }

    public function getFileXML()
    {
        return $this->fileXML;
    }
    public function setFileXML($fileXML)
    {
        $this->fileXML = $fileXML;
        return $this;
    }
    public function getPathImages()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/data/uploads/images/';
    }
}