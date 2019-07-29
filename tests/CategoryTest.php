<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmazonMWS\CategoryRequest;

final class CategoryTest extends TestCase {
    protected static $categoryRequest;
    protected $categories;

    public static function setUpBeforeClass() {
        self::$categoryRequest = new CategoryRequest();
    }

    /**
     */
    public function testLoadCategories() {
        try {
            $categories = self::$categoryRequest->loadCategories();

            $this->assertNotEmpty($categories);
        } catch(\Exception $e) {
            error_log($e->getMessage());
            $this->fail();
        }
    }

    /**
     */
    public function testLoadFields() {
        $categories = self::$categoryRequest->loadCategories();

        foreach($categories as $category) {
            $fields = self::$categoryRequest->loadFields($category);

            foreach($category as $c) {
                if(!isset($fields[$c])) $this->assertArrayHasKey($c, $fields);
            }
        }

        $this->assertTrue(true);
    }
}
?>