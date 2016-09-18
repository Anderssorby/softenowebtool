<?php
/**
 *
 */
class CDATA extends TextNode {
	public function __toString() {
		return "<![CDATA[".$this->text."]]>";
	}
}
