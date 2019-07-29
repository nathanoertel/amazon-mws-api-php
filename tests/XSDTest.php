<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmazonMWS\util\XSDParser;

final class XSDTest extends TestCase {
    public function testCanLoad() {
        try {
            $parser = new XSDParser('Product');
        
            $this->assertTrue(true);
        } catch(\Exception $e) {
            $this->fail();
        }
    }
}
?>