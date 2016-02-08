<?php

/*
 * Domparser class for parsing HTML code get tags by id or class, get images,
  get external and internal links etc...
  Copyright (C) 2011  George Imerlishvili

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>. */

class Default_Plugin_Domparser {

    private $url = "";
    public $host = "";
    public $path = "";
    public $scheme;
    private $pageSource;
    private $images = array();
    private $links = array();
    private $internalurls = array();
    private $extrenalurls = array();
    private $openCloseTags = array();

    private function _sortlinks() {

        foreach ($this->links as $l) {
            preg_match('/href="([^"]*)"/i', $l, $ma);

            if (substr(trim($ma[1]), 0, 1) == "/")
                $this->internalurls[] = $this->scheme . '://' . $this->host . trim($ma[1]);
            elseif (substr(trim($ma[1]), 0, 2) == "./")
                $this->internalurls[] = $this->scheme . '://' . $this->host . substr(trim($ma[1]), 2);
            elseif (substr(trim($ma[1]), 0, 7) != "http://") {
                $pa = strrchr($this->path, '/');

                $this->internalurls[] = $this->scheme . '://' . $this->host . $pa . '/' . substr(trim($ma[1]), 2);
            } elseif (substr(trim($ma[1]), 0, 7) == "http://") {

                $this->extrenalurls[] = trim($ma[1]);
            }
        }
    }

    public function Domparser($url = false) {

        if ($url !== false) {
            $this->url = $url;
            $urlarr = parse_url($this->url);
            $this->scheme = $urlarr['scheme'];
            $this->host = $urlarr['host'];
            $this->path = $urlarr['path'];
            $this->pageSource = file_get_contents($this->url);
            if($this->pageSource === FALSE){
                return false;
            }
        }
        preg_match_all('#<img[\d\D]*?>#si', $this->pageSource, $mats);
        $this->images = $mats;
        preg_match_all('#<a[\d\D]*?<\/a>#si', $this->pageSource, $mats);
        $this->links = $mats[0];
        $this->_sortlinks($this->links);
        return $this;
    }

    public function setUrl($ob, $pageSource) {

        $ob->pageSource = $pageSource;

        return $ob;
    }

    public function getImages() {
        return $this->images;
    }

    public function getLinks() {
        return $this->links;
    }

    public function getInternalinks() {
        return $this->internalurls;
    }

    public function getExternalinks() {
        return $this->extrenalurls;
    }

    public function getElementbyid($elid, $tag = "div") {
        preg_match_all('/(<' . $tag . '[^>]*>|<\/' . $tag . '>)/si', $this->pageSource, $openandclosedts);
        $this->openCloseTags = $openandclosedts[0];
        $found = false;
        $opdivs = 1;
        $howmen = 0;
        foreach ($this->openCloseTags as $val) {
            if ($found) {
                if (trim($val) != "</" . $tag . ">") {
                    $opdivs++;
                } else {
                    $howmen++;
                    $opdivs--;
                }
                if ($opdivs < 1)
                    break;
            }

            if (preg_match('/<' . $tag . '[^id]+id="' . $elid . '"/i', $val)) {
                $found = true;
            }
        }
        if ($found !== true)
            return "specifed id not found!";
        $patterni = '#<' . $tag . '[^id]*?id="' . $elid . '"[^>]*?>' . str_repeat('([\d\D]*?)<\/' . $tag . '>', $howmen) . '#si';

        preg_match($patterni, $this->pageSource, $ma);



        $strii = "<" . $tag . ">";

        for ($i = 1; $i <= $howmen; $i++)
            $strii.=$ma[$i] . "</" . $tag . ">";

        return $strii;
    }

    public function getElementbyclass($elid, $tag = "div") {
        preg_match_all('/(<' . $tag . '[^>]*>|<\/' . $tag . '>)/si', $this->pageSource, $openandclosedts);
        $this->openCloseTags = $openandclosedts[0];
        $found = false;
        $opdivs = 1;
        $howmen = -1;
        foreach ($this->openCloseTags as $val) {
            if ($found) {
                if (trim($val) != "</" . $tag . ">") {
                    $opdivs++;
                } else {
                    $howmen++;
                    $opdivs--;
                }
                if ($opdivs < 1)
                    break;
            }

//            if (preg_match('/<' . $tag . '[^class]+class="' . $elid . '"/i', $val)) {
            if (preg_match('/<' . $tag . '[^class]+class="' . $elid . '"/i', $val)) {
                $found = true;
            }
        }
        if ($found !== true){
            return FALSE;
        }
            
        $patterni = '/<' . $tag . '[^class]*?class="' . $elid . '"[^>]*?>' . str_repeat('([\d\D]*?)<\/' . $tag . '>', $howmen) . '/si';


        preg_match($patterni, $this->pageSource, $ma);


        $strii = "<" . $tag . ">";

        for ($i = 1; $i <= $howmen; $i++)
            $strii.=$ma[$i] . "</" . $tag . ">";

        return $strii;
    }

    public function getHead() {

        return preg_replace('/^[\d\D]*?<head>([\d\D]*?)<\/head>[\d\D]*?$/is', "$1", $this->pageSource);
    }

    public function getBody() {

        return preg_replace('/^[\d\D]*?<body>([\d\D]*?)<\/body>[\d\D]*?$/is', "$1", $this->pageSource);
    }

    public function getTitle() {

        return preg_replace('/^[\d\D]*?<title>([\d\D]*?)<\/title>[\d\D]*?$/is', "$1", $this->pageSource);
    }

}

?>