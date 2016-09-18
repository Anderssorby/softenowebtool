<?php
/**
 *
 */
class Img extends HtmlElement {
    
    function __construct($ref, $alt = "", $thumb = false) {
        parent::__construct("img", true);
        if (is_numeric($ref)) {
            $this->src = $thumb ? "?site=bilde&id=".$ref."&thumb=true" : "?site=bilde&id=".$ref;
        } else {
            $this->src = $ref;
        }
        $this->alt = $alt;
    }
}