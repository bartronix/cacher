<?php
/**  
* @copyright Bart Leemans
* @author Bart Leemans <contact@bartleemans.be>
* @version 1.0
* @license MIT
*/
namespace Bartronix;

class Cacher {

    private $file = "";
    private $cacheDir = "";

    function __construct($file) {
        $this->cacheDir = $_SERVER["DOCUMENT_ROOT"] . "/cache";
        if(!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        $this->loadCache($file);
        $this->removeExpired();
    }
    
    function loadCache($file) {
        $this->file = $this->cacheDir . "/" . $file . ".xml";      
        if(!file_exists($this->file)) {
            $content = '<?xml version="1.0" encoding="UTF-8"?><items></items>';
            $fp = fopen($this->file,"wb");
            fwrite($fp,$content);
            fclose($fp);
        }
    }

    function removeExpired() {
        $xmlStr = file_get_contents($this->file);
        $xml = new \SimpleXMLElement($xmlStr);
        $items = $xml->xpath("//item[(date < " . time() . ")]");
        foreach($items as $item) {
            $node = dom_import_simplexml($item);
            $node->parentNode->removeChild($node);
        }
        file_put_contents($this->file, $xml->asXML());
    }

    function getEntry($id) {
        $xmlStr = file_get_contents($this->file);
        $xml = new \SimpleXMLElement($xmlStr);
        $res = $xml->xpath("//item[@id = '" . $id . "']");
        if($res) {
            $val = (string) $res[0]->content;
            if($val) {
                return unserialize(base64_decode($val));
            }
        }
        return null;
    }

    function addEntry($id, $content, $expirationSeconds) {
        $content = base64_encode(serialize($content));
        $xmlStr = file_get_contents($this->file);
        $xml = new \SimpleXMLElement($xmlStr);
        $this->checkExistsAndRemove($xml,$id);
        $child = $xml->addChild('item');
        $child->addAttribute('id', $id);
        $child->addChild('content', $content);
        $child->addChild('date', time() + $expirationSeconds);
        file_put_contents($this->file, $xml->asXML());
    }

    function checkExistsAndRemove($xml, $id) {
        $item = $xml->xpath("//item[@id='" . $id . "']");
        if(isset($item[0])) {
            $node = dom_import_simplexml($item[0]);
            $node->parentNode->removeChild($node);
        }
    }
}
